<?php

namespace App\Http\Controllers\V1\Transport\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Transport\RideRequest;
use App\Models\Transport\RideRequestDispute;
use App\Models\Transport\RideLostItem;
use App\Models\Common\Dispute;
use App\Models\Common\Setting;
use App\Models\Common\Rating;
use App\Traits\Actions;
use App\Models\Common\State;
use Auth;
use App\Services\V1\Common\UserServices;

class HomeController extends Controller
{

use Actions;

    public function trips(Request $request) {
        try{
             $jsonResponse = [];
			 $jsonResponse['type'] = 'transport';
            
             $withCallback=[ 'user' => function($query){  $query->select('id', 'first_name', 'last_name', 'rating', 'picture','currency_symbol' ); },
				'provider' => function($query){  $query->select('id', 'first_name', 'last_name', 'rating', 'picture','mobile' ); },
				'provider_vehicle' => function($query){  $query->select('id', 'provider_id', 'vehicle_make', 'vehicle_model', 'vehicle_no' ); },
				'payment' => function($query){  $query->select('ride_request_id','total','payment_mode'); }, 
				'ride' => function($query){  $query->select('id','vehicle_name', 'vehicle_image'); }, 
				'rating' => function($query){  $query->select('request_id','user_rating', 'provider_rating','user_comment','provider_comment'); }];

             $userrequest=RideRequest::select('id', 'booking_id', 'assigned_at', 's_address', 'd_address','provider_id','user_id','timezone','ride_delivery_id', 'status', 'user_rated', 'provider_rated','payment_mode', 'provider_vehicle_id','created_at','schedule_at');

             $data=(new UserServices())->userHistory($request,$userrequest,$withCallback);
            
             $jsonResponse['total_records'] = count($data);
			 $jsonResponse['transport'] = $data;
			return Helper::getResponse(['data' => $jsonResponse]);
		}

		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}

	}
	public function gettripdetails(Request $request,$id) {
		try{
			
			$jsonResponse = [];
			$jsonResponse['type'] ='transport';
			$userrequest = RideRequest::with(['provider','payment','service_type','ride','provider_vehicle','dispute'=> function($query){  
				$query->where('dispute_type','user'); 
			        },'lostItem']);
			$request->request->add(['admin_service'=>'TRANSPORT','id'=>$id]);
			$data=(new UserServices())->userTripsDetails($request,$userrequest);
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
				'dispute_name' => 'required',
				'dispute_type' => 'required',
				'provider_id' => 'required',
				'user_id' => 'required',
				'id'=>'required',
			]);
		$ride_request_dispute = RideRequestDispute::where('company_id',Auth::guard('user')->user()->company_id)
							    ->where('ride_request_id',$request->id)
								->where('dispute_type','user')
								->first(); 
         $request->request->add(['admin_service'=>'Transport']);								

		if($ride_request_dispute==null)
		{
			try{
				$disputeRequest = new RideRequestDispute;
				$data=(new UserServices())->userDisputeCreate($request, $disputeRequest);
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
		$ride_request_dispute = RideRequestDispute::with('request')->where('company_id',Auth::guard('user')->user()->company_id)
							    ->where('ride_request_id',$id)
								->where('dispute_type','user')
								->first();
		if($ride_request_dispute){						

		$ride_request_dispute->created_time=(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ride_request_dispute->created_at, 'UTC'))->setTimezone($ride_request_dispute->request->timezone)->format('d-m-Y g:i A');
		}							
		return Helper::getResponse(['data' => $ride_request_dispute]);
	}
	//Save the dispute details
	public function ride_lost_item(Request $request) {
		$ride_lost_item = RideLostItem::where('company_id',Auth::guard('user')->user()->company_id)
						  ->where('ride_request_id',$request->id)
						  ->first();
		if($ride_lost_item==null)
		{
			$this->validate($request, [ 
				'id' => 'required|numeric|exists:transport.ride_requests,id,user_id,'.Auth::guard('user')->user()->id,
				'lost_item_name' => 'required',
			]);
			try{
				$ride_lost_item = new RideLostItem;
				$ride_lost_item->ride_request_id = $request->id;
				$ride_lost_item->company_id = Auth::guard('user')->user()->company_id;  
				$ride_lost_item->user_id = Auth::guard('user')->user()->id;
				$ride_lost_item->lost_item_name = $request->lost_item_name;
				$ride_lost_item->save();
				return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
			} 
			catch (\Throwable $e) {
				return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
			}
		}else{
			return Helper::getResponse(['status' => 404, 'message' => trans('Already Lost Items Created for the Ride Request')]);
		}
	}
	public function get_ride_lost_item(Request $request,$id) {
		$ride_lost_item = RideLostItem::where('company_id',Auth::guard('user')->user()->company_id)
								->where('ride_request_id',$id)
								->first();
	     $timezone=(Auth::guard('user')->user()->state_id) ? State::find(Auth::guard('user')->user()->state_id)->timezone : '';
	     if(count($ride_lost_item) > 0){
	     	$ride_lost_item->created_time=(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ride_lost_item->created_at, 'UTC'))->setTimezone($timezone)->format(Helper::dateFormat());
	}							

							
 
		return Helper::getResponse(['data' => $ride_lost_item]);
	}
	public function getdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name','service')->where('service','TRANSPORT')->where('dispute_type','provider')->where('status','active')->get();
        return Helper::getResponse(['data' => $dispute]);
	}
	
	public function getUserdisputedetails(Request $request)
	{
		$dispute = Dispute::select('id','dispute_name','service')->where('service','TRANSPORT')->where('dispute_type','user')->where('status','active')->get();
        return Helper::getResponse(['data' => $dispute]);
	}
}
