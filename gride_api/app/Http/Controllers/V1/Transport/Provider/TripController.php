<?php

namespace App\Http\Controllers\V1\Transport\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transport\RideRequestPayment;
use App\Models\Transport\RideCity;
use Illuminate\Support\Facades\Storage;
use App\Models\Transport\RideRequestWaitingTime; 
use App\Services\V1\Common\ProviderServices;
use App\Models\Common\RequestFilter;
use App\Services\SendPushNotification;
use App\Models\Common\ProviderService;
use Illuminate\Support\Facades\Hash;
use App\Services\ReferralResource;
use App\Models\Transport\RideRequest;
use App\Models\Common\Provider;
use Location\Distance\Vincenty;
use Location\Coordinate;
use App\Models\Transport\RidePeakPrice;
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
use App\Models\Transport\RideRequestDispute;
use App\Models\Transport\RideLostItem;
use App\Traits\Actions;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Services\Transactions;
use App\Services\PaymentGateway;
use App\Models\Common\Admin;
use App\Models\Common\Chat;
use Auth;
use Log;
use DB;



class TripController extends Controller
{

    use Actions;

    public function index(Request $request)
	{
		try{

			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));

	        $siteConfig = $settings->site;

	        $transportConfig = $settings->transport;

			$Provider = Provider::with(['service'  => function($query) {  
				$query->where('admin_service', 'TRANSPORT' ); 
			}])->where('id', Auth::guard('provider')->user()->id)->first();

			$provider = $Provider->id;

			$IncomingRequests = RideRequest::with(['user', 'payment', 'chat', 'ride'])
				->where('status','<>', 'CANCELLED')
				->where('status','<>', 'SCHEDULED')
				->where('provider_rated', '0')
				->where('provider_id', $provider )->first();

			if(!empty($request->latitude)) {
				$Provider->update([
						'latitude' => $request->latitude,
						'longitude' => $request->longitude,
				]);

				//when the provider is idle for a long time in the mobile app, it will change its status to hold. If it is waked up while new incoming request, here the status will change to active
				//DB::table('provider_services')->where('provider_id',$Provider->id)->where('status','hold')->update(['status' =>'active']);
			}

			$Reason=Reason::where('type','PROVIDER')->where('service','TRANSPORT')->where('status','Active')->get();

			$referral_total_count = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_count;
			$referral_total_amount = (new ReferralResource)->get_referral('provider', Auth::guard('provider')->user()->id)[0]->total_amount;

			$Response = [
					'sos' => isset($siteConfig->sos_number) ? $siteConfig->sos_number : '911' , 
                	'emergency' => isset($siteConfig->contact_number) ? $siteConfig->contact_number : [['number' => '911']],
					'account_status' => $Provider->status,
					'service_status' => !empty($IncomingRequests) ? 'TRANSPORT':'ACTIVE',
					'request' => $IncomingRequests,
					'provider_details' => $Provider,
					'reasons' => $Reason,
					'waitingStatus' => !empty($IncomingRequests) ? $this->waiting_status($IncomingRequests->id) : 0,
					'waitingTime' => !empty($IncomingRequests) ? (int)(new Ride())->total_waiting($IncomingRequests->id) : 0,
					'referral_count' => $siteConfig->referral_count,
					'referral_amount' => $siteConfig->referral_amount,
					'ride_otp' => $transportConfig->ride_otp,
					'referral_total_count' => $referral_total_count,
					'referral_total_amount' => $referral_total_amount,
				];

			if($IncomingRequests != null){
				if(!empty($request->latitude) && !empty($request->longitude)) {
					//$this->calculate_distance($request,$IncomingRequests->id);
				}	
			}

