<?php 

namespace App\Services\V1\Order;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\Helper;
use App\Models\Order\Store;
use App\Models\Order\Cuisine;
use App\Models\Common\UserAddress;
use App\Models\Common\RequestFilter;
use App\Models\Order\StoreItemAddon;
use App\Models\Order\StoreItem;
use App\Models\Order\StoreCart;
use App\Models\Common\Rating;
use App\Models\Common\User;
use App\Models\Common\State;
use App\Models\Order\StoreCityPrice;
use App\Models\Order\StoreOrderDispute;
use Auth;
use DB;
use Carbon\Carbon;
use App\Models\Common\Setting;
use App\Models\Order\StoreCartItemAddon; 
use App\Models\Common\Promocode;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreOrderInvoice;
use App\Models\Order\StoreOrderStatus;
use App\Models\Common\AdminService;
use App\Models\Common\UserRequest;
use App\Models\Common\PaymentLog;
use App\Services\PaymentGateway;
use App\Models\Common\Card;
use App\Services\Transactions;
use App\Services\SendPushNotification;
use App\Services\V1\Common\UserServices;
use App\Traits\Actions;
use App\Models\Common\AdminWallet;
use App\Models\Common\Provider;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderWallet;
use App\Models\Order\StoreWallet;
use Exception;
use Illuminate\Support\Facades\Log;

class Order {
    
    use Actions;

    /**
        * Get a validator for a tradepost.
        *
        * @param  array $data
        * @return \Illuminate\Contracts\Validation\Validator
    */
    protected function validator(array $data) {
        $rules = [
            'location'  => 'required',
        ];

        $messages = [
            'location.required' => 'Location Required!',
        ];

        return Validator::make($data,$rules,$messages);
    }


