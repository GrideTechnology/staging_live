<?php

namespace App\Http\Controllers\V1\Order\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage; 
use App\Models\Common\RequestFilter;

use App\Models\Order\Store;
use App\Models\Order\StoreCategory;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreItemAddon;
use App\Models\Order\StoreItem;
use App\Models\Order\StoreOrderInvoice;
use App\Models\Order\StoreOrderStatus;
use App\Models\Order\StoreOrderDispute;

use App\Services\SendPushNotification;
use App\Models\Common\ProviderService;
use Illuminate\Support\Facades\Hash;
use App\Services\ReferralResource;
use App\Services\V1\Common\ProviderServices;
use App\Models\Common\Provider;
use Location\Distance\Vincenty;
use Location\Coordinate;
use App\Models\Common\Setting;
use App\Services\V1\Transport\Ride;
use App\Models\Common\Reason;
use App\Models\Common\Rating;
use App\Models\Common\UserRequest;
use App\Models\Common\AdminService;
use App\Models\Common\User;
use App\Models\Common\Promocode;
use App\Models\Common\PromocodeUsage;
use App\Models\Common\PeakHour;
use App\Models\Common\Dispute;
use App\Traits\Actions;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Models\Common\AdminWallet;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderWallet;
use App\Models\Order\StoreWallet;
use App\Models\Common\Chat;
use Auth;
use Log;
use DB;
use App\Models\Order\StoreCuisines;
use App\Models\Order\StoreType;


class OrderController extends Controller
{
    use Actions;
    private $model;
    private $request;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(StoreOrder $model)
    {
        $this->model = $model;
    }

