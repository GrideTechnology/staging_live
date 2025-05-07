<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Common\City;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Models\Common\GeoFence;
use App\Models\Common\CompanyCity;
use DB;
use Auth;
use Carbon\Carbon;

class GeofenceController extends Controller
{
	use Actions;

	private $model;
	private $request;
	 /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(GeoFence $model)
	{
		$this->model = $model;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		
	  $datum = GeoFence::with('city')->where('company_id', Auth::user()->company_id);
		
		if($request->has('search_text') && $request->search_text != null) {
			$datum->Search($request->search_text);
		}

		if($request->has('order_by')) {
			$datum->orderby($request->order_by, $request->order_direction);
		}
        
        if($request->has('page') && $request->page == 'all') {
            $data = $datum->get();
        } else {
            $data = $datum->paginate(10);
        }

		return Helper::getResponse(['data' => $data]);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{		
		$this->validate($request, [
			'city_id' => 'required',
			'location_name' => 'required',
			'ranges' => 'required',
		]);
		
			
			// $geofence = new GeoFence;
			// $geofence->company_id = Auth::user()->company_id;  
			// $geofence->city_id = $request->city_id;   
			// $geofence->location_name = $request->location_name; 
			// $geofence->ranges = '"'.$request->ranges.'"';
			// if($request->has('type')) $geofence->type = $request->type;              
			// $geofence->save();
			if(!empty($request->city_id)){
				
			
			try {	
								
				foreach($request->city_id as $city_id){
					
					$geofence = new GeoFence;
					$geofence->company_id = Auth::user()->company_id;  
					$geofence->city_id = $city_id;   
					$geofence->location_name = $request->location_name; 
					$geofence->ranges = $request->ranges;
					if($request->has('type')) {
						$geofence->type = $request->type;
					}  else {
						$geofence->type = NULL;
					}              
					$geofence->save();
					// $geofence = GeoFence::create([
					// 	'company_id' => Auth::user()->company_id,
					// 	'city_id' => $city_id,//,
					// 	'location_name' => $request->location_name,
					// 	'ranges' => (string)$request->ranges,
					// 	'type' => !empty($request->type)?$request->type:Null,
					// ]);						
					
					$getState = City::where(['id' => $city_id])->first();
					$price = \App\Models\Transport\RideCityPrice::where('state_id', $getState->state_id)->where('company_id', $geofence->company_id)->whereNull('city_id')->whereNull('geofence_id')->orderby('pricing_differs')->first();

					if(!empty($price)) {						
						$ridePrice = new \App\Models\Transport\RideCityPrice();
						$ridePrice->geofence_id = $geofence->id;
						$ridePrice->company_id = $geofence->company_id;  
						$ridePrice->fixed = $price->fixed;
						$ridePrice->state_id = $price->state_id;
						$ridePrice->city_id = $city_id;  
						$ridePrice->ride_delivery_vehicle_id = $price->ride_delivery_vehicle_id;  
						$ridePrice->calculator = $price->calculator;  
						$ridePrice->price = $price->price;
						$ridePrice->default_price = $price->default_price;
						$ridePrice->cancellation_fee = $price->cancellation_fee;
						$ridePrice->scheduled_cancellation_fee = $price->scheduled_cancellation_fee;
						$ridePrice->scheduled_minimum_fee = $price->scheduled_minimum_fee;
						$ridePrice->max_price = $price->max_price;
						$ridePrice->min_price = $price->min_price;
						$ridePrice->service_fee = $price->service_fee;
						$ridePrice->airport_fee = $price->airport_fee;
						$ridePrice->minute = $price->minute;
						$ridePrice->hour = $price->hour;
						$ridePrice->distance = $price->distance;
						$ridePrice->waiting_free_mins = $price->waiting_free_mins;
						$ridePrice->waiting_min_charge = $price->waiting_min_charge;
						$ridePrice->commission = $price->commission;
						$ridePrice->fleet_commission = $price->fleet_commission;
						$ridePrice->tax = $price->tax;
						$ridePrice->peak_commission = $price->peak_commission;
						$ridePrice->waiting_commission = $price->waiting_commission;
						$ridePrice->save();
					}
				}
				return Helper::getResponse(['status' => 200, 'message' => 'Success']);
			} catch(\Exception $e) { 				
				return Helper::getResponse(['status' => 400, 'message' => $e->getMessage()]);//trans('admin.create')
			} 
		// catch (ModelNotFoundException $e) {
		// 	return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		// }
		} else {			
			return Helper::getResponse(['status' => 400, 'message' => 'City can not be blank']);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Promocode  $promocode
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		try {
			$geofence = GeoFence::findOrFail($id);
			$country = CompanyCity::where('city_id', $geofence->city_id)->first();
			$geofence->country_id = $country->country_id;
			return Helper::getResponse(['data' => $geofence]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Promocode  $promocode
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'city_id' => 'required',
			'location_name' => 'required',
			'ranges' => 'required',
		]);		
		try {

			// $geofence = GeoFence::findOrFail($id);			
			// $geofence->company_id = Auth::user()->company_id;  
			// $geofence->city_id = $request->city_id;   
			// $geofence->location_name = $request->location_name; 
			// $geofence->ranges = (string)$request->ranges;

			// if($request->has('type')) {
			// 	$geofence->type = $request->type;
			// }  else {
			// 	$geofence->type = NULL;
			// }					
			// $geofence->save();
			
			foreach($request->city_id as $cityid){
				$geofence = GeoFence::findOrFail($id);			
				$geofence->company_id = Auth::user()->company_id;  
				$geofence->city_id = $cityid;   
				$geofence->location_name = $request->location_name; 
				$geofence->ranges = (string)$request->ranges;
				if($request->has('type')) {
					$geofence->type = $request->type;
				}  else {
					$geofence->type = NULL;
				}					
				$geofence->save();
				// $data = [ 
				// 	'company_id' => Auth::user()->company_id,
				// 	'city_id' => $cityid,
				// 	'location_name' => $request->location_name,
				// 	'ranges' => $request->ranges,
				// 	'type' => $type,
				// ];				
				// echo '<pre>';print_r($data);exit;
				// $data = GeoFence::where(['id' => $id,'city_id' => $cityid])->first()->update($data);			
				// echo '<pre>';print_r($data);exit;
			}
			return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);    
		} 
		catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function updateStatus(Request $request, $id)
	{
		
		try {

			$datum = GeoFence::findOrFail($id);
			
			if($request->has('status')){
				if($request->status == 1){
					$datum->status = 0;
				}else{
					$datum->status = 1;
				}
			}
			$datum->save();


			return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

		} 

		catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Promocode  $promocode
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		return $this->removeModel($id);
	}
}