			return Helper::getResponse(['data' => $Response ]);

		} catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}
	
	public function accept_ride(Request $request)
	{
		$this->validate($request, [
			'otp' => 'required',
		]);

		$newRequest = RideRequest::where('otp', $request->otp)->first();

		if($newRequest) {
			$provider_vehicle = ProviderVehicle::where('provider_id', $this->user->id)->where('vehicle_service_id', $newRequest->ride_delivery_id)->first();

			if($this->user->admin_id != null) {
				$newRequest->admin_id = $this->user->admin_id;
			}

			$user_request = UserRequest::where('request_id', $newRequest->id)->where('admin_service', 'TRANSPORT')->first();        
			
			$newRequest->provider_id = $this->user->id;
			if($request->admin_service == "TRANSPORT") {
				$newRequest->provider_vehicle_id = $provider_vehicle->id;
			}

			$newRequest->started_at = Carbon::now();
			$newRequest->status = "PICKEDUP";
			$newRequest->save();    
			//Send message to socket
			$requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$this->company_id, 'id' => $newRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id, 'message' => 'testing' ];

			$user_request->provider_id = $this->user->id;
			$user_request->status = $newRequest->status;
			$user_request->request_data = json_encode($newRequest);

			$publishUrl = 'newRequest';
			if($newRequest->admin_service == "TRANSPORT") $publishUrl = 'checkTransportRequest';

			app('redis')->publish($publishUrl, json_encode( $requestData ));

			$user_request->save();
			$provider = Provider::find($this->user->id);
			$provider->is_assigned = 1;
			$provider->save();

			$existingRequest =  RequestFilter::where('provider_id', $this->user->id)->first();
			if($existingRequest != null) return ['status' => 422, 'message' => trans('api.ride.request_already_scheduled')];

			$Filter = new RequestFilter;
			$Filter->admin_service = $newRequest->admin_service;
			$Filter->request_id = $user_request->id;
			$Filter->provider_id = $this->user->id; 
			$Filter->assigned = 0; 
			$Filter->company_id = $newRequest->company_id; 
			$Filter->save();

			//Send message to socket
			$requestData = ['type' => $newRequest->admin_service, 'room' => 'room_'.$newRequest->company_id, 'id' => $newRequest->id, 'city' => ($this->settings->demo_mode == 0) ? $newRequest->city_id : 0, 'user' => $newRequest->user_id ];
	
			app('redis')->publish('newRequest', json_encode( $requestData ));

			(new SendPushNotification)->RideAccepted($newRequest, strtolower($request->admin_service), trans('api.ride.request_accepted'));
			return ['status' => 200, 'message' => trans('api.ride.request_accepted'), 'data' => $newRequest  ];
		} else {
			return Helper::getResponse(['status' => 422, 'message' => 'Wrong OTP!']);
		}
	}

	public function update_ride(Request $request)
	{
		$this->validate($request, [
		 	'id' => 'required|numeric|exists:transport.ride_requests,id,provider_id,'.Auth::guard('provider')->user()->id,
			'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,NOTPAID,PAYMENT,COMPLETED'
	    ]);


		try {
			$ride = (new Ride())->updateRide($request);		
			
			return Helper::getResponse(['message' => isset($ride['message']) ? $ride['message'] : 'test' , 'data' => isset($ride['data']) ? $ride['data']: []  ]);
		} catch (Exception $e) {  
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}


	public function getDistanceByAddress($request){
		// Change address format
	    $formattedAddrFrom    = str_replace(' ', '+', $request['s_address']);
	    $formattedAddrTo     = str_replace(' ', '+', $request['d_address']);
	    
	    // Geocoding API request with start address
	    $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$request['server_key']);
	    $outputFrom = json_decode($geocodeFrom);
	    if(!empty($outputFrom->error_message)){
	        return $outputFrom->error_message;
	    }
	    
	    // Geocoding API request with end address
	    $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$request['server_key']);
	    $outputTo = json_decode($geocodeTo);
	    if(!empty($outputTo->error_message)){
	        return $outputTo->error_message;
	    }
	    
	    // Get latitude and longitude from the geodata
	    $latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
	    $longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
	    $latitudeTo        = $outputTo->results[0]->geometry->location->lat;
	    $longitudeTo    = $outputTo->results[0]->geometry->location->lng;
	    
	    // Calculate distance between latitude and longitude
	    $theta    = $longitudeFrom - $longitudeTo;
	    $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
	    $dist    = acos($dist);
	    $dist    = rad2deg($dist);
	    $miles    = $dist * 60 * 1.1515;
	    return['distance'=>$miles, 's_latitude'=> $latitudeFrom , 's_longitude'=> $longitudeFrom , 'd_latitude'=> $latitudeTo, 'd_longitude'=> $longitudeTo];
	}

	public function cancel_ride(Request $request)
	{
		$this->validate($request, [
			  'id' => 'required|numeric|exists:transport.ride_requests,id,provider_id,'.$this->user->id,
			  //'service_id' => 'required|numeric|exists:common.admin_services,id',
			  'reason'=>'required',
		   ]);

		$request->request->add(['cancelled_by' => 'PROVIDER']);

		try {
			$ride = (new Ride())->cancelRide($request);

			$provider = Provider::find($this->user->id);
			$provider->is_assigned = 0;
			$provider->save();

			return Helper::getResponse(['status' => $ride['status'], 'message' => $ride['message'], 'data' => isset($ride['data']) ? $ride['data']: []  ]);
		} catch (Exception $e) {  
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}

	public function rate(Request $request)
    {
        $this->validate($request, [
              'id' => 'required|numeric|exists:transport.ride_requests,id,provider_id,'.Auth::guard('provider')->user()->id,
              'rating' => 'required|integer|in:1,2,3,4,5',
              'comment' => 'max:255',
          ],['comment.max'=>'character limit should not exceed 255']);

        try {

        	$rideRequest = RideRequest::where('id', $request->id)->whereIn('status', ['COMPLETED','NOTPAID','DROPPED'])->firstOrFail();
        	
        	$data = (new ProviderServices())->rate($request, $rideRequest );

        	return Helper::getResponse(['status' => isset($data['status']) ? $data['status'] : 200, 'message' => isset($data['message']) ? $data['message'] : '', 'error' => isset($data['error']) ? $data['error'] : '' ]);

        } catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' =>trans('api.ride.request_not_completed') ]);
        }
    }

	public function waiting(Request $request){

		$this->validate($request, [  
			'id' => 'required|numeric|exists:transport.ride_requests,id,provider_id,'.Auth::guard('provider')->user()->id,             
		]);

		$user_id = RideRequest::find($request->id)->user_id;

		if($request->has('status') && $request->status != "" ) {

			$waiting = RideRequestWaitingTime::where('ride_request_id', $request->id)->whereNull('ended_at')->first();

			if($waiting != null) {
				$waiting->ended_at = Carbon::now();
				$waiting->waiting_mins = (Carbon::parse($waiting->started_at))->diffInSeconds(Carbon::now());
				$waiting->save();
			} else {
				$waiting = new RideRequestWaitingTime();
				$waiting->ride_request_id = $request->id;
				$waiting->started_at = Carbon::now();
				$waiting->save();
			}

			(new SendPushNotification)->ProviderWaiting($user_id, $request->status, 'transport');
		}

		return response()->json(['waitingTime' => (int)(new Ride())->total_waiting($request->id), 'waitingStatus' => (int)$this->waiting_status($request->id)]);
	}

	

	public function waiting_status($id){

		$waiting = RideRequestWaitingTime::where('ride_request_id', $id)->whereNull('ended_at')->first();

		return ($waiting != null) ? 1 : 0;
	}
	/**
	 * Get the trip history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function trips(Request $request)
	{
		try{
			$jsonResponse = [];
			$jsonResponse['type'] = 'transport';
			$request->request->add(['admin_service'=>'Transport']);
			$withCallback=[
							'user' => function($query){  $query->select('id', 'first_name', 'last_name', 'rating', 'picture','currency_symbol' ); },
							'provider' => function($query){  $query->select('id', 'first_name', 'last_name', 'rating', 'picture','currency_symbol' ); },
							'provider_vehicle' => function($query){  $query->select('id', 'provider_id', 'vehicle_make', 'vehicle_model', 'vehicle_no' ); },
							'payment' => function($query){  $query->select('ride_request_id','total','cash','card','payment_mode','payable'); }, 
							'ride' => function($query){  $query->select('id','vehicle_name', 'vehicle_image'); },
                            'rating' => function($query){  $query->select('request_id','user_rating', 'provider_rating','user_comment','provider_comment'); },
							'payment','service_type'
						  ];
		    $ProviderRequests = RideRequest::select('id', 'booking_id', 'assigned_at', 's_address', 'd_address','provider_id','user_id','timezone','ride_delivery_id', 'status', 'provider_vehicle_id','started_at');
		    $data=(new ProviderServices())->providerHistory($request,$ProviderRequests,$withCallback);
		   			  
			$jsonResponse['total_records'] = count($data);
		    $jsonResponse['transport'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	/**
	 * Get the trip history of the provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gettripdetails(Request $request,$id)
	{
		try{
			
			$jsonResponse = [];
			$jsonResponse['type'] = 'transport';
			$providerrequest = RideRequest::with(array('payment','ride','user','service_type',
			'rating'=>function($query){
				$query->select('id','request_id','user_rating', 'provider_rating','user_comment','provider_comment');
				$query->where('admin_service','TRANSPORT');
			},'dispute'=>function($query){
				$query->where('dispute_type','provider');
			}));
			$request->request->add(['admin_service'=>'TRANSPORT','id'=>$id]);
			$data=(new ProviderServices())->providerTripsDetails($request,$providerrequest);
            $jsonResponse['transport'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	//Save the dispute details
	public function ride_request_dispute(Request $request) {

		$this->validate($request, [
				'id' => 'required', 
				'user_id' => 'required',
				'provider_id'=>'required',
				'dispute_name' => 'required',
				'dispute_type' => 'required',
			]);

		$ride_request_dispute = RideRequestDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('ride_request_id',$request->id)
								->where('dispute_type','provider')
								->first();
		$request->request->add(['admin_service'=>'Transport']);						
		if($ride_request_dispute==null)
		{
			
			try{
				$disputeRequest = new RideRequestDispute;
				$data=(new ProviderServices())->providerDisputeCreate($request, $disputeRequest);
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
			} 
			catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			}
		}else{
			return Helper::getResponse(['status' => 404, 'message' => trans('Already Dispute Created for the Ride Request')]);
		}
	}

	public function get_ride_request_dispute(Request $request,$id) {
		$ride_request_dispute = RideRequestDispute::where('company_id',Auth::guard('provider')->user()->company_id)
							    ->where('ride_request_id',$id)
								->where('dispute_type','provider')
								->first();
	    $$ride_request_dispute->created_time=(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ride_request_dispute->created_at, 'UTC'))->setTimezone($ride_request_dispute->request->timezone)->format(Helper::dateFormat());								
		return Helper::getResponse(['data' => $ride_request_dispute]);
	}

	public function getdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name')->get();
        return Helper::getResponse(['data' => $dispute]);
	}
    

	public function callTransaction($request_id){  

		$UserRequest = RideRequest::with('provider')->with('payment')->findOrFail($request_id);
		
		if($UserRequest->paid==1){
			$transation=array();
			$transation['admin_service']='TRANSPORT';
			$transation['company_id']=$UserRequest->company_id;
			$transation['transaction_id']=$UserRequest->id;
			$transation['country_id']=$UserRequest->country_id;
        	$transation['transaction_alias']=$UserRequest->booking_id;		

			$paymentsRequest = RideRequestPayment::where('ride_request_id',$request_id)->first();

			$provider = Provider::where('id',$paymentsRequest->provider_id)->first();

			$fleet_amount=$discount=$admin_commision=$credit_amount=$balance_provider_credit=$provider_credit=0;                

			if($paymentsRequest->is_partial==1){
				//partial payment
				if($paymentsRequest->payment_mode=="CASH"){
					$credit_amount=$paymentsRequest->wallet + $paymentsRequest->tips;
				}
				else{
					$credit_amount=$paymentsRequest->total + $paymentsRequest->tips;
				}
			}
			else{
				if($paymentsRequest->payment_mode=="CARD" || $paymentsRequest->payment_id=="WALLET"){
					$credit_amount=$paymentsRequest->total + $paymentsRequest->tips;
				}
				else{

					$credit_amount=0;                    
				}    
			}                
			


			//admin,fleet,provider calculations
			if(!empty($paymentsRequest->commision)){

				$admin_commision=$paymentsRequest->commision;

				if(!empty($paymentsRequest->fleet_id)){
					//get the percentage of fleet owners
					$fleet_per=$paymentsRequest->fleet_percent;
					$fleet_amount=($admin_commision) * ( $fleet_per/100 );
					$admin_commision=$admin_commision;

				}

				//check the user applied discount
				if(!empty($paymentsRequest->discount)){
					$balance_provider_credit=$paymentsRequest->discount;
				}  

			}
			else{

				if(!empty($paymentsRequest->fleet_id)){
					$fleet_per=$paymentsRequest->fleet_percent;
					$fleet_amount=($paymentsRequest->total) * ( $fleet_per/100 );
					$admin_commision=$fleet_amount;
				}
				if(!empty($paymentsRequest->discount)){
					$balance_provider_credit=$paymentsRequest->discount;
				}    
			}


			if(!empty($admin_commision)){
				//add the commission amount to admin wallet and debit amount to provider wallet, update the provider wallet amount to provider table				
        		$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$admin_commision;
			   (new Transactions)->adminCommission($transation);
			}
			

			if(!empty($paymentsRequest->fleet_id) && !empty($fleet_amount)){
				$paymentsRequest->fleet=$fleet_amount;
				$paymentsRequest->save();
				//create the amount to fleet account and deduct the amount to admin wallet, update the fleet wallet amount to fleet table				
        		$transation['id']=$paymentsRequest->fleet_id;
        		$transation['amount']=$fleet_amount;
			   	(new Transactions)->fleetCommission($transation);
				                       
			}
			if(!empty($balance_provider_credit)){
				//debit the amount to admin wallet and add the amount to provider wallet, update the provider wallet amount to provider table				
        		$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$balance_provider_credit;
			   	(new Transactions)->providerDiscountCredit($transation);				
			}

			if(!empty($paymentsRequest->tax)){
				//debit the amount to provider wallet and add the amount to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$paymentsRequest->tax;
				(new Transactions)->taxCredit($transation);
			}

			if(!empty($paymentsRequest->peak_comm_amount)){
				//add the peak amount commision to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$paymentsRequest->peak_comm_amount;
				(new Transactions)->peakAmount($transation);
			}

			if(!empty($paymentsRequest->waiting_comm_amount)){
				//add the waiting amount commision to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
        		$transation['amount']=$paymentsRequest->waiting_comm_amount;
				(new Transactions)->waitingAmount($transation);
			}  
			if($credit_amount>0){               
				//provider ride amount
				//check whether provider have any negative wallet balance if its deduct the amount from its credit.
				//if its negative wallet balance grater of its credit amount then deduct credit-wallet balance and update the negative amount to admin wallet
				$transation['id']=$paymentsRequest->provider_id;
				$transation['amount']=$credit_amount;

				if($provider->wallet_balance>0){
					$transation['admin_amount']=$credit_amount-($admin_commision+$paymentsRequest->tax);

				}
				else{
					$transation['admin_amount']=$credit_amount-($admin_commision+$paymentsRequest->tax)+($provider->wallet_balance);
				}

				(new Transactions)->providerRideCredit($transation);
			}

			return true;
		}
		else{
			
			return true;
		}
		
	}

	public function createprovideraccount(Request $request){
		$gateway = new PaymentGateway('stripe');
		$account = $gateway->CreateProviderAccount();
		return Helper::getResponse(['data' => $account]);
	}

	public function TransferPayment(){
		$request = array('amount'=>1);
		$gateway = new PaymentGateway('stripe');
		$account = $gateway->TransferPayment($request);
		return Helper::getResponse(['data' => $account]);
	}
}