    public function cancelOrder($request) {

        try{

            $card = Card::where('user_id', $request->user_id)->first();//->where('is_default', 1) 
            $payable = 0;
            if(!empty($card)){
                $orderRequest = StoreOrder::findOrFail($request->id);
                
                $orderInvoice = StoreOrderInvoice::where(['store_order_id' => $orderRequest->id])->first();                
                
                if($orderInvoice->payment_mode == 'CASH')
                {
                    return ['status' => 404, 'message' => 'Order can not be cancelled'];
                }
                if($orderRequest->status == 'CANCELLED')
                {
                    return ['status' => 404, 'message' => trans('api.order.ride_cancelled')];
                }


                // if(in_array($orderRequest->status, ['ORDERED','STORECANCELLED'])) {
                    
                    $orderRequest->status = 'CANCELLED';

                    $cityprice=StoreCityPrice::where('store_type_id',$orderRequest->store_type_id)->where('company_id',$orderRequest->company_id)
                    ->where('city_id',$orderRequest->city_id)
                    ->first();

                    if($cityprice){
                        $payable = $cityprice->cancellation_fee;
                    }
                    if($payable!=0){                                                
                        $request->request->add(['card_id' => $card->card_id]);
                        $request->request->add(['payment_mode' => $orderInvoice->payment_mode]);
                        $request->request->add(['company_id' => $orderRequest->company_id]);
                        $request->request->add(['user_id' => $orderRequest->user_id]);                            
                        $payment_id = self::orderpayment($payable,$request);
                        // if($payment_id=='failed'){                            
                        if($payment_id['responseCode'] == 400){    
                            return Helper::getResponse(['status' => $payment_id['responseCode'],'message' => $payment_id['responseMessage']]);//trans('Transaction Failed')
                        }  
                    }

                    $user_data=ProviderWallet::orderBy('id', 'DESC')->first();
                    if(!empty($user_data))
                        $transaction_id=$user_data->id+1;
                    else
                        $transaction_id=1;

                    //To update the cancellation_fee in order invoice
                    $orderInvoice->cancellation_fee = $payable;
                    if(!empty($orderRequest->provider_id)){
                        $orderInvoice->cancellation_fee_goesto = 1;
                        $request['transaction_id']=$transaction_id;        
                        $request['transaction_alias']=$orderRequest->store_order_invoice_id;
                        $request['transaction_desc']='Order cancelled amount sent';
                        $request['id']=$orderRequest->provider_id;
                        $request['admin_service']=$orderRequest->admin_service;
                        $request['type']='C';
                        $request['amount']=$payable;
                        $this->createProviderWallet($request);
                    } else {
                        $orderInvoice->cancellation_fee_goesto = 2;
                        $request['company_id']=$orderRequest->company_id;
                        $request['admin_service']=$orderRequest->admin_service;                    
                        $request['country_id']=$orderRequest->country_id;
                        $request['transaction_id']=$orderRequest->id;  
                        $request['transaction_alias']=$orderRequest->store_order_invoice_id;
                        $request['transaction_desc']='Order cancelled amount sent';                                        
                        $request['transaction_type']=12;
                        $request['type']='C';        
                        $request['wallet_type'] = 'ADMIN';
                        $request['created_type'] = $request->cancelled_by;
                        $request['created_by'] = $orderRequest->user_id;
                        $request['amount']=$payable;
                        $this->createAdminWallet($request);                    
                    }
                    $orderInvoice->save();

                    if($request->cancel_reason=='ot')
                        $orderRequest->cancel_reason = $request->cancel_reason_opt;
                    else
                        $orderRequest->cancel_reason = $request->cancel_reason;

                    $orderRequest->cancelled_by = $request->cancelled_by;
                    $orderRequest->save();                                       

                    $getdispute = StoreOrderDispute::where(['store_order_id' => $orderRequest->id])->first();
                    if(!empty($getdispute)){
                        $getdispute->status = 'closed';
                        $getdispute->save();
                    }

                    if(empty($getdispute)){
                        //Refund user to their wallet if wallet amount is set to ture while order food
                        if($orderInvoice->wallet_amount > 0){
                            $ipdata=array();
                            $ipdata['company_id']=$orderRequest->company_id;;
                            $ipdata['transaction_id']=$orderRequest->id;
                            $ipdata['transaction_alias']=$orderRequest->store_order_invoice_id;
                            $ipdata['transaction_desc']='Order amount refund deducted from wallet';
                            $ipdata['id']=$orderRequest->user_id;
                            $ipdata['type']='C';
                            $ipdata['amount']=$orderInvoice->wallet_amount;
                            if(!empty($orderRequest->admin_service))
                                $ipdata['admin_service']=$orderRequest->admin_service;
                            if(!empty($request['country_id']))
                                $ipdata['country_id']=$request['country_id'];
                            return $this->createUserWallet($ipdata); 
                        }
                    }

                    (new UserServices())->cancelRequest($orderRequest);

                    (new SendPushNotification)->ShopCancelRequest($orderRequest->store_id, $orderRequest->admin_service); 

                    return ['status' => 200, 'message' => trans('api.ride.ride_cancelled')];

                // } else {

                //     return ['status' => 403, 'message' => trans('api.ride.already_onride')];
                // }
            } else {
                return ['status' => 404, 'message' => trans('api.order.payment_method_required')];
            }
        }

        catch (ModelNotFoundException $e) {
            return $e->getMessage();
        }
    }

    public function shopAccept(Request $request) {

        try {


            $storeorder = StoreOrder::find($request->store_order_id);
            
            $user=User::findorfail($storeorder->user_id);
            $timezone= $storeorder->timezone;

            if($request->has('cooking_time')){
                $storeorder->order_ready_time=$request->cooking_time;
            }
            
            $Providers = (new UserServices())->availableDeliveryBoy($request,$storeorder);  
           
            if(count($Providers) == 0) {
                return ['status' => 422, 'message' => trans('api.ride.no_providers_found')];
            }
            
           
            if($request->has('delivery_date')){
                 
                $delivery_date = (Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::parse($request->delivery_date)->format('Y-m-d H:i:s')), $timezone))->setTimezone('UTC');
                $storeorder->delivery_date=$delivery_date;
            }
            $storeorder->status='SEARCHING';
            $storeorder->save();

            $storeorderstatus =  new StoreOrderStatus;
            $storeorderstatus->store_order_id=$request->store_order_id;
            $storeorderstatus->status='SEARCHING';
            $storeorderstatus->company_id=Auth::guard('shop')->user()->company_id;
            $storeorderstatus->save();
            
            
            //(new SendPushNotification)->OrderAcceptedRespond($storeorder->user_id,  $storeorder, 'ORDER');
            (new UserServices())->createDeliveryRequest($Providers, $storeorder, 'ORDER');
            
