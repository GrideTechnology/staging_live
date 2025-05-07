<?php

namespace App\Services;

use App\Models\Common\Provider;
use App\Models\Common\Setting;
use Illuminate\Http\Request;
use App\Models\Common\User;
use App\Models\Order\Store;
use App\Jobs\PushNotificationJob;
use Exception;
use App\Helpers\Helper;



class SendPushNotification
{

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request, $type = null,$message=null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);
        return $this->sendPushToUser($request->user_id, $type, $message, 'Ride Accepted', $request);
    }

    public function ProviderAssign($provider, $type = null){

        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider, $type, trans('api.push.request_assign')  );
    }

    public function UserStatus($user, $type, $message){

        $user = User::where('id',$user)->first();
        if($user->language){
            $language = $user->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($user, $type, $message   );
    }

    public function providerCreateCall($request, $type = null,$message=null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);
        return $this->sendPushToUser($request->user_id, $type, $message, 'Call from provider.' );
    }

    public function userCreateCall($request, $type = null,$message=null){

        if($request->provider_id!=''){
            $user = Provider::where('id',$request->provider_id)->first();
            $language = $user->language;
            app('translator')->setLocale($language);
            return $this->sendPushToProvider($request->provider_id, $type, $message, 'Call from user.' );
        }else{
            return Helper::getResponse(['status' => 400,'message' => 'Provider not assigned']);
        }
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function user_schedule($user, $type = null) {
         $user = User::where('id',$user)->first();
         $language = $user->language;
         app('translator')->setLocale($language);
        return $this->sendPushToUser($user, $type, trans('api.push.schedule_start')  );
    }

    public function user_scheduled($user, $scheduled_at, $pickup_point, $drop_point, $type = null) {
         $user = User::where('id',$user)->first();
         $language = $user->language;
         app('translator')->setLocale($language);
         $message = 'Hello '.$user->first_name.',\n'. ' Your ride has been schedule at '.$scheduled_at. ' from '.$pickup_point. ' to '.$drop_point;
        return $this->sendPushToUser($user->id, $type, $message );
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function provider_schedule($provider, $type = null){
        exit;

        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider->id, $type, trans('api.push.schedule_start')  );

    }

    public function provider_scheduled($provider, $scheduled_at, $pickup_point, $drop_point, $type = null){

        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        $message = 'Hello '.$provider->first_name.',\n'. ' Ride has been schedule at '.$scheduled_at. ' from '.$pickup_point. ' to '.$drop_point;
        return $this->sendPushToProvider($provider->id, $type, $message );

    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function UserCancelRide($request, $type = null){

        if(!empty($request->provider_id)){

            $provider = Provider::where('id',$request->provider_id)->first();

            if($provider->language){
                $language = $provider->language;
                app('translator')->setLocale($language);
            }

            return $this->sendPushToProvider($request->provider_id, $type, trans('api.push.user_cancelled'), 'Request Cancelled', ''  );
        }
        
        return true;    
    } 

    public function StoreCanlled($request, $type = null){

        if(!empty($request->user_id)){

            $user = user::where('id',$request->user_id)->first();

            if($user->language){
                $language = $user->language;
                app('translator')->setLocale($language);
            }

            return $this->sendPushToUser($request->user_id, $type, trans('api.push.Cancelled'), 'Store Cancelled');
        }
        
        return true;    
    }

    public function ProviderWaiting($user_id, $status, $type = null){

        $user = User::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        if($status == 1) {
            return $this->sendPushToUser($user_id, $type, trans('api.push.provider_waiting_start'), 'Provider Waiting'  );
        } else {
            return $this->sendPushToUser($user_id, $type, trans('api.push.provider_waiting_end'), 'Provider Waiting'   );
        }
        
    }


    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function ProviderCancelRide($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.provider_cancelled'), 'Provider Cancelled Ride'   );
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Arrived($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.arrived'), 'Ride Arrived'  );
    }

    /**
     * Driver Picked You  in your location.
     *
     * @return void
     */
    public function Pickedup($request, $type = null){
        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.pickedup'), 'Ride Pickedup'  );
    }

    /**
     * Driver Reached  destination
     *
     * @return void
     */
    public function Dropped($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);
        
        return $this->sendPushToUser($request->user_id, $type, trans('api.push.dropped')." ".$request->currency.$request->payment->payable.' by '.$request->payment_mode, 'Ride Dropped');
    }

    /**
     * Your Ride Completed
     *
     * @return void
     */
    public function Complete($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.complete'), 'Ride Completed');
    }

    
     
    /**
     * Rating After Successful Ride
     *
     * @return void
     */
    public function Rate($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.rate')  );
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function ProviderNotAvailable($user_id, $type = null){
        $user = User::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($user_id, $type, trans('api.push.provider_not_available')  );
    }

    /**
     * New Incoming request
     *
     * @return void
     */

     public function OrderAcceptedRespond($userid, $order, $type = null, $title = null){

        $user = User::where('id',$userid)->first();
        if($user->language){
            $language = $user->language;
            app('translator')->setLocale($language);
        }

         
        $lan='Your order is accepted by the shop.';
        return $this->sendPushToUser($user->id, $type,$lan, $lan, $order);

    }

    public function IncomingRequest($provider, $type = null, $title = null, $newRequest=array()){

        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

         if($type=="TRANSPORT"){
           $lan=trans('api.push.incoming_request');
         }else if($type=="SERVICE"){
           $lan=trans('api.push.service.incoming_request');
         } else{
           $lan=trans('api.push.order.incoming_request');
         }
        return $this->sendPushToProvider($provider->id, $type,$lan, $title, $newRequest);

    }

    public function OrderIncomingRequest($provider, $type = null, $newRequest=array() ){
       
        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        $title ='New Incoming Request.'; 
        $lan='New Incoming Request. \nOrder is processing and will be ready for pickup in next '.$newRequest->order_ready_time.'minutes.';
        return $this->sendPushToProvider($provider->id, $type, $title, $lan, $newRequest);

    }



    public function ShopRequest($shop, $type = null, $title = null){

        $provider = Store::where('id',$shop)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        $message=trans('api.push.order.incoming_request');

        return $this->sendPushToShop($shop, $type,$message, $title, ''  );

    }

    public function ShopCancelRequest($shop, $type = null, $title = null){

        $provider = Store::where('id',$shop)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        $message=trans('api.push.order.incoming_request');

        return $this->sendPushToShop($shop, $type,$message, $title, ''  );

    }

    public function ChatPushProvider($provider, $type = null){
        
        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }
        
        return $this->sendPushToProvider($provider->id, $type, trans('api.push.chat_message'));

    }

    public function ChatPushUser($user, $type = null){
      
        $user = User::where('id',$user)->first();
        if($user->language){
            $language = $user->language;
            app('translator')->setLocale($language);
        }
                
        return $this->sendPushToUser($user->id, $type, trans('api.push.chat_message'));

    }
    

    /**
     * Driver Documents verfied.
     *
     * @return void
     */
    public function DocumentsVerfied($provider_id, $type = null){

        $provider = Provider::where('id',$provider_id)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider_id, $type, trans('api.push.document_verfied')  );
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function WalletMoney($user_id, $money, $type = null, $title = null, $data = null){

        $user = User::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);
        return $this->sendPushToUser($user_id, $type, $money.' '.trans('api.push.added_money_to_wallet'), $title, $data  );
    }

    public function ProviderWalletMoney($user_id, $money, $type = null, $title = null, $data = null){

        $user = Provider::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToProvider($user_id, $type, $money.' '.trans('api.push.added_money_to_wallet'), $title, $data  );
    }

    /**
     * Money charged from user wallet.
     *
     * @return void
     */
    public function ChargedWalletMoney($user_id, $money, $type = null){

        $user = User::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($user_id, $type, $money.' '.trans('api.push.charged_from_wallet')  );

    }

    public function updateProviderStatus($provider_id, $type = null,$message){

        $provider = Provider::where('id',$provider_id)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider_id, $type, $message  );

    }

      public function adminAddamount($provider_id, $type = null,$message,$amount){

        $provider = Provider::where('id',$provider_id)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider_id, $type, $message.' '.$provider->currency_symbol.$amount);

    }


    public function provider_hold($provider_id, $type = null){

        $provider = Provider::where('id',$provider_id)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider_id, $type, trans('api.push.provider_status_hold')  );

    }


    /*
    *  SERVICE TYPE PUSH NOTIFICATIONS
    */ 
    /**
     * New Incoming request
     *
     * @return void
     */
    public function serviceIncomingRequest($provider, $type = null){

        $provider = Provider::where('id',$provider)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }

        return $this->sendPushToProvider($provider->id, $type, trans('api.push.service.incoming_request')  );

    }

     public function serviceProviderCancel($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.service.provider_cancelled'), 'Provider Cancelled Service'   );
    }

    public function serviceUserCancel($request, $type = null){

        if(!empty($request->provider_id)){

            $provider = Provider::where('id',$request->provider_id)->first();

            if($provider->language){
                $language = $provider->language;
                app('translator')->setLocale($language);
            }

            return $this->sendPushToProvider($request->provider_id, $type, trans('api.push.service.user_cancelled'), 'Request Cancelled', ''  );
        }
        
        return true;    
    }
     /**
     * Provider Not Available
     *
     * @return void
     */
    public function serviceProviderNotAvailable($user_id, $type = null){
        $user = User::where('id',$user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($user_id, $type, trans('api.push.service.provider_not_available'));
    }
    /**
     * Service provider Arrived at your location.
     *
     * @return void
     */
    public function serviceProviderArrived($request, $type = null){
        if($request != null){
            $user = User::where('id',$request->user_id)->first();
            $language = $user->language;
            app('translator')->setLocale($language);
            $serviceAlias = isset($request->service->serviceCategory)? $request->service->serviceCategory->alias_name:'';
            $message = $serviceAlias .' ' . trans('api.push.service.arrived');
            return $this->sendPushToUser($request->user_id, $type, $message );
        }else{
            return false;
        }
    }
    /**
     * Your Service Completed
     *
     * @return void
     */
    public function serviceProviderComplete($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.service.complete')  );
    }
    /**
     * Provider Picked up service in your location.
     *
     * @return void
     */
    public function serviceProviderPickedup($request, $type = null){
        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.service.pickedup')  );
    }

     /**
     * Service provider end service
     *
     * @return void
     */
    public function serviceProviderDropped($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.service.dropped')." ".$request->currency.$request->payment->payable.' by '.$request->payment_mode  );

    }
    /**
     * confirmed the payment
     *
     * @return void
     */
    public function serviceProviderConfirmPay($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.service.confirmpay') ." ".$request->currency.$request->payment->payable.' by '.$request->payment_mode  );

    }
    /*
    *  ORDER PUSH NOTIFICATIONS
    */

    public function OrderPrepared($request , $type, $message){
        
        $provider = Provider::where('id',$request->provider_id)->first();
        if($provider->language){
            $language = $provider->language;
            app('translator')->setLocale($language);
        }
        return $this->sendPushToProvider($request->provider_id, $type, 'Order Prepared and ready to pickup', $message, ''  );
    }

    public function orderProviderStarted($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

       return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.started')." ".$request->currency.$request->orderInvoice->cash .' by '.$request->orderInvoice->payment_mode  );

    }
    

    public function orderProviderReached($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

       return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.reached')." ".$request->currency.$request->orderInvoice->cash .' by '.$request->orderInvoice->payment_mode  );

    }

    


    public function orderProviderPickedup($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

       return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.pickedup')." ".$request->currency.$request->orderInvoice->cash .' by '.$request->orderInvoice->payment_mode  );

    }

    public function orderProviderArrived($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

       return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.arrived')." ".$request->currency.$request->orderInvoice->cash .' by '.$request->orderInvoice->payment_mode  );

    }
    public function orderProviderConfirmPay($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

       return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.confirmpay')."  ".$request->currency.$request->orderInvoice->cash .' by '.$request->orderInvoice->payment_mode  );

    }
        /**
     * Your Order Completed
     *
     * @return void
     */
    public function orderProviderComplete($request, $type = null){

        $user = User::where('id',$request->user_id)->first();
        $language = $user->language;
        app('translator')->setLocale($language);

        return $this->sendPushToUser($request->user_id, $type, trans('api.push.order.complete')  );
    }

    public function UserCancelOrder($request, $type = null){
        if(!empty($request->provider_id)){
            $provider = Provider::where('id',$request->provider_id)->first();
            if($provider->language){
                $language = $provider->language;
                app('translator')->setLocale($language);
            }
            return $this->sendPushToProvider($request->provider_id, $type, trans('api.push.user_cancelled'), 'Request Cancelled', ''  );
        }        
        return true;    
    } 



    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id, $topic, $push_message, $title = null, $data = null){

        try{                 

            $user = User::findOrFail($user_id);

            $settings_data = Setting::where('company_id', $user->company_id)->first();

            $settings = json_decode(json_encode($settings_data->settings_data));

            if($title == null) $title = $settings->site->site_title;
            // $data = ['test_message' => 'test message'];
            if($data == null) $data = new \stdClass();

                $msgData = [                        
                    'title' => $title,
                    'body' =>  $push_message,
                    'badge' => 1,
                    'sound' => 'default',                                        
                    'type' => 'new-message-received',            
                    'id' => 0,
                ];

                if(isset($data->admin_service)){
                    if($data->admin_service=='TRANSPORT' ){
                        $msgData['ride_id'] = $data->id;
                    }

                    if($data->admin_service=='ORDER'){
                        $msgData['order_id'] = $data->id;
                    }
                }

            
            if(!empty($user->device_token)){                                
                if($user->device_type == 'ANDROID'){
                    // dispatch(new PushNotificationJob($topic, $push_message, $title, $data, $user, $settings, 'user'));
                    Helper::sendPushAndroid($user->device_token,$msgData,$settings);
                } else {                                       
                    Helper::sendPushIOS($user->device_token,$msgData,$settings);
                }
            } 
        

        } catch(Exception $e){                    
            return $e;
        }

    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToProvider($provider_id, $topic, $push_message, $title = null, $data = null){
              
            $user = Provider::findOrFail($provider_id);         

            $settings_data = Setting::where('company_id', $user->company_id)->first();

            $settings = json_decode(json_encode($settings_data->settings_data));

            if($title == null) $title = $settings->site->site_title;

            if($data == null) $data = new \stdClass();            

            $msgData = [                        
                'title' => $title,
                'body' =>  $push_message,
                'badge' => 1,
                'sound' => 'default',                                        
                'type' => 'new-message-received',            
                'id' => 0,
            ];

            if(isset($data->admin_service)){
                    if($data->admin_service=='TRANSPORT' ){
                        $msgData['ride_id'] = $data->id;
                    }

                    if($data->admin_service=='ORDER'){
                        $msgData['order_id'] = $data->id;
                    }
                }

            if(!empty($user->device_token)){                
                if($user->device_type == 'ANDROID'){
                    // dispatch(new PushNotificationJob($topic, $push_message, $title, $data, $user, $settings, 'provider'));
                    Helper::sendPushAndroid($user->device_token,$msgData,$settings);
                } else {                                        
                   Helper::sendPushIOS($user->device_token,$msgData,$settings);
                }
            }
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToShop($shop_id, $topic, $push_message, $title = null, $data = null){

        try{ 
                 

            $user = Store::findOrFail($shop_id);         

            $settings_data = Setting::where('company_id', $user->company_id)->first();

            $settings = json_decode(json_encode($settings_data->settings_data));

            if($title == null) $title = $settings->site->site_title;

            if($data == null) $data = new \stdClass();

            // if($user->device_token != ""){
            //     dispatch(new PushNotificationJob($topic, $push_message, $title, $data, $user, $settings, 'shop'));
            // }

            $msgData = [                        
                'title' => $title,
                'body' =>  $push_message,
                'badge' => 1,
                'sound' => 'default',                                        
                'type' => 'new-message-received',            
                'id' => 0,
            ];

            if(!empty($user->device_token)){                
                if($user->device_type == 'ANDROID'){
                    // dispatch(new PushNotificationJob($topic, $push_message, $title, $data, $user, $settings, 'provider'));
                    Helper::sendPushAndroid($user->device_token,$msgData,$settings);
                }
                elseif($user->device_type == 'WEB' || $user->device_type == ''){
                    Helper::sendPushWeb($user->device_token,$msgData,$settings);
                } else {                                        
                    Helper::sendPushIOS($user->device_token,$msgData,$settings);
                }
            }

            

        } catch(Exception $e){           
            return $e;
        }

    }

}