    public function shoptype(Request $request)
	{
		try{
			$storetype=StoreType::with('providerservice')->where('status',1)->where('company_id',Auth::guard('provider')->user()->company_id)->get();
			return Helper::getResponse(['data' => $storetype ]);
		}catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
	{
		try{
			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));

	        $siteConfig = $settings->site;
            $serviceConfig = $settings->order;
			$provider = Auth::guard('provider')->user();
			$incomingStatus = array('PROCESSING','ASSIGNED','STARTED','REACHED','PICKEDUP','ARRIVED','DELIVERED','PROVIDEREJECTED');
			$IncomingRequests = StoreOrder::with(['user','chat',
			'storesDetails' => function($query){  $query->select('id', 'store_name','store_location','store_zipcode','city_id','rating','latitude','longitude','picture','is_veg' ); },
			'orderInvoice' => function($query){  $query->select('id','store_order_id','payment_mode','gross', 'discount','promocode_amount','wallet_amount','tax_amount','delivery_amount','store_package_amount','total_amount','cash','payable','cart_details','item_price' ); }
			])
				->where('status','<>', 'CANCELLED')
				->where('status','<>', 'SCHEDULED')
				->where('provider_rated','<>', 1)
				->where('provider_id', $provider->id )
				->first();
				
			$Response = (new ProviderServices())->checkRequest($request);
			
			// if(!empty($request->latitude)) {
			// 	$provider->update([
			// 			'latitude' => $request->latitude,
			// 			'longitude' => $request->longitude,
			// 	]);

			// 	//when the provider is idle for a long time in the mobile app, it will change its status to hold. If it is waked up while new incoming request, here the status will change to active
			// 	//DB::table('provider_services')->where('provider_id',$provider->id)->where('status','hold')->update(['status' =>'active']);
			// }

			// $Reason=Reason::where('type','PROVIDER')->where('status','Active')->where('service','ORDER')->get();

			// $referral_total_count = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_count;
			// $referral_total_amount = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_amount;

			// $Response = [
			// 		'account_status' => $provider->status,
			// 		'service_status' => $provider->service ? $provider->service->status : 'OFFLINE',
			// 		'requests' => $IncomingRequests,
			// 		'provider_details' => $provider,
			// 		'reasons' => $Reason,/*
			// 		'waitingStatus' => (count($IncomingRequests) > 0) ? $this->waiting_status($IncomingRequests[0]->request_id) : 0,
			// 		'waitingTime' => (count($IncomingRequests) > 0) ? $this->total_waiting($IncomingRequests[0]->request_id) : 0,*/
			// 		'referral_count' => $siteConfig->referral_count,
			// 		'referral_amount' => $siteConfig->referral_amount,
			// 		'serve_otp' => 0,
			// 		'referral_total_count' => $referral_total_count,
			// 		'referral_total_amount' => $referral_total_amount,
			// 	];

			// if($IncomingRequests != null){
			// 	if(!empty($request->latitude) && !empty($request->longitude)) {
					
			// 	}	
			// }

			return Helper::getResponse(['data' => $Response ]);

		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function updateOrderStaus(Request $request)
	{
		$this->validate($request, [
			  'id' => 'required',
			  'status' => 'required|in:ACCEPTED,STARTED,REACHED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED,DELIVERED,PROVIDEREJECTED,PROCESSING',
		   ]);
		try{
			$setting = Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first();
			$settings = json_decode(json_encode($setting->settings_data));
			$requestId = $request->id;
	        $siteConfig = $settings->site;
			$orderConfig = isset($settings->order)?$settings->order:null;
			$otpEnableState = isset($orderConfig->serve_otp)?$orderConfig->order_otp:0;
			$serveRequest = StoreOrder::with('user','orderInvoice','store')->findOrFail($request->id);
			if($serveRequest->status == 'COMPLETED' && $serveRequest->provider_rated == 1){
				return Helper::getResponse(['status' => 500, 'message' => trans('api.push.order.already_completed'), 'error' => trans('api.push.order.already_completed') ]);
			}
			//Add the Log File for ride

			$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'ORDER' )->first();
			if($request->status == 'PAYMENT' && $serveRequest->orderInvoice->payment_mode != 'CASH') {
				$serveRequest->status = 'COMPLETED';
				// $serveRequest->paid = 0;

				(new SendPushNotification)->orderProviderComplete($serveRequest, 'order', 'Order Completed');
			} else if ($request->status == 'PAYMENT' && $serveRequest->orderInvoice->payment_mode == 'CASH') {
				
				if($serveRequest->status=='COMPLETED'){
					//for off cross clicking on change payment issue on mobile
					return Helper::getResponse(['data' => $serveRequest ]);
				}				
				$serveRequest->status = 'COMPLETED';
				$serveRequest->paid = 1;
				(new SendPushNotification)->orderProviderComplete($serveRequest, 'order', 'Order Completed');
				//for completed payments
				$RequestPayment = StoreOrderInvoice::where('store_order_id', $request->id)->first();
				$RequestPayment->status = 1;
				$RequestPayment->payable = 0; 
				$RequestPayment->save();               

			} else {
				$serveRequest->status = $request->status;
				if($request->status == 'STARTED'){
					(new SendPushNotification)->orderProviderStarted($serveRequest, 'order', 'Order Started');
				}
				if($request->status == 'REACHED'){
					(new SendPushNotification)->orderProviderReached($serveRequest, 'order', 'Order Reached');
				}
			}
			if($request->status == 'PICKEDUP'){
				$serveRequest->status = $request->status;
				(new SendPushNotification)->orderProviderPickedup($serveRequest, 'order', 'Order Pickedup');
			}
			if($request->status == 'ARRIVED'){
				$serveRequest->status = $request->status;
				(new SendPushNotification)->orderProviderArrived($serveRequest, 'order', 'Order Arrived');
			}
			if($request->status == 'DELIVERED'){
				
				$serveRequest->status = $request->status;
				if($otpEnableState == 1 && $request->has('otp')){
					if($request->otp == $serveRequest->order_otp){				
						(new SendPushNotification)->orderProviderConfirmPay($serveRequest, 'order', 'Order Payment Confirmation');
					}else{
						return Helper::getResponse(['status' => 500, 'message' => trans('api.otp'), 'error' => trans('api.otp') ]);
					}
				}else{
					(new SendPushNotification)->orderProviderConfirmPay($serveRequest, 'order', 'Order Payment Confirmation');
				}
			}	
			if($request->status == 'PAYMENT') {
				$chat=Chat::where('admin_service', $serveRequest->admin_service)->where('request_id', $requestId)->where('company_id', Auth::guard('provider')->user()->company_id)->first();

				if($chat != null) {
					$chat->delete();
				}
				if($otpEnableState == 1 && $request->has('otp')){
					if($request->otp == $serveRequest->order_otp){							
						$serveRequest->save();
						$serveRequest = StoreOrder::with('user','orderInvoice','store')->findOrFail($user_request->request_id);
						(new SendPushNotification)->orderProviderConfirmPay($serveRequest, 'order', 'Order Payment Confirmation');
					}else{
						return Helper::getResponse(['status' => 500, 'message' => trans('api.otp'), 'error' => trans('api.otp') ]);
					}
				}else{
					$serveRequest->save();	
					$serveRequest = StoreOrder::with('user','orderInvoice','store')->findOrFail($user_request->request_id);
					(new SendPushNotification)->orderProviderConfirmPay($serveRequest, 'order', 'Order Payment Confirmation');
				}

			}
			$serveRequest->save();
			$serveRequest = StoreOrder::with('user','orderInvoice','store')->findOrFail($requestId);
			
			if($user_request != null){
				$user_request->provider_id = $serveRequest->provider_id;
				$user_request->status = $serveRequest->status;
				$user_request->request_data = json_encode($serveRequest);

				$user_request->save();
			}
			//for completed payments
			if(($serveRequest->status == 'COMPLETED') && ($serveRequest->paid == 1)){				
				\Log::info('Initiate transaction with status:' .$serveRequest->status);
				$this->callTransaction($request->id);			
			}
			//Send message to socket
			$requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::guard('provider')->user()->company_id, 'id' => $serveRequest->id, 'city' => ($setting->demo_mode == 0) ? $serveRequest->store->city_id : 0, 'user' => $serveRequest->user_id ];
			app('redis')->publish('checkOrderRequest', json_encode( $requestData ));
			app('redis')->publish('newRequest', json_encode( $requestData ));

			// Send Push Notification to User
	   
			return Helper::getResponse(['data' => $serveRequest ]);

		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.unable_accept'), 'error' => $e->getMessage() ]);
		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.connection_err'), 'error' => $e->getMessage() ]);
		}
	}

	
	public function createDispute(Request $request)
	{
		$this->validate($request, [
            'id' => 'required|integer|exists:order.store_orders,id,provider_id,'.Auth::guard('provider')->user()->id,
            'reason' => 'required',
        ]);

		$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));

        $siteConfig = $settings->site;
        $transportConfig = $settings->service;
		$orderRequest = StoreOrder::findOrFail($request->id);

		$user_request = UserRequest::where('request_id', $request->id)->where('admin_service', 'ORDER' )->first();
		try{
			$serviceDelete = RequestFilter::where('admin_service' , 'ORDER')->where('request_id', $user_request->id)->where('provider_id' , Auth::guard('provider')->user()->id)->first();
			if($serviceDelete != null){
				if($request->reason != null) {
					$storedisputedata=StoreOrderDispute::where('store_order_id',$user_request->request_id)->get();
					if(count($storedisputedata) ==0){
						
					}
				}
				//Send message to socket
				$requestData = ['type' => 'ORDER', 'room' => 'room_'.Auth::guard('provider')->user()->company_id, 'id' => $orderRequest->id, 'user' => $orderRequest->user_id ];
				app('redis')->publish('checkOrderRequest', json_encode( $requestData ));
				
				return Helper::getResponse(['message' => trans('api.order.request_rejected') ]);
			}else{
				return Helper::getResponse(['status' => 500, 'message' => trans('api.order.something_went_wront'), 'error' =>trans('api.order.something_went_wront') ]);
			}
		} catch(\Throwable $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage() ]);
		}
	}

	public function rate(Request $request)
    {

        $this->validate($request, [
				'rating' => 'required|integer|in:1,2,3,4,5',
				'comment' => 'max:255',
            ],['comment.max'=>'character limit should not exceed 255']); 

        try {

        	$orderRequest = StoreOrder::where('id', $request->id)->where('status', 'COMPLETED')->firstOrFail();
        	 //echo 'fgdgd'; exit;
        	$data = (new ProviderServices())->rate($request, $orderRequest );

        	return Helper::getResponse(['status' => isset($data['status']) ? $data['status'] : 200, 'message' => isset($data['message']) ? $data['message'] : '', 'error' => isset($data['error']) ? $data['error'] : '' ]);

        } catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.order.request_not_completed'), 'error' =>trans('api.order.request_not_completed') ]);
        } 
	}
	 
	 /**
	 * Get the service history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function historyList(Request $request)
	{
		try{
			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));
			$providerId = Auth::guard('provider')->user()->id;
			$siteConfig = $settings->site;
			$jsonResponse = [];
			$jsonResponse['type'] = 'order';
			
			$OrderRequests = StoreOrder::with(['rating' => function($query){  $query->select('request_id','user_rating', 'provider_rating','user_comment','provider_comment','store_comment','store_rating'); }])->select('id','store_order_invoice_id','store_id','user_id','provider_id','admin_service','company_id','pickup_address','delivery_address','created_at','assigned_at','status','timezone',DB::raw('(SELECT total_amount FROM store_order_invoices WHERE store_order_id=store_orders.id) as total'))
					->where('provider_id', $providerId)
					->where('status', 'COMPLETED');
			if($request->has('limit')) {
				$OrderRequests = $OrderRequests->orderBy('created_at','desc')->take($request->limit)->offset($request->offset)->get();
			}else{ 
				    $OrderRequests=$OrderRequests->with('user','orderInvoice','storesDetails');
				    $OrderRequests->orderby('id','desc');
                 
                 if($request->has('search_text') && $request->search_text != null) {
                        
			            $OrderRequests->ProviderhistorySearch($request->search_text);
			        }

			        if($request->has('order_by')) {

			            $OrderRequests->orderby($request->order_by, $request->order_direction);
			        }

				$OrderRequests = $OrderRequests->paginate(10);
			}
			$jsonResponse['total_records'] = count($OrderRequests);
			if(!empty($OrderRequests)){
				$map_icon_start = '';
				//asset('asset/img/marker-start.png');
				$map_icon_end = '';
				//asset('asset/img/marker-end.png');
				foreach ($OrderRequests as $key => $value) {
					
					$ratingQuery = Rating::select('id','user_rating','provider_rating','store_rating','user_comment','provider_comment')->where('admin_service', 'ORDER')
										->where('request_id',$value->id)->first();
					$OrderRequests[$key]->rating = $ratingQuery;
					$cuisineQuery = StoreCuisines::with('cuisine')->where('store_id',$value->store_id)->get();
					if(count($cuisineQuery)>0){
						foreach($cuisineQuery as $cusine){
							$cusines_list [] = $cusine->cuisine->name;
						}
					}
					$cuisinelist = implode($cusines_list,',');
					$OrderRequests[$key]->cuisines = $cuisinelist;
					$OrderRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
							"autoscale=1".
							"&size=600x300".
							"&maptype=terrian".
							"&format=png".
							"&visual_refresh=true".
							"&markers=icon:".$map_icon_start."%7C".$value->pickup->latitude.",".$value->pickup->longitude.
							"&markers=icon:".$map_icon_end."%7C".$value->delivery->latitude.",".$value->delivery->longitude.
							"&path=color:0x000000|weight:3|enc:".$value->route_key.
							"&key=".$siteConfig->server_key;
				}
			}
			$jsonResponse['order'] = $OrderRequests;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	/**
	 * Get the service history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getOrderHistorydetails(Request $request,$id)
	{
		try{
			
			$jsonResponse = [];
			$jsonResponse['type'] = 'order';
			$providerrequest = StoreOrder::with(array('orderInvoice'=>function($query){
				$query->select('id','store_order_id','gross','wallet_amount','total_amount','payment_mode','tax_amount','delivery_amount','promocode_amount','store_package_amount','payable','cart_details','discount','cash',"item_price");
			},'user'=>function($query){
				$query->select('id','first_name','last_name','rating','picture','mobile','currency_symbol');
			},'dispute'=>function($query){
				$query->where('dispute_type','provider');
			},'rating' => function($query){  $query->select('request_id','user_rating', 'provider_rating','user_comment','provider_comment','store_comment','store_rating'); }))
			->select('id','store_order_invoice_id','user_id','provider_id','admin_service','company_id','pickup_address','delivery_address','created_at','timezone','status');
			$request->request->add(['admin_service'=>'ORDER','id'=>$id]);
			$data=(new ProviderServices())->providerTripsDetails($request,$providerrequest);
			$jsonResponse['order'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}

	//Save the dispute details
	public function saveOrderRequestDispute(Request $request) {
		$this->validate($request, [
				'id' => 'required', 
				'user_id' => 'required',
				'provider_id'=>'required',
				'dispute_name' => 'required',
				'dispute_type' => 'required',
			]);
		
		$order_request_dispute = StoreOrderDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('store_order_id',$request->id)
								->where('dispute_type','provider')
								->first();
	    $request->request->add(['admin_service'=>'ORDER']);								
		if($order_request_dispute==null)
		{
			
			try{
				$disputeRequest = new StoreOrderDispute;
				$data=(new ProviderServices())->providerDisputeCreate($request, $disputeRequest);
				
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
			} 
			catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			}
		}else{
			return Helper::getResponse(['status' => 404, 'message' => trans('Already Dispute Created for the Service Request')]);
		}
	}

	public function getOrderRequestDispute(Request $request,$id) {
		$order_request_dispute = StoreOrderDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('store_order_id',$id)
								->where('dispute_type','provider')
								->first();
			$order_request_dispute->created_time=(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $order_request_dispute->created_at, 'UTC'))->setTimezone(Auth::guard('provider')->user()->timezone)->format(Helper::dateFormat()); 					
		return Helper::getResponse(['data' => $order_request_dispute]);
	}

	public function getdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name','service')->where('service','ORDER')->where('dispute_type','provider')->where('status','active')->get();
        return Helper::getResponse(['data' => $dispute]);
	}
	
	public function getUserdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name','service')->where('service','ORDER')->where('dispute_type','user')->where('status','active')->get();
        return Helper::getResponse(['data' => $dispute]);
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

			$credit_amount=$paymentsStore->commision_amount;//$paymentsStore->total_amount-$paymentsStore->commision_amount-$paymentsStore->delivery_amount;	
					
			//admin,shop,provider calculations
			if(!empty($paymentsStore->gride_commission_amount)){//commision_amount
				// $admin_commision=$paymentsStore->commision_amount;//commision_amount
        		$transation['id']=$StoreOrder->store_id;
        		$transation['amount']=$paymentsStore->gride_commission_amount;//commision_amount;
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
        \Log::info('shopCreditDebit called');
        $amount=$request['amount'];
        // $ad_det_amt= -1 * abs($request['amount']);                            
        // $request['transaction_desc']='Order amount sent';
        // $request['transaction_type']=10;       
        // $request['type']='D';
        // $request['amount']=$ad_det_amt;
        // $this->createAdminWallet($request);
                    
        $request['transaction_desc']='Order amount recevied';
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$amount;
        $this->createShopWallet($request);

        // $request['transaction_desc']='Order amount recharge';
        // $request['transaction_type']=11;
        // $request['type']='C';
        // $request['amount']=$amount;
        // $this->createAdminWallet($request);

        return true;
    }

    protected function providerCredit($request){
                                    
        $request['transaction_desc']='Order delivery amount sent';
        $request['id']=$request['id'];
        $request['type']='C';
        $request['amount']=$request['amount'];
        $this->createProviderWallet($request);       
        
        // $ad_det_amt= -1 * abs($request['amount']);                  
        // $request['transaction_desc']='Order delivery amount recharge';
        // $request['transaction_type']=9;
        // $request['type']='D';
        // $request['amount']=$ad_det_amt;
        // $this->createAdminWallet($request);

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

	protected function createShopWallet($request){
	    \Log::info('creatShopWallet called');
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

}