            $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::guard('shop')->user()->company_id, 'id' => $request->store_order_id,'shop'=> Auth::guard('shop')->user()->id, 'user' => $request->user_id ];
            app('redis')->publish('checkOrderRequest', json_encode( $requestData ));

            return ['status' => 200, 'message' =>'Accepted  Succesfully'];
        }  catch (\Throwable $e) {            
            return ['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()];
        }
    }

    public function shopCancel(Request $request) {
        StoreOrder::where('id',$request->id )->update(['status'=>'STORECANCELLED']);
        $storedispute =  new StoreOrderDispute;
        $storedispute->dispute_type='system';
        $storedispute->user_id=$request->user_id;
        $storedispute->store_id=$request->store_id;
        $storedispute->store_order_id=$request->id;
        $storedispute->dispute_name="Store Cancelled";
        $storedispute->dispute_type_comments="Store Cancelled";
        $storedispute->status="open";
        $storedispute->company_id=Auth::guard('shop')->user()->company_id;
        $storedispute->save();
        $storeorderstatus =  new StoreOrderStatus;
        $storeorderstatus->store_order_id=$request->id;
        $storeorderstatus->status='CANCELLED';
        $storeorderstatus->company_id=Auth::guard('shop')->user()->company_id;
        $storeorderstatus->save();
        //Send message to socket
        $requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::guard('shop')->user()->company_id, 'id' => $request->id,'shop'=> $request->store_id, 'user' => $request->user_id ];
        app('redis')->publish('checkOrderRequest', json_encode( $requestData ));
        $data=['status' => 200, 'message' =>'Cancelled Succesfully'];

        return $data;
    }

    public function createDispute($request) {
        $storedispute =  new StoreOrderDispute;
        $storedispute->dispute_type='system';
        $storedispute->user_id= $request->user_id;
        $storedispute->provider_id= $request->provider_id;
        $storedispute->store_id= $request->store_id;
        $storedispute->store_order_id= $request->id;
        $storedispute->dispute_name="Provider Changed";
        $storedispute->dispute_title= $request->reason;
        $storedispute->dispute_type_comments="Provider Changed";
        $storedispute->status="open";
        $storedispute->company_id=$request->company_id;
        $storedispute->save();
        
        $orderRequest->status = 'PROVIDEREJECTED';
        $orderRequest->save();
    }


    public function callTransaction($store_order_id){  

        $StoreOrder = StoreOrder::findOrFail($store_order_id);
        
        if($StoreOrder->paid==1){
            $transation=array();
            $transation['admin_service']='ORDER';
            $transation['company_id']=$StoreOrder->company_id;
            $transation['transaction_id']=$StoreOrder->id;
            $transation['country_id']=$StoreOrder->country_id;
            $transation['transaction_alias']=$StoreOrder->store_order_invoice_id;       

            $paymentsStore = StoreOrderInvoice::where('store_order_id',$store_order_id)->first();

            $admin_commision=$credit_amount=0;                  

            $credit_amount=$paymentsStore->total_amount-$paymentsStore->commision_amount-$paymentsStore->delivery_amount;   

            //admin,shop,provider calculations
            if(!empty($paymentsStore->commision_amount)){
                $admin_commision=$paymentsStore->commision_amount;
                $transation['id']=$StoreOrder->store_id;
                $transation['amount']=$admin_commision;
                //add the commission amount to admin
                $this->adminCommission($transation);
            }       

            if(!empty($paymentsStore->delivery_amount)){
                //credit the deliviery amount to provider wallet
                if($StoreOrder->order_type=='DELIVERY'){
                    $transation['id']=$StoreOrder->provider_id;
                    $transation['amount']=$paymentsStore->delivery_amount;
                    $this->providerCredit($transation);
                }
            }             
            
            if($credit_amount>0){
                //credit the amount to shop wallet
                $transation['id']=$StoreOrder->store_id;
                $transation['amount']=$credit_amount;
                $this->shopCreditDebit($transation);
            }

            return true;
        }
        else{
            
            return true;
        }
        
    }

    protected function adminCommission($request){       
        $request['transaction_desc']='Shop Commission added';
        $request['transaction_type']=1;
        $request['type']='C';        
        $this->createAdminWallet($request);     
    }

    protected function shopCreditDebit($request){
        
        $amount=$request['amount'];
        $ad_det_amt= -1 * abs($request['amount']);                            
        $request['transaction_desc']='Order amount sent';
        $request['transaction_type']=10;       
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createAdminWallet($request);
                    
        $request['transaction_desc']='Order amount recevied';
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$amount;
        $this->createShopWallet($request);

        $request['transaction_desc']='Order amount recharge';
        $request['transaction_type']=11;
        $request['type']='C';
        $request['amount']=$amount;
        $this->createAdminWallet($request);

        return true;
    }

    protected function providerCredit($request){
                                    
        $request['transaction_desc']='Order deliviery amount sent';
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$request['amount'];
        $this->createProviderWallet($request);       
        
        $ad_det_amt= -1 * abs($request['amount']);                  
        $request['transaction_desc']='Order deliviery amount recharge';
        $request['transaction_type']=9;
        $request['type']='D';
        $request['amount']=$ad_det_amt;
        $this->createAdminWallet($request);

        return true;
    }

    protected function createAdminWallet($request){

        $admin_data=AdminWallet::orderBy('id', 'DESC')->first();

        $adminwallet=new AdminWallet;
        $adminwallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $adminwallet->admin_service=$request['admin_service'];
        if(!empty($request['country_id']))
            $adminwallet->country_id=$request['country_id'];
        $adminwallet->transaction_id=$request['transaction_id'];        
        $adminwallet->transaction_alias=$request['transaction_alias'];
        $adminwallet->transaction_desc=$request['transaction_desc'];
        $adminwallet->transaction_type=$request['transaction_type'];
        $adminwallet->type=$request['type'];
        $adminwallet->amount=$request['amount'];

        if(empty($admin_data->close_balance))
            $adminwallet->open_balance=0;
        else
            $adminwallet->open_balance=$admin_data->close_balance;

        if(empty($admin_data->close_balance))
            $adminwallet->close_balance=$request['amount'];
        else            
            $adminwallet->close_balance=$admin_data->close_balance+($request['amount']);        

        $adminwallet->save();

        return $adminwallet;
    }   

    protected function createProviderWallet($request){
        
        $provider=Provider::findOrFail($request['id']);

        $providerWallet=new ProviderWallet;        
        $providerWallet->provider_id=$request['id'];
        $providerWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $providerWallet->admin_service=$request['admin_service'];        
        $providerWallet->transaction_id=$request['transaction_id'];        
        $providerWallet->transaction_alias=$request['transaction_alias'];
        $providerWallet->transaction_desc=$request['transaction_desc'];
        $providerWallet->type=$request['type'];
        $providerWallet->amount=$request['amount'];

        if(empty($provider->wallet_balance))
            $providerWallet->open_balance=0;
        else
            $providerWallet->open_balance=$provider->wallet_balance;

        if(empty($provider->wallet_balance))
            $providerWallet->close_balance=$request['amount'];
        else            
            $providerWallet->close_balance=$provider->wallet_balance+($request['amount']);        

        $providerWallet->save();

        //update the provider wallet amount to provider table        
        $provider->wallet_balance=$provider->wallet_balance+($request['amount']);
        $provider->save();

        return $providerWallet;

    }

    public static function createUserWallet($request){
        
        $user=User::findOrFail($request['id']);

        $userWallet=new UserWallet;
        $userWallet->user_id=$request['id'];
        $userWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $userWallet->admin_service=$request['admin_service']; 
        $userWallet->transaction_id=$request['transaction_id'];        
        $userWallet->transaction_alias=$request['transaction_alias'];
        $userWallet->transaction_desc=$request['transaction_desc'];
        $userWallet->type=$request['type'];
        $userWallet->amount=$request['amount'];        

        if(empty($user->wallet_balance))
            $userWallet->open_balance=0;
        else
            $userWallet->open_balance=$user->wallet_balance;

        if(empty($user->wallet_balance))
            $userWallet->close_balance=$request['amount'];
        else            
            $userWallet->close_balance=$user->wallet_balance+($request['amount']);

        $userWallet->save();

        //update the user wallet amount to user table        
        $user->wallet_balance=$user->wallet_balance+($request['amount']);
        $user->save();

        return $userWallet;
    }

    protected function createShopWallet($request){
        
        $store=Store::findOrFail($request['id']);

        $storeWallet=new StoreWallet;        
        $storeWallet->store_id=$request['id'];
        $storeWallet->company_id=$request['company_id'];
        if(!empty($request['admin_service']))
            $storeWallet->admin_service=$request['admin_service'];             
        $storeWallet->transaction_id=$request['transaction_id'];        
        $storeWallet->transaction_alias=$request['transaction_alias'];
        $storeWallet->transaction_desc=$request['transaction_desc'];
        $storeWallet->type=$request['type'];
        $storeWallet->amount=$request['amount'];

        if(empty($store->wallet_balance))
            $storeWallet->open_balance=0;
        else
            $storeWallet->open_balance=$store->wallet_balance;

        if(empty($store->wallet_balance))
            $storeWallet->close_balance=$request['amount'];
        else            
            $storeWallet->close_balance=$store->wallet_balance+($request['amount']);        

        $storeWallet->save();

        //update the provider wallet amount to provider table        
        $store->wallet_balance=$store->wallet_balance+($request['amount']);
        $store->save();

        return $storeWallet;

    }

    public static function orderTipPayment($totalAmount,$request){
        $paymentMode = $request->payment_mode;        
               
        try{
        $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
              $siteConfig = $settings->site;
              $orderConfig = $settings->order;
              $paymentConfig = json_decode( json_encode( $settings->payment ) , true);

              $cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
              $card = 0;

                $stripe_secret_key = "";
                $stripe_publishable_key = "";
                $stripe_currency = "";

                if(count($cardObject) > 0) { 
                    $card = $cardObject[0]['status'];

                    $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
                    $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
                    $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

                    if(count($stripeSecretObject) > 0) {
                        $stripe_secret_key = $stripeSecretObject[0]['value'];
                    }

                    if(count($stripePublishableObject) > 0) {
                        $stripe_publishable_key = $stripePublishableObject[0]['value'];
                    }

                    if(count($stripeCurrencyObject) > 0) {
                        $stripe_currency = $stripeCurrencyObject[0]['value'];
                    }
                }
  
              $random = $orderConfig->booking_prefix.mt_rand(100000, 999999).'-tip';            
              
                switch ($paymentMode) {
                    case 'CARD':  

                    if($request->has('card_id')){
                        //$this->user->id
                        Card::where('user_id',$request->user_id)->update(['is_default' => 0]);
                        Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
                    }
                    // $this->user->id
                    $card = Card::where('user_id', $request->user_id)->where('is_default', 1)->first();

                    //if($card == null)  $card = Card::where('user_id', $this->user->id)->first();
                    $log = new PaymentLog();
                    $log->admin_service = 'ORDER';
                    $log->company_id = $request->company_id;//$this->company_id;
                    $log->user_type = 'user';
                    $log->transaction_code = $random;
                    $log->amount = $totalAmount;
                    $log->transaction_id = '';
                    $log->payment_mode = $paymentMode;
                    $log->user_id = $request->user_id;//$this->user->id
                    $log->save();    
                    $gateway = new PaymentGateway('stripe');
                    $totalAmount = number_format((float)$totalAmount, 2, '.', '');                    
                    $response = $gateway->process([
                          'order' => $random,
                          "amount" => $totalAmount,
                          "currency" => $stripe_currency,
                          "customer" => Auth::guard('user')->user()->stripe_cust_id,
                          "card" => $card->card_id,
                          "description" => "Payment Charge for " . Auth::guard('user')->user()->email,
                          "receipt_email" => Auth::guard('user')->user()->email,
                    ]);
                    
                  break;
                  case 'BRAINTREE':
                    // Perform payment logging for BRAINTREE
                    $log = new PaymentLog();
                    $log->admin_service = 'ORDER';
                    $log->company_id = $request->company_id;
                    $log->user_type = 'user';
                    $log->transaction_code = $random;
                    $log->amount = $totalAmount;
                    $log->transaction_id = ''; // Add transaction ID if available
                    $log->payment_mode = $paymentMode;
                    $log->user_id = $request->user_id;
                    $response = $log->save();
                    break;
                }
                
                //return $response;
                if(!empty($response)){
                    if($paymentMode =="CARD"){
                    if($response->status == "SUCCESS") {  
                        $log->transaction_id = $response->payment_id;
                        $log->save();
                        
                        return ['responseCode' => 200, 'responseMessage' => $response->payment_id]; 
                    }
                    }
                    elseif($paymentMode == "BRAINTREE"){
                        if($response)
                        {
                            if(!empty($request->nonce)){
                            $log->transaction_id = $request->nonce;
                            $log->save();
                            
                            return ['responseCode' => 200, 'responseMessage' => $request->nonce];
                            }
                            else
                            {
                                return ['responseCode' => 400, 'responseMessage' => 'Nonce not found'];
                            }
                        } 
                    }
                   
                    else {
                        // return 'failed';
                        Log::warning($response->status);
                        Log::warning('Payment is failed because stripe is not giving status success');
                        return ['responseCode' => 400, 'responseMessage' => 'Payment failed'];
                    }
                } else {                    
                    Log::warning('Payment is failed because stripe is not giving blank response');
                    return ['responseCode' => 400, 'responseMessage' => 'Payment failed'];
                }
        } catch(\Exception $e) {
            return ['responseCode' => 400, 'responseMessage' => $e->getMessage()];
        }
    }

    public static function orderpayment($totalAmount,$request){
        $paymentMode = $request->payment_mode;        
        //$this->company_id        
        try{
        $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
              $siteConfig = $settings->site;
              $orderConfig = $settings->order;
              $paymentConfig = json_decode( json_encode( $settings->payment ) , true);

              $cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
              $card = 0;

                $stripe_secret_key = "";
                $stripe_publishable_key = "";
                $stripe_currency = "";

                if(count($cardObject) > 0) { 
                    $card = $cardObject[0]['status'];

                    $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
                    $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
                    $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

                    if(count($stripeSecretObject) > 0) {
                        $stripe_secret_key = $stripeSecretObject[0]['value'];
                    }

                    if(count($stripePublishableObject) > 0) {
                        $stripe_publishable_key = $stripePublishableObject[0]['value'];
                    }

                    if(count($stripeCurrencyObject) > 0) {
                        $stripe_currency = $stripeCurrencyObject[0]['value'];
                    }
                }
  
              $random = $orderConfig->booking_prefix.mt_rand(100000, 999999);            
              
                switch ($paymentMode) {
                    case 'CARD':  

                    if($request->has('card_id')){
                        //$this->user->id
                        Card::where('user_id',$request->user_id)->update(['is_default' => 0]);
                        Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
                    }
                    // $this->user->id
                    $card = Card::where('user_id', $request->user_id)->where('is_default', 1)->first();

                    //if($card == null)  $card = Card::where('user_id', $this->user->id)->first();
                    $log = new PaymentLog();
                    $log->admin_service = 'ORDER';
                    $log->company_id = $request->company_id;//$this->company_id;
                    $log->user_type = 'user';
                    $log->transaction_code = $random;
                    $log->amount = $totalAmount;
                    $log->transaction_id = '';
                    $log->payment_mode = $paymentMode;
                    $log->user_id = $request->user_id;//$this->user->id
                    $log->save();    
                    $gateway = new PaymentGateway('stripe');
                    $totalAmount = number_format((float)$totalAmount, 2, '.', '');                    
                    $response = $gateway->process([
                          'order' => $random,
                          "amount" => $totalAmount,
                          "currency" => $stripe_currency,
                          "customer" => Auth::guard('user')->user()->stripe_cust_id,
                          "card" => $card->card_id,
                          "description" => "Payment Charge for " . Auth::guard('user')->user()->email,
                          "receipt_email" => Auth::guard('user')->user()->email,
                    ]);
                    
                  break;
                  case 'BRAINTREE':
                    // Perform payment logging for BRAINTREE
                    $log = new PaymentLog();
                    $log->admin_service = 'ORDER';
                    $log->company_id = $request->company_id;
                    $log->user_type = 'user';
                    $log->transaction_code = $random;
                    $log->amount = $totalAmount;
                    $log->transaction_id = ''; // Add transaction ID if available
                    $log->payment_mode = $paymentMode;
                    $log->user_id = $request->user_id;
                    $response = $log->save();
                    break;
                }
                
                //return $response;
                if(!empty($response)){
                    if($paymentMode =="CARD"){
                    if($response->status == "SUCCESS") {  
                        $log->transaction_id = $response->payment_id;
                        $log->save();
                        
                        return ['responseCode' => 200, 'responseMessage' => $response->payment_id]; 
                    }
                    }
                    elseif($paymentMode == "BRAINTREE"){
                        if($response)
                        {
                            if(!empty($request->nonce)){
                            $log->transaction_id = $request->nonce;
                            $log->save();
                            
                            return ['responseCode' => 200, 'responseMessage' => $request->nonce];
                            }
                            else
                            {
                                return ['responseCode' => 400, 'responseMessage' => 'Nonce not found'];
                            }
                        } 
                    }
                   
                    else {
                        // return 'failed';
                        Log::warning($response->status);
                        Log::warning('Payment is failed because stripe is not giving status success');
                        return ['responseCode' => 400, 'responseMessage' => 'Payment failed'];
                    }
                } else {                    
                    Log::warning('Payment is failed because stripe is not giving blank response');
                    return ['responseCode' => 400, 'responseMessage' => 'Payment failed'];
                }
        } catch(\Exception $e) {
            return ['responseCode' => 400, 'responseMessage' => $e->getMessage()];
        }
    }


    public static function getDeliveryCharge($cityprice,$userid,$storeid,$browserKey,$request,$flag=0){
        try{            
            if($flag == 0){
                //If user current location is used, while user checkout order
                $store_details = Store::select('id','latitude','longitude')->find($storeid);        
                $address_details = User::select('id','latitude','longitude')->find($userid);

            } else {
                //If user change the addresss from checkout out screen
                $store_details = Store::select('id','latitude','longitude')->find($storeid);        
                $address_details = UserAddress::select('id','latitude','longitude','map_address','flat_no','street')->find($request->user_address_id);                
            }
            
            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$address_details->latitude.",".$address_details->longitude."&destination=".$store_details->latitude.",".$store_details->longitude."&mode=driving&key=".$browserKey;        
            $json = Helper::curl($details);            
            $details = json_decode($json, TRUE);                    
            $distancemeter = (count($details['routes'])> 0) ? $details['routes'][0]['legs'][0]['distance']['value']:0;
            $distance = abs(round($distancemeter/1609,2));

            $distancetime = (count($details['routes'])> 0) ? $details['routes'][0]['legs'][0]['duration']['value']:0;
            $time = round($distancetime / 60, 2);
   
            $delivery_charges = 0;
            if($cityprice->minimum_delivery_charge!=''){
                $delivery_charges = (float)$cityprice->minimum_delivery_charge;                    
            }
            
            if($distance > 0){
                $delivery_charges += ($distance * 0.56);
            }

            if($time > 0){
                $delivery_charges += ($time * 0.21);
            }
            
            
            return ['responseCode' => 200, 'responseMessage' => 'success', 'responseData' => $delivery_charges, 'distance' => $distance, 'duration' => $time];
        } catch(\Exception $e){
            return ['responseCode' => 400, 'responseMessage' => $e->getMessage()];
        }
    }

    public static function getCalculatorDeliveryCharge($cityprice,$storeid,$browserKey, $latitude, $longitude){
        try{          
                //If user current location is used, while user checkout order
            $store_details = Store::select('id','latitude','longitude')->find($storeid);        
            
            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$latitude.",".$longitude."&destination=".$store_details->latitude.",".$store_details->longitude."&mode=driving&key=".$browserKey;        
            $json = Helper::curl($details);            
            $details = json_decode($json, TRUE);                    
            $distancemeter = (count($details['routes'])> 0) ? $details['routes'][0]['legs'][0]['distance']['value']:0;
            $distance = abs(round($distancemeter/1609,2));
            
            $delivery_charges = 0;                    
            if(($distance >= 0)&&($distance <= 4)){
                $delivery_charges = $cityprice->minimum_delivery_charge;//4
            } else if(($distance > 4)&&($distance <= 9)){
                $delivery_charges = 8;
            } else if(($distance > 9)&&($distance <= 15)){
                $delivery_charges = 13;
            } else if(($distance > 15)&&($distance <= 24)){
                $delivery_charges = 16;
            } else if($distance > 24){
                $delivery_charges = $cityprice->maximum_delivery_charge;//25;
            }
            
            return ['responseCode' => 200, 'responseMessage' => 'success', 'responseData' => $delivery_charges, 'distance' => $distance];
        } catch(\Exception $e){
            return ['responseCode' => 400, 'responseMessage' => $e->getMessage()];
        }
    }

}