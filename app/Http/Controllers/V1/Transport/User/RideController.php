<?php

namespace App\Http\Controllers\V1\Transport\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SendPushNotification;
use App\Models\Transport\RideDeliveryVehicle;
use App\Models\Transport\RideCars;
use App\Models\Transport\RideCarsImages;
use App\Models\Transport\RideCarsType;
use App\Models\Transport\RideRental;
use App\Models\Transport\RideRentalCharges;
use App\Models\Common\RequestFilter;
use App\Models\Transport\RideRequest;
use App\Models\Common\UserRequest;
use App\Models\Transport\RideType;
use App\Models\Common\Provider;
use App\Models\Common\Country;
use App\Models\Common\Rating;
use App\Services\V1\Transport\Ride;
use App\Models\Common\Setting;
use App\Models\Common\Reason;
use App\Models\Common\State;
use App\Models\Common\User;
use App\Models\Common\Menu;
use App\Models\Common\Card;
use App\Models\Transport\RideCityPrice;
use App\Models\Transport\RidePeakPrice;
use App\Models\Common\PeakHour;
use App\Models\Common\AdminService;
use App\Models\Transport\RideLostItem;
use App\Models\Transport\RideRequestDispute;
use App\Models\Transport\RideRequestPayment;
use App\Models\Common\ProviderService;
use App\Models\Common\CompanyCountry;
use App\Models\Common\Promocode;
use App\Services\PaymentGateway;
use App\Services\V1\Common\UserServices;
use App\Services\V1\Common\ProviderServices;
use App\Models\Common\PaymentLog;
use App\Services\V1\Order\Order;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\V1\Transport\Provider\TripController;
use App\Http\Controllers\V1\Common\Provider\HomeController;
use App\Models\Common\City;
use App\Models\Common\GeoFence;
use Carbon\Carbon;
use App\Traits\Actions;
use Auth;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;

class RideController extends Controller
{
	use Actions;

	
	public function services(Request $request)
	{        
		$this->validate($request, [
			'type' => 'required|numeric|exists:transport.ride_types,id',
			'latitude' => 'required|numeric',
			'longitude' => 'required|string'
		]);

		$transport= new \stdClass;

		$distance = isset($this->settings->transport->provider_search_radius) ? $this->settings->transport->provider_search_radius : 100;
     
		$ride_delivery_vehicles = [];

		$callback = function ($q) use($request) {
			$q->where('admin_service', 'TRANSPORT');
			$q->where('category_id',$request->type);
		};

		$withCallback = ['service' => $callback, 'service.ride_vehicle','provider_vehicle'];
		$whereHasCallback = ['service' => $callback];

		$data = (new UserServices())->availableProviders($request, $withCallback, $whereHasCallback);
		//print_r($data); exit;
		$service = null;
		$providers = []; 
		$nearestProvider = []; 
		//here added new code
		// $transport->mkdata = $data;       

		//List providers in nearestProvider variable (result is ordered ascending based on distance)
		// $transport->data_of_providers =$data;
		$result = [];
		foreach($data as $datum) {
			//new code for getting multiple arrays
			if($datum->service != null) {
				foreach ($datum->provider_vehicle as $vehicle) {
					$vehicleServiceId = $vehicle['vehicle_service_id'];
					// Check if the vehicle_service_id is not already in the result array
                    if (!in_array($vehicleServiceId, $result)) {
                        $result[] = $vehicleServiceId;
                    }
				}
			}
			if($datum->service != null) {
				if($datum->latitude!='' && $datum->longitude){
					$nearestProvider[] = [ 'service_id' => $datum->service->ride_delivery_id, 'latitude' => $datum->latitude, 'longitude' => $datum->longitude ];
				}
				$service = $datum->service->ride_delivery_id;
				$ride_delivery_vehicles[] = $service;

			}

			$provider = new \stdClass();
			foreach (json_decode($datum) as $l => $val) {
				$provider->$l = $val;
			}
			$provider->service_id = $service;
			$providers[] = $provider;
		}
		$transport->result_array = $result;
		$ride_delivery_vehicles = $result;		
		$output=[];
		foreach ($nearestProvider as $near) {			
			$sources = [];
		    $destinations = [];
			$sources[] = $near['latitude'].','.$near['longitude'];
			$destinations[] = $request->latitude.','.$request->longitude;			
			$output[] = Helper::getDistanceMap($sources, $destinations);			
		}

        
		$output=array_replace_recursive($output);
        $dis=[];

		if(count($output) > 0) {
			foreach ($output as $key => $data) {
				// dd($nearestProvider);
				if($data->status == "OK"){
					if(array_key_exists('duration', $data->rows[0]->elements[0])){
					//if($data->rows[0]->elements[0]->duration!)
						$estimations[$nearestProvider[$key]['service_id']][$data->rows[0]->elements[0]->duration->value] =$data->rows[0]->elements[0]->duration->text;
						$dis[$nearestProvider[$key]['service_id']][]=$data->rows[0]->elements[0]->duration->value;
					    ksort($estimations[$nearestProvider[$key]['service_id']]);
					    sort($dis[$nearestProvider[$key]['service_id']]);
					}
				}
			}
		}
	

		$user = Auth::guard(strtolower(Helper::getGuard()))->user();
		if(!empty($user)){
		$is_female = [0];
		    if($user->gender == 'FEMALE'){
			   $is_female = [0,1];
		    }
	    }
	   
		$city_id = $this->user ? $this->user->city_id : $request->city_id;
		$state = City::where('id',$city_id)->first();
		//print_r($this->user); exit;
		$state_id = '';//$state->state_id;
		if(isset($this->settings->transport->geofence) && $this->settings->transport->geofence == 1) {

			$geofence =(new UserServices())->poly_check_request((round($request->latitude,6)),(round($request->longitude,6)),$city_id);

			if($geofence) {
				if(!empty($user)){
				$service_list = RideDeliveryVehicle::with(['priceDetails' => function($q) use($geofence) {
					$q->where('geofence_id', $geofence);
				}])->whereHas('priceDetails', function($q) use($geofence) {
					$q->where('geofence_id', $geofence);
				})->whereIn('id', $ride_delivery_vehicles)->where('company_id', $this->company_id)->where('status', 1)->whereIn('is_female',$is_female)->get();
			    }
				else
				{
					$service_list = RideDeliveryVehicle::with(['priceDetails' => function($q) use($geofence) {
						$q->where('geofence_id', $geofence);
					}])->whereHas('priceDetails', function($q) use($geofence) {
						$q->where('geofence_id', $geofence);
					})->whereIn('id', $ride_delivery_vehicles)->where('company_id', $this->company_id)->where('status', 1)->get();	

				}
			} else {
				$service_list = [];
			}
			

		} else {

			// $service_list = RideDeliveryVehicle::with(['priceDetails' => function($q) use($city_id) {
			// 	$q->where('city_id', $city_id);
			// }])->whereHas('priceDetails', function($q) use($city_id) {
			// 	$q->where('city_id', $city_id);
			// })->whereIn('id', $ride_delivery_vehicles)->whereIn('is_female',$is_female)->where(['company_id' => $this->company_id, 'status' => 1])->get();
			// echo '<pre>';print_r($state_id);exit;

			if(!empty($user)){
			$service_list = RideDeliveryVehicle::with(['priceDetails' => function($q) use($state_id) {
				//$q->where('state_id', $state_id);
			}])->whereHas('priceDetails', function($q) use($state_id) {
				//$q->where('state_id', $state_id);
			})->where(['company_id' => $this->company_id, 'status' => 1])->whereIn('id', $ride_delivery_vehicles)->whereIn('is_female',$is_female)->get();
			 
	     	}
			else
			{
				$service_list = RideDeliveryVehicle::with(['priceDetails' => function($q) use($state_id) {
					//$q->where('state_id', $state_id);
				}])->whereHas('priceDetails', function($q) use($state_id) {
					//$q->where('state_id', $state_id);
				})->where(['company_id' => $this->company_id, 'status' => 1])->whereIn('id', $ride_delivery_vehicles)->get();
	
			}
			// $transport->mkdata = $service_list; 
		}		

		$service_types = [];
		$service_id_list = [];

		if(count($service_list) > 0) {			
			foreach ($service_list as $k => $services) {
				$service = new \stdClass();
				foreach (json_decode($services)as $j => $s) {
					if($j == 'price_details') {
						$service->estimated_time = isset($estimations[ $services->id ]) ?$estimations[ $services->id ][$dis[$services->id][0]] : '0 Min';
					}
					$service->$j = $s;
				}

					$service_types[] = $service;
					$service_id_list[] = $service->id; 
			}
		}
		
		if(!empty($user)){
			if(count($service_id_list)>0){
				$ride_delivery_vehicles = RideDeliveryVehicle::with(['priceDetails' => function($q) use($state_id) {
					//$q->where('state_id', $state_id);//$this->user ? $this->user->city_id : $request->city_id
				}])->whereHas('priceDetails', function($q) use($state_id) {
					//$q->where('city_id', $state_id);//$this->user ? $this->user->city_id : $request->city_id
				})->where('ride_type_id', $request->type)->where('company_id', $this->company_id)->whereIn('is_female',$is_female)->where('status', 1)->whereNotIn('id', $result)->select('*', \DB::raw('"..." AS "estimated_time"'))->orderBy('sort_order', 'asc')->get();
			    }
			 }
		else
		{
			if(count($service_id_list)>0){
				$ride_delivery_vehicles = RideDeliveryVehicle::with(['priceDetails' => function($q) use($state_id) {
					//$q->where('state_id', $state_id);//$this->user ? $this->user->city_id : $request->city_id
				}])->whereHas('priceDetails', function($q) use($state_id) {
					//$q->where('city_id', $state_id);//$this->user ? $this->user->city_id : $request->city_id
				})->where('ride_type_id', $request->type)->where('company_id', $this->company_id)->where('status', 1)->whereNotIn('id', $result)->select('*', \DB::raw('"..." AS "estimated_time"'))->orderBy('sort_order', 'asc')->get();
			}
	
		}

		//print_r($ride_delivery_vehicles); exit;

		if(count($ride_delivery_vehicles) > 0) {
			foreach ($ride_delivery_vehicles as $k => $ride_delivery_vehicle) {
				$service = new \stdClass();
				foreach (json_decode($ride_delivery_vehicle)as $j => $s) {
					if($j == 'price_details') {
						$service->estimated_time = isset($estimations[ $ride_delivery_vehicle->id ]) ?$estimations[ $ride_delivery_vehicle->id ][$dis[$ride_delivery_vehicle->id][0]] : '0 Min';
					}
					$service->$j = $s;
				}

				if(in_array($ride_delivery_vehicle->id, $result)){
					$service_types[] = $service;
				}
				
			}
		}

		usort($service_types, function($a, $b) {
			$min = $a->id;
			if($a->id > $b->id){
				$min = $a->id;
			}
			
			return $min;
		});

		$transport->services = array_reverse($service_types);

		if(isset($this->settings->transport->geofence) && $this->settings->transport->geofence == 1) {
			if(!empty($user)){
			$geofence =(new UserServices())->poly_check_request((round($request->latitude,6)),(round($request->longitude,6)),Auth::guard('user')->user()->city_id);
			}
			else{
			$geofence =(new UserServices())->poly_check_request((round($request->latitude,6)),(round($request->longitude,6)),$city_id);
			}
			if($geofence == false) {
				$transport->services = [];
			}
		}


		$transport->providers = $providers;

		if( Auth::guard(strtolower(Helper::getGuard()))->user() != null ) {            
		$transport->promocodes = Promocode::where('company_id', $this->company_id)->where('service', 'TRANSPORT')
					->where('expiration','>=',date("Y-m-d H:i"))
					->whereDoesntHave('promousage', function($query) {
						$query->where('user_id', Auth::guard('user')->user()->id);
					})
					->get();
				} else {
					$transport->promocodes = [];
				}
		Log::warning('transport/services log: '.json_encode($transport));
		return Helper::getResponse(['data' => $transport]);
	}

	/*public function cards(Request $request)
	{
		$cards = (new Resource\CardResource)->index();

		return Helper::getResponse(['data' => $cards]);
	}*/

	public function getDetailByVin(Request $request){
		$vin = $request->vin;
		if ($vin) {
			    $postdata = http_build_query([
			            'format' => 'json',
			            'data' => $vin
			        ]
			    );
			    $opts = [
			        'http' => [
			            'method' => 'POST',
			            'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
			                        "Content-Length: ".strlen($postdata)."\r\n",
			            'content' => $postdata
			        ]
			    ];

			    $apiURL = "https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVINValuesBatch/";
			    $context = stream_context_create($opts);
			    $fp = fopen($apiURL, 'rb', false, $context);

			    $line_of_text = fgets($fp);
			    $json = json_decode($line_of_text, true);

			    fclose($fp);

			    

			    return Helper::getResponse(['data' => $json['Results'][0]]);
			} else {
			    return Helper::getResponse(['status' => 200, 'message' => 'Please enter VIN number.']);
			}
	}
 	public function carTypeList(Request $request)
    {
        $datum = RideCarsType::where('is_deleted', 0);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function carList($id)
    {
    	$data=array();

        $carsList = RideCars::leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')
            ->select('ride_cars.*', 'ride_cars_images.front', 'ride_cars_images.back','ride_cars_images.left', 'ride_cars_images.right','ride_cars_images.front_left_tire', 'ride_cars_images.front_right_tire','ride_cars_images.back_left_tire', 'ride_cars_images.back_right_tire','ride_cars_images.left_front_seat_interior', 'ride_cars_images.right_front_seat_interior','ride_cars_images.left_back_seat_interior', 'ride_cars_images.right_back_seat_interior','ride_cars_images.left_front_door', 'ride_cars_images.right_front_door','ride_cars_images.left_back_door', 'ride_cars_images.right_back_door','ride_cars_images.back_interior', 'ride_cars_images.trunk')

            	->where(['ride_cars.type'=>$id, 'ride_cars.status'=>1, 'ride_cars.is_deleted'=>0]);
            
        $data['carlist'] = $carsList->get();

        $typeList = RideCarsType::where('is_deleted', 0);

        $data['typelist'] = $typeList->get();

        return Helper::getResponse(['data' => $data]);
    }

    public function carDetails($id)
    {
    	$data=array();

    	$data['admincharges'] = RideRentalCharges::find(1);

        $carsList = RideCars::select('ride_cars.*', 'ride_common.owners.first_name', 'ride_common.owners.last_name', 'ride_common.owners.picture')->join('ride_common.owners', 'ride_common.owners.id', '=', 'ride_cars.owner_id')->where('ride_cars.id', '=', $id);
            
        $data['carlist'] = $carsList->get();

        $carImages = RideCarsImages::where('car_id', $id)->select('front', 'back','left', 'right','front_left_tire', 'front_right_tire','back_left_tire', 'back_right_tire','left_front_seat_interior', 'right_front_seat_interior','left_back_seat_interior', 'right_back_seat_interior','left_front_door', 'right_front_door','left_back_door', 'right_back_door','back_interior', 'trunk');
        $data['carimages']= $carImages->get();

        return Helper::getResponse(['data' => $data]);
    }

    public function carListFiltered(Request $request, $id){
    	$data=array();

    	$priceorder=$request->order;
    	$pricestart=$request->pricestart;
    	$priceend=$request->priceend;
    	$location=$request->location;

        $carsList = RideCars::leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')
            ->select('ride_cars.*', 'ride_cars_images.front', 'ride_cars_images.back','ride_cars_images.left', 'ride_cars_images.right','ride_cars_images.front_left_tire', 'ride_cars_images.front_right_tire','ride_cars_images.back_left_tire', 'ride_cars_images.back_right_tire','ride_cars_images.left_front_seat_interior', 'ride_cars_images.right_front_seat_interior','ride_cars_images.left_back_seat_interior', 'ride_cars_images.right_back_seat_interior','ride_cars_images.left_front_door', 'ride_cars_images.right_front_door','ride_cars_images.left_back_door', 'ride_cars_images.right_back_door','ride_cars_images.back_interior', 'ride_cars_images.trunk');

            	$carsList->where(['ride_cars.type'=>$id, 'ride_cars.is_deleted'=>0, 'ride_cars.status'=>1]);

            if($pricestart!=''){
            	$carsList->where('daily_charges', '>=', $pricestart);
            }
            if($priceend!='' && $priceend!=150){
            		$carsList->where('daily_charges', '<=', $priceend);
            }
            if($priceorder=='asc'){
            	$carsList->orderBy('daily_charges', 'asc');
            }else{
            	$carsList->orderBy('daily_charges', 'desc');
            }
            
        $data['carlist'] = $carsList->get();


        if($priceorder=='distance'){
        	$cars=array();
        	if($location==''){

	        	foreach($data['carlist'] as $car){
	        		$data = array('latFrom'=>$request->lat, 'longFrom'=>$request->lang, 'addressTo'=>$car->pickup_address);
		        	$distance = $this->getDistanceByLatLong($data);
		        	$car['distance'] = $distance;

		        	array_push($cars, $car);
		        }
	        }
	        else{
	        	
		        foreach($data['carlist'] as $car){
		        	$distance = $this->getDistance($location, $car->pickup_address);
		        	$car['distance'] = $distance;

		        	array_push($cars, $car);
		        }
	        }

	        $distances=array();
	        foreach ($cars as $key => $val)
			{
			    $distances[$key] = $val['distance'];  
			}

			array_multisort($distances, SORT_ASC, $cars);

			$data['carlist'] = $cars;

		}

        
        return Helper::getResponse(['data' => $data]);
    }

    public function recommendedCars(Request $request){
    	$carsList = RideCars::leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')
            ->select('ride_cars.*', 'ride_cars_images.right')->where('is_deleted', 0)->get();

    	$cars=array();
    	foreach($carsList as $car){

    		if ($car->pickup_address!='') {
	    		
	    		$data = array('latFrom'=>$request->lat, 'longFrom'=>$request->long, 'addressTo'=>$car->pickup_address);

	        	$distance = $this->getDistanceByLatLong($data);

	        	$car['distance'] = $distance;

	        	array_push($cars, $car);
        	}else{
        		$car['distance'] = '';
        		array_push($cars, $car);
        	}

        }

        $distances=array();
        foreach ($cars as $key => $val)
		{
		    $distances[$key] = $val['distance'];  
		}

		array_multisort($distances, SORT_ASC, $cars);

		array_splice($cars,2);
		return Helper::getResponse(['data' => $cars]);
    }

    function getDistanceByLatLong($data, $unit=''){
		// Google API key
	    $apiKey = 'AIzaSyDBxeLMSZ5cFv4ibx1YIXWoEG6_yLXOEpQ';
	    
	    // Change address format
	    $formattedAddrTo     = str_replace(' ', '+', $data['addressTo']);
	    
	   
	    // Get latitude and longitude from the geodata
	    $latitudeFrom    = $data['latFrom'];
	    $longitudeFrom    = $data['longFrom'];
	    

	    $tourl = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey;
	    // Geocoding API request with end address
	    $geocodeTo = file_get_contents($tourl);
	    $outputTo = json_decode($geocodeTo);
	    if(!empty($outputTo->error_message)){
	        return $outputTo->error_message;
	    }
	    
	    $latitudeTo        = $outputTo->results[0]->geometry->location->lat;
	    $longitudeTo    = $outputTo->results[0]->geometry->location->lng;
	    
	    // Calculate distance between latitude and longitude
	    $theta    = $longitudeFrom - $longitudeTo;
	    $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
	    $dist    = acos($dist);
	    $dist    = rad2deg($dist);
	    $miles    = $dist * 60 * 1.1515;
	    
	    // Convert unit and return distance
	    $unit = strtoupper($unit);
	    if($unit == "K"){
	        return round($miles * 1.609344, 2).' km';
	    }elseif($unit == "M"){
	        return round($miles * 1609.344, 2).' meters';
	    }else{
	        return round($miles, 2).' mi';
	    }
    }

    function getDistance($addressFrom, $addressTo, $unit = ''){
    // Google API key
	    $apiKey = 'AIzaSyDBxeLMSZ5cFv4ibx1YIXWoEG6_yLXOEpQ';
	    
	    // Change address format
	    $formattedAddrFrom    = str_replace(' ', '+', $addressFrom);
	    $formattedAddrTo     = str_replace(' ', '+', $addressTo);
	    
	    // return $formattedAddrFrom;
	    $fromurl = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey;

	    // Geocoding API request with start address
	    $geocodeFrom = file_get_contents($fromurl);
	    $outputFrom = json_decode($geocodeFrom);
	    if(!empty($outputFrom->error_message)){
	        return $outputFrom->error_message;
	    }

	    // Get latitude and longitude from the geodata
	    $latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
	    $longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
	    

	    $tourl = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey;
	    // Geocoding API request with end address
	    $geocodeTo = file_get_contents($tourl);
	    $outputTo = json_decode($geocodeTo);
	    if(!empty($outputTo->error_message)){
	        return $outputTo->error_message;
	    }
	    
	    $latitudeTo        = $outputTo->results[0]->geometry->location->lat;
	    $longitudeTo    = $outputTo->results[0]->geometry->location->lng;
	    
	    // Calculate distance between latitude and longitude
	    $theta    = $longitudeFrom - $longitudeTo;
	    $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
	    $dist    = acos($dist);
	    $dist    = rad2deg($dist);
	    $miles    = $dist * 60 * 1.1515;
	    
	    // Convert unit and return distance
	    $unit = strtoupper($unit);
	    if($unit == "K"){
	        return round($miles * 1.609344, 2).' km';
	    }elseif($unit == "M"){
	        return round($miles * 1609.344, 2).' meters';
	    }else{
	        return round($miles, 2).' mi';
	    }
	}

	public function dashboard(){
		$user = Auth::guard('owner')->user();

		$admincharges = RideRentalCharges::find('1');
		
		$date = date('Y-m-d');

		$datebefore3 = date('Y-m-d', strtotime($date. ' - 3 days'));
		$datebefore7 = date('Y-m-d', strtotime($date. ' - 7 days'));
		$datebefore14 = date('Y-m-d', strtotime($date. ' - 14 days'));
		$datebefore30 = date('Y-m-d', strtotime($date. ' - 30 days'));

		$pending = RideRental::where(['owner_id'=> $user->id, 'status'=>0]);
		$accept = RideRental::where(['owner_id'=> $user->id, 'status'=>1]);
		$checkedin = RideRental::where(['owner_id'=> $user->id, 'status'=>2]);
		$active = RideRental::where(['owner_id'=> $user->id, 'status'=>3]);
		$cancel = RideRental::where(['owner_id'=> $user->id, 'status'=>4]);
		$late = RideRental::where(['owner_id'=> $user->id, 'status'=>5]);
		$complete = RideRental::where(['owner_id'=> $user->id, 'status'=>6]);

		$complete = RideRental::where(['owner_id'=> $user->id, 'status'=>6]);

		// $sevenDays = RideRental::select('sum(total_amount)')where('owner_id', '=', $user->id)
		// 						->where('is_paid', '=', 1)
		// 						->where('paid_on_date', '<', $date)
		// 						->where('paid_on_date' '>=', $date);

		$thrice_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore3)->sum('rent_amount');

		$thrice_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore3)->count();


		$weekly_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore7)->sum('rent_amount');

		$weekly_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore7)->count();


		$halfmonth_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore14)->sum('rent_amount');

		$halfmonth_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore14)->count();


		$monthly_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore30)->sum('rent_amount');

		$monthly_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore30)->count();


		$thrice = $thrice_all + ($thrice_cancel*$admincharges->cancellation_fee);
		$weekly = $weekly_all + ($weekly_cancel*$admincharges->cancellation_fee);
		$halfmonth = $halfmonth_all + ($halfmonth_cancel*$admincharges->cancellation_fee);
		$monthly = $monthly_all + ($monthly_cancel*$admincharges->cancellation_fee);


		$data['thrice'] = $thrice;
		$data['weekly'] = $weekly;
		$data['halfmonth'] = $halfmonth;
		$data['monthly'] = $monthly;

		$data['pending'] = $pending->count();
		$data['accept'] = $accept->count();
		$data['active'] = $active->count();
		$data['checkedin'] = $checkedin->count();
		$data['cancel'] = $cancel->count();
		$data['late'] = $late->count();
		$data['complete'] = $complete->count();

		return Helper::getResponse(['data' => $data]);
	}
	public function ownercars(Request $request)
    {
    	$data=array();
    	$search=  $request->keyword;
    	$user = Auth::guard('owner')->user();

        $carsList = RideCars::select('ride_cars.*', 'ride_cars_images.right')->where('ride_cars.is_deleted' ,'=', '0')
        					->where('ride_cars.owner_id' ,'=', $user->id);
        					if($search!=''){
							    $carsList->where(function($query) use ($search){
							        $query->orWhere('ride_cars.model', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.vin', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.year', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.make', 'LIKE', '%'.$search.'%');
							    });
							}
						$carsList->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')  ;	

            
        $data['carlist'] = $carsList->get();

        return Helper::getResponse(['data' => $data]);
    }

    public function paymentlist(Request $request)
    {
    	$user = Auth::guard('owner')->user();

    	$admincharges = RideRentalCharges::find('1');

        $paymentList = RideRental::where('is_paid' ,'=', '1')->where('status' ,'!=', 7)->where('owner_id' ,'=', $user->id)->orderBy('id', 'DESC')->get();

        $date = date('Y-m-d');

		$datebefore3 = date('Y-m-d', strtotime($date. ' - 3 days'));
		$datebefore7 = date('Y-m-d', strtotime($date. ' - 7 days'));
		$datebefore14 = date('Y-m-d', strtotime($date. ' - 14 days'));
		$datebefore30 = date('Y-m-d', strtotime($date. ' - 30 days'));
        
        $thrice_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore7)
								->where('paid_on_date', '<', $datebefore3)->sum('rent_amount');

		$thrice_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $datebefore7)
								->where('paid_on_date', '<', $datebefore3)->count();

		


		$last_all = RideRental::select('total_amount')->where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->orderBy('id', 'DESC')->limit(1)->get();

		$last_cancel = $admincharges->cancellation_fee;


		$month = date('m', strtotime($date));
		$year = date('Y', strtotime($date));

		$monthly_all = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '!=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $year.'-'.$month.'-'.'01')
								->where('paid_on_date', '<', $date)->sum('rent_amount');

		$monthly_cancel = RideRental::where('owner_id', '=', $user->id)
								->where('is_paid', '=', 1)
								->where('status', '=', 4)
								->where('status' ,'!=', 7)
								->where('paid_on_date', '>', $year.'-'.$month.'-'.'01')
								->where('paid_on_date', '<', $date)->count();		

		$thrice = $thrice_all + ($thrice_cancel*$admincharges->cancellation_fee);
		if(is_array($last_all)){
			$last = $last_all[0]->rent_amount + $last_cancel;
		}else{
			$last = $last_cancel;
		}

		$monthly = $monthly_all + ($monthly_cancel*$admincharges->cancellation_fee);



		

		$data['thrice'] = $thrice;
		$data['last'] = $last;
		$data['monthly'] = $monthly;
		$data['payments'] = $paymentList;
		$data['admincharges'] = $admincharges;

        return Helper::getResponse(['data' => $data]);
    }

    public function ownerbookings(Request $request)
    {
    	$user = Auth::guard('owner')->user();
		$search= $request->keyword;
		$booking = RideRental::select('ride_cars.year','ride_cars.make','ride_cars.model','ride_cars.vin','ride_cars.protection','ride_cars.daily_charges','ride_cars_images.right', 'ride_rentals.id', 'ride_rentals.booking_start_date', 'ride_rentals.total_amount','ride_rentals.booking_end_date', 'ride_rentals.status')->leftjoin('ride_cars', 'ride_cars.id', '=', 'ride_rentals.car_id' )->leftjoin('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id')->where('ride_rentals.owner_id', '=', $user->id);

							if($search!=''){
							    $booking->where(function($query) use ($search){
							        $query->orWhere('ride_cars.model', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.vin', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.year', 'LIKE', '%'.$search.'%');
							        $query->orWhere('ride_cars.make', 'LIKE', '%'.$search.'%');
							    });
							}
		$booking->orderBy('ride_rentals.id', 'desc');

		$data['carlist'] = $booking->get();

		return Helper::getResponse(['data' => $data]);
    }

    public function bookingstatus(Request $request){

    	$user = Auth::guard('owner')->user();

    	$id = $request->id;

    	$status = $request->status;

    	$booking = RideRental::findOrFail($id);

    	$admincharges = json_decode($this->admincharges());
    	$refundable_amount = $booking->total_amount;// - ;

    	if($status==4){
    		
        $stripe = new \Stripe\StripeClient();
			$refund = $stripe->refunds->create(['payment_intent' => $booking->txn_id,
						  'amount' => ($refundable_amount-$admincharges['cancellation_fee'])*100
						]);

			$booking->refund_id = $refund;
    	}


    	if($status==1){
    		$userinfo = User::find($booking->user_id);
    		$total = $booking->total_amount;
    		$data=array('user_id'=>$userinfo->id, 'company_id'=>$userinfo->company_id, 'payment_mode'=>'CARD', 'payment_method'=>$booking->stripe_card_id);
    		$payment = Order::bookingpayment($total, $data); 
	    	$paymentid = $payment['payment_id'];

	    	$date=date('Y-m-d');
	    	$time=date('H:i:s');

	    	$booking->txn_id = $paymentid;
			$booking->is_paid = 1;
	    	$booking->paid_on_date = $date;
	    	$booking->paid_on_time = $time;
    	}

    	$booking->status = $status;
    	$booking->save();

    	return Helper::getResponse(['status' => 200, 'message' => 'status changed successfully.']);

    }

    public function carstatus(Request $request){
    	$id = $request->id;
    	$date = date('Y-m-d');
    	$isbooked = RideRental:: where('car_id', $id)->where('booking_start_date', '<=', $date)->where('booking_end_date', '>=', $date)->get();

    	if($isbooked->count()>0){
			return Helper::getResponse(['status' => 200, 'message' => 'Active']);
		}else{
			return Helper::getResponse(['status' => 200, 'message' => 'Inactive']);
		}
    }

    public function adminCharges(){
    	$admincharges = RideRentalCharges::find('1');

    	return Helper::getResponse(['data' => $admincharges]);
    }

	public function addcar(Request $request){
		try{

			$user = Auth::guard('owner')->user();

		 	$rideDeliveryVehicle = new RideCars;
            $rideDeliveryVehicle->company_id = $user->company_id;
            $rideDeliveryVehicle->owner_id = $user->id;
            $rideDeliveryVehicle->model = $request->model;
            $rideDeliveryVehicle->capacity = $request->capacity;
            $rideDeliveryVehicle->status = 0;
            $rideDeliveryVehicle->type = $request->type;
            $rideDeliveryVehicle->is_female = $request->is_female;
            $rideDeliveryVehicle->vin = $request->vin;
            $rideDeliveryVehicle->make = $request->make;
            $rideDeliveryVehicle->color = $request->color;
            $rideDeliveryVehicle->year = $request->year;
            $rideDeliveryVehicle->odometer = $request->odometer;
            $rideDeliveryVehicle->plate_number = $request->plate_number;
            $rideDeliveryVehicle->protection = $request->protection;
            $rideDeliveryVehicle->driver_name = $user->first_name.' '.$user->last_name;
            $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
            $rideDeliveryVehicle->pickup_address = $request->pickup_address;
            $rideDeliveryVehicle->about = $request->about;
            //$rideDeliveryVehicle->hourly_charges = $request->hourly_charges;
            $rideDeliveryVehicle->daily_charges = $request->daily_charges;
            $rideDeliveryVehicle->weekly_charges = $request->weekly_charges;
            //$rideDeliveryVehicle->trip_fee = $request->trip_fee;
            //$rideDeliveryVehicle->cancellation_charges = $request->cancellation_charges;
            //$rideDeliveryVehicle->insurance_charges = $request->insurance_charges;
            //$rideDeliveryVehicle->booking_fee = $request->booking_fee;
            //$rideDeliveryVehicle->sales_tax = $request->sales_tax; 

            $rideDeliveryVehicle->registration_exp = $request->registration_exp;
            $rideDeliveryVehicle->insurance_exp = $request->insurance_exp;
            $rideDeliveryVehicle->inspection_exp = $request->inspection_exp;  

            if ($request->hasFile('inspection')) {
                $rideDeliveryVehicle->inspection = Helper::upload_file($request->file('inspection'), 'vehicle/inspection');
            }
            if ($request->hasFile('insurance')) {
                $rideDeliveryVehicle->insurance = Helper::upload_file($request->file('insurance'), 'vehicle/insurance');
            }
            if ($request->hasFile('registration')) {
                $rideDeliveryVehicle->registration = Helper::upload_file($request->file('registration'), 'vehicle/registration');
            }
            if ($request->hasFile('driver_profile')) {
                $rideDeliveryVehicle->driver_profile = Helper::upload_file($request->file('driver_profile'), 'vehicle/driver');
            }

            $rideDeliveryVehicle->save();

            $rideCars = new RideCarsImages;
                $rideCars->car_id = $rideDeliveryVehicle->id;
                
                if ($request->hasFile('front')) {
                    
                    $rideCars->front = Helper::upload_file($request->file('front'), 'cars/image');
                }
                if ($request->hasFile('back')) {
                    
                    $rideCars->back = Helper::upload_file($request->file('back'), 'cars/image');
                }
                if ($request->hasFile('left')) {
                   
                    $rideCars->left = Helper::upload_file($request->file('left'), 'cars/image');
                }
                if ($request->hasFile('right')) {
                   
                    $rideCars->right = Helper::upload_file($request->file('right'), 'cars/image');
                }
                if ($request->hasFile('front_left_tire')) {
                    
                    $rideCars->front_left_tire = Helper::upload_file($request->file('front_left_tire'), 'cars/image');
                }
                if ($request->hasFile('front_right_tire')) {
                  
                    $rideCars->front_right_tire = Helper::upload_file($request->file('front_right_tire'), 'cars/image');
                }
                if ($request->hasFile('back_left_tire')) {
                    
                    $rideCars->back_left_tire = Helper::upload_file($request->file('back_left_tire'), 'cars/image');
                }
                if ($request->hasFile('back_right_tire')) {
                   
                    $rideCars->back_right_tire = Helper::upload_file($request->file('back_right_tire'), 'cars/image');
                }
                if ($request->hasFile('left_front_seat_interior')) {
                    
                    $rideCars->left_front_seat_interior = Helper::upload_file($request->file('left_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('right_front_seat_interior')) {
                    
                    $rideCars->right_front_seat_interior = Helper::upload_file($request->file('right_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_back_seat_interior')) {
                    
                    $rideCars->left_back_seat_interior = Helper::upload_file($request->file('left_back_seat_interior'), 'cars/image');
                }
                
                if ($request->hasFile('right_back_seat_interior')) {
                   
                    $rideCars->right_back_seat_interior = Helper::upload_file($request->file('right_back_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_front_door')) {
                
                    $rideCars->left_front_door = Helper::upload_file($request->file('left_front_door'), 'cars/image');
                }
                if ($request->hasFile('right_front_door')) {
                    
                    $rideCars->right_front_door = Helper::upload_file($request->file('right_front_door'), 'cars/image');
                }
                if ($request->hasFile('left_back_door')) {
                    
                    $rideCars->left_back_door = Helper::upload_file($request->file('left_back_door'), 'cars/image');
                }
                if ($request->hasFile('right_back_door')) {
                    
                    $rideCars->right_back_door = Helper::upload_file($request->file('right_back_door'), 'cars/image');
                }
                if ($request->hasFile('back_interior')) {
                    
                    $rideCars->back_interior = Helper::upload_file($request->file('back_interior'), 'cars/image');
                }
                if ($request->hasFile('trunk')) {
                    
                    $rideCars->trunk = Helper::upload_file($request->file('trunk'), 'cars/image');
                }
                if ($request->hasFile('dashboard')) {
                    
                    $rideCars->dashboard = Helper::upload_file($request->file('dashboard'), 'cars/image');
                }

            $rideCars->save();

            return Helper::getResponse(['status' => 200, 'message' => 'Car listed successfully']);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}

	public function updateCar(Request $request){

		try {
			$id = $request->id;
            $rideDeliveryVehicle = RideCars::findOrFail($id);
            if ($rideDeliveryVehicle) {

            	$rideCarsImages = RideCarsImages::where('car_id', $id)->get();
            if (count($rideCarsImages)>0) {
                $rideCars = RideCarsImages::findOrFail($rideCarsImages[0]->id);
                if ($request->hasFile('front')) {
                    if ($rideCars->front) {
                        Helper::delete_picture($rideCars->front);
                    }
                    $rideCars->front = Helper::upload_file($request->file('front'), 'cars/image');
                }
                if ($request->hasFile('back')) {
                    if ($rideCars->back) {
                        Helper::delete_picture($rideCars->back);
                    }
                    $rideCars->back = Helper::upload_file($request->file('back'), 'cars/image');
                }
                if ($request->hasFile('left')) {
                    if ($rideCars->left) {
                        Helper::delete_picture($rideCars->left);
                    }
                    $rideCars->left = Helper::upload_file($request->file('left'), 'cars/image');
                }
                if ($request->hasFile('right')) {
                    if ($rideCars->right) {
                        Helper::delete_picture($rideCars->right);
                    }
                    $rideCars->right = Helper::upload_file($request->file('right'), 'cars/image');
                }
                if ($request->hasFile('front_left_tire')) {
                    if ($rideCars->front_left_tire) {
                        Helper::delete_picture($rideCars->front_left_tire);
                    }
                    $rideCars->front_left_tire = Helper::upload_file($request->file('front_left_tire'), 'cars/image');
                }
                if ($request->hasFile('front_right_tire')) {
                    if ($rideCars->front_right_tire) {
                        Helper::delete_picture($rideCars->front_right_tire);
                    }
                    $rideCars->front_right_tire = Helper::upload_file($request->file('front_right_tire'), 'cars/image');
                }
                if ($request->hasFile('back_left_tire')) {
                    if ($rideCars->back_left_tire) {
                        Helper::delete_picture($rideCars->back_left_tire);
                    }
                    $rideCars->back_left_tire = Helper::upload_file($request->file('back_left_tire'), 'cars/image');
                }
                if ($request->hasFile('back_right_tire')) {
                    if ($rideCars->back_right_tire) {
                        Helper::delete_picture($rideCars->back_right_tire);
                    }
                    $rideCars->back_right_tire = Helper::upload_file($request->file('back_right_tire'), 'cars/image');
                }
                if ($request->hasFile('left_front_seat_interior')) {
                    if ($rideCars->left_front_seat_interior) {
                        Helper::delete_picture($rideCars->left_front_seat_interior);
                    }
                    $rideCars->left_front_seat_interior = Helper::upload_file($request->file('left_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('right_front_seat_interior')) {
                    if ($rideCars->right_front_seat_interior) {
                        Helper::delete_picture($rideCars->right_front_seat_interior);
                    }
                    $rideCars->right_front_seat_interior = Helper::upload_file($request->file('right_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_back_seat_interior')) {
                    if ($rideCars->left_back_seat_interior) {
                        Helper::delete_picture($rideCars->left_back_seat_interior);
                    }
                    $rideCars->left_back_seat_interior = Helper::upload_file($request->file('left_back_seat_interior'), 'cars/image');
                }
                
                if ($request->hasFile('right_back_seat_interior')) {
                    if ($rideCars->right_back_seat_interior) {
                        Helper::delete_picture($rideCars->right_back_seat_interior);
                    }
                    $rideCars->right_back_seat_interior = Helper::upload_file($request->file('right_back_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_front_door')) {
                    if ($rideCars->image) {
                        Helper::delete_picture($rideCars->left_front_door);
                    }
                    $rideCars->left_front_door = Helper::upload_file($request->file('left_front_door'), 'cars/image');
                }
                if ($request->hasFile('right_front_door')) {
                    if ($rideCars->right_front_door) {
                        Helper::delete_picture($rideCars->right_front_door);
                    }
                    $rideCars->right_front_door = Helper::upload_file($request->file('right_front_door'), 'cars/image');
                }
                if ($request->hasFile('left_back_door')) {
                    if ($rideCars->left_back_door) {
                        Helper::delete_picture($rideCars->left_back_door);
                    }
                    $rideCars->left_back_door = Helper::upload_file($request->file('left_back_door'), 'cars/image');
                }
                if ($request->hasFile('right_back_door')) {
                    if ($rideCars->right_back_door) {
                        Helper::delete_picture($rideCars->right_back_door);
                    }
                    $rideCars->right_back_door = Helper::upload_file($request->file('right_back_door'), 'cars/image');
                }
                if ($request->hasFile('back_interior')) {
                    if ($rideCars->back_interior) {
                        Helper::delete_picture($rideCars->back_interior);
                    }
                    $rideCars->back_interior = Helper::upload_file($request->file('back_interior'), 'cars/image');
                }
                if ($request->hasFile('trunk')) {
                    if ($rideCars->trunk) {
                        Helper::delete_picture($rideCars->trunk);
                    }
                    $rideCars->trunk = Helper::upload_file($request->file('trunk'), 'cars/image');
                }

                if ($request->hasFile('dashboard')) {
                    if ($rideCars->dashboard) {
                        Helper::delete_picture($rideCars->dashboard);
                    }
                    $rideCars->dashboard = Helper::upload_file($request->file('dashboard'), 'cars/image');
                }

            } else {

                $rideCars = new RideCarsImages;
                $rideCars->car_id = $id;
                
                if ($request->hasFile('front')) {
                    
                    $rideCars->front = Helper::upload_file($request->file('front'), 'cars/image');
                }
                if ($request->hasFile('back')) {
                    
                    $rideCars->back = Helper::upload_file($request->file('back'), 'cars/image');
                }
                if ($request->hasFile('left')) {
                   
                    $rideCars->left = Helper::upload_file($request->file('left'), 'cars/image');
                }
                if ($request->hasFile('right')) {
                   
                    $rideCars->right = Helper::upload_file($request->file('right'), 'cars/image');
                }
                if ($request->hasFile('front_left_tire')) {
                    
                    $rideCars->front_left_tire = Helper::upload_file($request->file('front_left_tire'), 'cars/image');
                }
                if ($request->hasFile('front_right_tire')) {
                  
                    $rideCars->front_right_tire = Helper::upload_file($request->file('front_right_tire'), 'cars/image');
                }
                if ($request->hasFile('back_left_tire')) {
                    
                    $rideCars->back_left_tire = Helper::upload_file($request->file('back_left_tire'), 'cars/image');
                }
                if ($request->hasFile('back_right_tire')) {
                   
                    $rideCars->back_right_tire = Helper::upload_file($request->file('back_right_tire'), 'cars/image');
                }
                if ($request->hasFile('left_front_seat_interior')) {
                    
                    $rideCars->left_front_seat_interior = Helper::upload_file($request->file('left_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('right_front_seat_interior')) {
                    
                    $rideCars->right_front_seat_interior = Helper::upload_file($request->file('right_front_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_back_seat_interior')) {
                    
                    $rideCars->left_back_seat_interior = Helper::upload_file($request->file('left_back_seat_interior'), 'cars/image');
                }
                
                if ($request->hasFile('right_back_seat_interior')) {
                   
                    $rideCars->right_back_seat_interior = Helper::upload_file($request->file('right_back_seat_interior'), 'cars/image');
                }
                if ($request->hasFile('left_front_door')) {
                
                    $rideCars->left_front_door = Helper::upload_file($request->file('left_front_door'), 'cars/image');
                }
                if ($request->hasFile('right_front_door')) {
                    
                    $rideCars->right_front_door = Helper::upload_file($request->file('right_front_door'), 'cars/image');
                }
                if ($request->hasFile('left_back_door')) {
                    
                    $rideCars->left_back_door = Helper::upload_file($request->file('left_back_door'), 'cars/image');
                }
                if ($request->hasFile('right_back_door')) {
                    
                    $rideCars->right_back_door = Helper::upload_file($request->file('right_back_door'), 'cars/image');
                }
                if ($request->hasFile('back_interior')) {
                    
                    $rideCars->back_interior = Helper::upload_file($request->file('back_interior'), 'cars/image');
                }
                if ($request->hasFile('trunk')) {
                    
                    $rideCars->trunk = Helper::upload_file($request->file('trunk'), 'cars/image');
                }
                if ($request->hasFile('dashboard')) {
                    
                    $rideCars->dashboard = Helper::upload_file($request->file('dashboard'), 'cars/image');
                }
            }

            $rideCars->save();
                
                if ($request->hasFile('inspection')) {
                    if ($rideDeliveryVehicle->inspection) {
                        Helper::delete_picture($rideDeliveryVehicle->inspection);
                    }
                    $rideDeliveryVehicle->inspection = Helper::upload_file($request->file('inspection'), 'cars/inspection');
                }
                if ($request->hasFile('insurance')) {
                    if ($rideDeliveryVehicle->insurance) {
                        Helper::delete_picture($rideDeliveryVehicle->insurance);
                    }
                    $rideDeliveryVehicle->insurance = Helper::upload_file($request->file('insurance'), 'cars/insurance');
                }
                if ($request->hasFile('registration')) {
                    if ($rideDeliveryVehicle->insurance) {
                        Helper::delete_picture($rideDeliveryVehicle->registration);
                    }
                    $rideDeliveryVehicle->registration = Helper::upload_file($request->file('registration'), 'cars/registration');
                }
                if ($request->hasFile('driver_profile')) {
                    if ($rideDeliveryVehicle->driver_profile) {
                        Helper::delete_picture($rideDeliveryVehicle->driver_profile);
                    }
                    $rideDeliveryVehicle->driver_profile = Helper::upload_file($request->file('driver_profile'), 'cars/driver');
                }
                $rideDeliveryVehicle->model = $request->model;
                $rideDeliveryVehicle->capacity = $request->capacity;
                $rideDeliveryVehicle->status = $request->status;
                $rideDeliveryVehicle->type = $request->type;
                $rideDeliveryVehicle->is_female = $request->is_female;
                $rideDeliveryVehicle->vin = $request->vin;
                $rideDeliveryVehicle->make = $request->make;
                $rideDeliveryVehicle->color = $request->color;
                $rideDeliveryVehicle->year = $request->year;
                $rideDeliveryVehicle->odometer = $request->odometer;
                $rideDeliveryVehicle->plate_number = $request->plate_number;
                $rideDeliveryVehicle->licence_number = $request->licence_number;
                $rideDeliveryVehicle->licence_state = $request->licence_state;
                $rideDeliveryVehicle->protection = 1;
                $rideDeliveryVehicle->driver_name = $request->driver_name;
                $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
                $rideDeliveryVehicle->pickup_address = $request->pickup_address;
                $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
                $rideDeliveryVehicle->about = $request->about;
                //$rideDeliveryVehicle->hourly_charges = $request->hourly_charges;
                $rideDeliveryVehicle->daily_charges = $request->daily_charges;
                $rideDeliveryVehicle->weekly_charges = $request->weekly_charges;
                //$rideDeliveryVehicle->trip_fee = $request->trip_fee;
                //$rideDeliveryVehicle->cancellation_charges = $request->cancellation_charges;
                //$rideDeliveryVehicle->insurance_charges = $request->insurance_charges;
                //$rideDeliveryVehicle->booking_fee = $request->booking_fee;
                //$rideDeliveryVehicle->sales_tax = $request->sales_tax;  
                $rideDeliveryVehicle->inspection_exp = $request->inspection_exp;
                $rideDeliveryVehicle->insurance_exp = $request->insurance_exp;
                $rideDeliveryVehicle->registration_exp = $request->registration_exp;

                $rideDeliveryVehicle->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]);
            }
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
	}
	public function documentStatus(Request $request){
		try{
			$user = Auth::guard('owner')->user();
			$today = date('Y-m-d');
			$expdate = date('Y-m-d', strtotime('+15 days', strtotime($today)));

			$carsList = RideCars::select('ride_cars.id', 'ride_cars.vin', 'ride_cars.model', 'ride_cars.inspection_exp', 'ride_cars.registration_exp', 'ride_cars.insurance_exp', 'ride_cars_images.right')->where('is_deleted' ,'=', '0')
        					->where('owner_id' ,'=', $user->id);
					    
		    $carsList->where(function($query) use ($expdate){
		        $query->orWhere('inspection_exp', '<=', $expdate);
		        $query->orWhere('registration_exp', '<=', $expdate);
		        $query->orWhere('insurance_exp', '<=', $expdate);
		    });

		    $carsList->join('ride_cars_images', 'ride_cars_images.car_id', '=', 'ride_cars.id');


			$data = $carsList->get();

            return Helper::getResponse(['data' => $data, 'message'=> 'success']);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}

	public function pickups(){
		try{
			$user = Auth::guard('owner')->user();
			$today = date('Y-m-d');
			$tomorrow = date('Y-m-d', strtotime('+1 days', strtotime($today)));

			$data['today'] = RideRental::select('ride_cars.model', 'ride_cars.plate_number', 'ride_rentals.*')->join('ride_cars','ride_cars.id','=','ride_rentals.car_id')->Where('ride_rentals.booking_start_date', $today)->Where('ride_rentals.owner_id', '=', $user->id)->Where('ride_rentals.status', '=', 1)->get();
			
			$data['tomorrow'] = RideRental::select('ride_cars.model', 'ride_cars.plate_number', 'ride_rentals.*')->join('ride_cars','ride_cars.id','=','ride_rentals.car_id')->Where('ride_rentals.booking_start_date', $tomorrow)->Where('ride_rentals.owner_id', '=', $user->id)->Where('ride_rentals.status', '=', 1)->get();

            return Helper::getResponse(['data' => $data, 'message'=> 'success']);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
	}
	


	public function estimateadmin(Request $request)
	{		
		$this->validate($request,[
			's_latitude' => 'required|numeric',
			's_longitude' => 'numeric',
			'd_latitude' => 'required|numeric',
			'd_longitude' => 'numeric',
			'service_type' => 'required|numeric|exists:transport.ride_delivery_vehicles,id',
			'id' => 'required|numeric',

		]);

		$request->request->add(['city_id' => $request->city_id ? $request->city_id : '' ]);		
		if($this->settings->transport->geofence == 1) {
			
			$geofence =(new UserServices())->poly_check_request((round($request->s_latitude,6)),(round($request->s_longitude,6)),$request->city_id);						
			if($geofence) {
				$getfence = GeoFence::where(['id' => $geofence])->first();
				$request->request->add(['server_key' => $this->settings->site->server_key]);
				
				$request->request->add(['geofence_id' => $geofence]);
				$request->request->add(['is_airport' => $getfence->type]);
				
				$fare = (new UserServices())->estimated_fare($request)->getData();				
				$service = RideDeliveryVehicle::find($request->service_type);

				if($request->has('current_longitude') && $request->has('current_latitude'))
				{
					User::where('id', $request->id)->update([
						'latitude' => $request->current_latitude,
						'longitude' => $request->current_longitude
					]);
				}

				if( Auth::guard(strtolower(Helper::getGuard()))->user() != null ) {
					$promocodes = Promocode::where('company_id', $this->company_id)->where('service', 'TRANSPORT')
							->where('expiration','>=',date("Y-m-d H:i"))
							->whereDoesntHave('promousage', function($query) use ($request){
										$query->where('user_id',$request->id);
									})
							->get();

					$currency = Auth::guard('user')->user()->currency_symbol;
				} else {
					$promocodes = [];
					$currency = '';
				}

				return Helper::getResponse(['data' => ['fare' => $fare, 'service' => $service, 'promocodes' => $promocodes, 'unit' => $this->settings->transport->unit_measurement, 'currency' => $currency ]]);
			} else {
	            return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];
	        }

		} else {
			$request->request->add(['server_key' => $this->settings->site->server_key]);	
			$request->request->add(['is_airport' => '']);		
			
			$fare = (new UserServices())->estimated_fare_admin($request)->getData();	
			// if(!empty($fare->responseCode)){		
			// 	if($fare->responseCode == 400){
			// 		return Helper::getResponse(['status' => 400, 'message' => $fare->responseMessage, 'error' => $fare->responseMessage]);
			// 	}			
			// }
			
			$service = RideDeliveryVehicle::find($request->service_type);

			if($request->has('current_longitude') && $request->has('current_latitude'))
			{
				User::where('id', $request->id)->update([
					'latitude' => $request->current_latitude,
					'longitude' => $request->current_longitude
				]);
			}

			if( Auth::guard(strtolower(Helper::getGuard()))->user() != null ) {
				$promocodes = Promocode::where('company_id', $this->company_id)->where('service', 'TRANSPORT')
						->where('expiration','>=',date("Y-m-d H:i"))
						->whereDoesntHave('promousage', function($query) use ($request){
									$query->where('user_id',$request->id);
								})
						->get();
                 $user = User::find($request->id);
				$currency = $user->currency_symbol;
			} else {
				$promocodes = [];
				$currency = '';
			}

			return Helper::getResponse(['data' => ['fare' => $fare, 'service' => $service, 'promocodes' => $promocodes, 'unit' => $this->settings->transport->unit_measurement, 'currency' => $currency ]]);

		}

		return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];

		
	}

	
	public function estimate(Request $request)
	{		
		$this->validate($request,[
			's_latitude' => 'required|numeric',
			's_longitude' => 'numeric',
			'd_latitude' => 'required|numeric',
			'd_longitude' => 'numeric',
			'service_type' => 'required|numeric|exists:transport.ride_delivery_vehicles,id',

		]);

		$request->request->add(['city_id' => $this->user ? $this->user->city_id : $request->city_id ]);		
		if($this->settings->transport->geofence == 1) {
			
			$geofence =(new UserServices())->poly_check_request((round($request->s_latitude,6)),(round($request->s_longitude,6)),$request->city_id);						
			if($geofence) {
				$getfence = GeoFence::where(['id' => $geofence])->first();
				$request->request->add(['server_key' => 'AIzaSyDo5-R0tZH0cWEvjvaOXin8fOSKHb_t35Q']);
				
				$request->request->add(['geofence_id' => $geofence]);
				$request->request->add(['is_airport' => $getfence->type]);
				
				$fare = (new UserServices())->estimated_fare($request)->getData();				
				$service = RideDeliveryVehicle::find($request->service_type);

				if($request->has('current_longitude') && $request->has('current_latitude'))
				{
					User::where('id', Auth::guard('user')->user()->id)->update([
						'latitude' => $request->current_latitude,
						'longitude' => $request->current_longitude
					]);
				}

				if( Auth::guard(strtolower(Helper::getGuard()))->user() != null ) {
					$promocodes = Promocode::where('company_id', $this->company_id)->where('service', 'TRANSPORT')
							->where('expiration','>=',date("Y-m-d H:i"))
							->whereDoesntHave('promousage', function($query) {
										$query->where('user_id',Auth::guard('user')->user()->id);
									})
							->get();

					$currency = Auth::guard('user')->user()->currency_symbol;
				} else {
					$promocodes = [];
					$currency = '';
				}

				return Helper::getResponse(['data' => ['fare' => $fare, 'service' => $service, 'promocodes' => $promocodes, 'unit' => $this->settings->transport->unit_measurement, 'currency' => $currency ]]);
			} else {
	            return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];
	        }

		} else {
			
			$request->request->add(['server_key' => 'AIzaSyDo5-R0tZH0cWEvjvaOXin8fOSKHb_t35Q']);	
			$request->request->add(['is_airport' => '']);		
			
			$fare = (new UserServices())->estimated_fare($request)->getData();	
			// if(!empty($fare->responseCode)){		
			// 	if($fare->responseCode == 400){
			// 		return Helper::getResponse(['status' => 400, 'message' => $fare->responseMessage, 'error' => $fare->responseMessage]);
			// 	}			
			// }
			
			$service = RideDeliveryVehicle::find($request->service_type);

			if($request->has('current_longitude') && $request->has('current_latitude'))
			{
				User::where('id', Auth::guard('user')->user()->id)->update([
					'latitude' => $request->current_latitude,
					'longitude' => $request->current_longitude
				]);
			}

			if( Auth::guard(strtolower(Helper::getGuard()))->user() != null ) {
				$promocodes = Promocode::where('company_id', $this->company_id)->where('service', 'TRANSPORT')
						->where('expiration','>=',date("Y-m-d H:i"))
						->whereDoesntHave('promousage', function($query) {
									$query->where('user_id',Auth::guard('user')->user()->id);
								})
						->get();

				$currency = Auth::guard('user')->user()->currency_symbol;
			} else {
				$promocodes = [];
				$currency = '';
			}

			return Helper::getResponse(['data' => ['fare' => $fare, 'service' => $service, 'promocodes' => $promocodes, 'unit' => $this->settings->transport->unit_measurement, 'currency' => $currency ]]);

		}

		return ['status' => 400, 'message' => trans('user.ride.service_not_available_location'), 'error' => trans('user.ride.service_not_available_location')];

		
	}

	public function create_ride(Request $request)
	{
		if(isset($this->settings->transport->destination)) {
			if($this->settings->transport->destination == 0) {
				$this->validate($request, [
					's_latitude' => 'required|numeric',
					's_longitude' => 'required|numeric',
					'ride_type_id' => 'required'					
				]);
			} else {
				$this->validate($request, [
					's_latitude' => 'required|numeric',
					's_longitude' => 'required|numeric',
					'ride_type_id' => 'required',
				    'd_latitude' => 'required|numeric',
					'd_longitude' => 'required|numeric',
					'estimated_fare' => 'required|numeric'
				]);
			}
		}

		try {
			$ride = (new Ride())->createRide($request);
			return Helper::getResponse(['status' => isset($ride['status']) ? $ride['status'] : 200, 'message' => isset($ride['message']) ? $ride['message'] : '', 'data' => isset($ride['data']) ? $ride['data'] : [] ]);
		} catch (Exception $e) {  
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	public function status(Request $request)
	{

		try{

			$check_status = ['CANCELLED', 'SCHEDULED'];
			$admin_service = 'TRANSPORT';

			$rideRequest = RideRequest::RideRequestStatusCheck(Auth::guard('user')->user()->id, $check_status, 'TRANSPORT',0)
										->get()
										->toArray();

			$start_time = (Carbon::now())->toDateTimeString();
			$end_time = (Carbon::now())->toDateTimeString();

			$peak_percentage = 1+(0/100)."X";
			$peak = 0;

			$start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->where('company_id', '>=', Auth::guard('user')->user()->company_id)->first();

			if( count($rideRequest) > 0 && $start_time_check){

				$Peakcharges = RidePeakPrice::where('ride_city_price_id', $rideRequest[0]['city_id'])->where('ride_delivery_id', $rideRequest[0]['ride_delivery_id'])->where('peak_hour_id',$start_time_check->id)->first();

				if($Peakcharges){
					$peak = 1;
				}

			}
									   

			$search_status = ['SEARCHING','SCHEDULED'];
			$rideRequestFilter = RideRequest::RideRequestAssignProvider(Auth::guard('user')->user()->id,$search_status)->get(); 

			if(!empty($rideRequest)){
				$rideRequest[0]['ride_otp'] = (int) $this->settings->transport->ride_otp ? $this->settings->transport->ride_otp : 0 ;
				$rideRequest[0]['peak'] = $peak ;

				$rideRequest[0]['reasons']=Reason::where('type','USER')->where('service','TRANSPORT')->where('status','Active')->get();
			}

			$Timeout = $this->settings->transport->provider_select_timeout ? $this->settings->transport->provider_select_timeout : 60 ;
			$response_time = $Timeout;

			if(!empty($rideRequestFilter)){
				for ($i=0; $i < sizeof($rideRequestFilter); $i++) {
					$ExpiredTime = $Timeout - (time() - strtotime($rideRequestFilter[$i]->assigned_at));
					if($rideRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
						(new ProviderServices())->assignNextProvider($rideRequestFilter[$i]->id, $admin_service );
						$response_time = $Timeout - (time() - strtotime($rideRequestFilter[$i]->assigned_at));
					}else if($rideRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
						break;
					}
				}

			}

			if(empty($rideRequest)) {

				$cancelled_request = RideRequest::where('ride_requests.user_id', Auth::guard('user')->user()->id)
					->where('ride_requests.user_rated',0)
					->where('ride_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
					->where('updated_at','>=',\Carbon\Carbon::now()->subSeconds(5))
					->first();
				
			}

			return Helper::getResponse(['data' => [
				'response_time' => $response_time, 
				'data' => $rideRequest, 
				'sos' => isset($this->settings->site->sos_number) ? $this->settings->site->sos_number : '911' , 
				'emergency' => isset($this->settings->site->contact_number) ? $this->settings->site->contact_number : [['number' => '911']]  ]]);

		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
		}
	}

	public function checkRide(Request $request, $id)
	{

		try{

			
			$admin_service = 'TRANSPORT';
			$ride_type_id=RideRequest::select('ride_delivery_id')->where('id',$id)->first();
			$check_status = ['CANCELLED', 'SCHEDULED'];

			$rideRequest = RideRequest::RideRequestStatusCheck(Auth::guard('user')->user()->id, $check_status, 'TRANSPORT',$ride_type_id->ride_delivery_id)
										->where('id', $id)
										->get()
										->toArray();

			$start_time = (Carbon::now())->toDateTimeString();
			$end_time = (Carbon::now())->toDateTimeString();

			$peak_percentage = 1+(0/100)."X";
			$peak = 0;

			$start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->where('company_id', '>=', Auth::guard('user')->user()->company_id)->first();

			if( count($rideRequest) > 0 && $start_time_check){

				$Peakcharges = RidePeakPrice::where('ride_city_price_id', $rideRequest[0]['city_id'])->where('ride_delivery_id', $rideRequest[0]['ride_delivery_id'])->where('peak_hour_id',$start_time_check->id)->first();

				if($Peakcharges){
					$peak = 1;
				}

			}
									   

			$search_status = ['SEARCHING','SCHEDULED'];
			$rideRequestFilter = RideRequest::RideRequestAssignProvider(Auth::guard('user')->user()->id,$search_status)->get(); 

			if(!empty($rideRequest)){
				$rideRequest[0]['ride_otp'] = (int) $this->settings->transport->ride_otp ? $this->settings->transport->ride_otp : 0 ;
				$rideRequest[0]['peak'] = $peak ;

				$rideRequest[0]['reasons']=Reason::where('type','USER')->where('service','TRANSPORT')->where('status','Active')->get();

				$Timeout = $this->settings->transport->provider_select_timeout ? $this->settings->transport->provider_select_timeout : 60 ;
				$response_time = $Timeout;

				if(!empty($rideRequestFilter)){
					for ($i=0; $i < sizeof($rideRequestFilter); $i++) {
						$ExpiredTime = $Timeout - (time() - strtotime($rideRequestFilter[$i]->assigned_at));
						if($rideRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
							(new ProviderServices())->assignNextProvider($rideRequestFilter[$i]->id, $admin_service );
							$response_time = $Timeout - (time() - strtotime($rideRequestFilter[$i]->assigned_at));
						}else if($rideRequestFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
							break;
						}
					}

				}

				if(empty($rideRequest)) {

					$cancelled_request = RideRequest::where('ride_requests.user_id', Auth::guard('user')->user()->id)
						->where('ride_requests.user_rated',0)
						->where('ride_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
						->where('updated_at','>=',\Carbon\Carbon::now()->subSeconds(5))
						->first();
					
				}

				return Helper::getResponse(['data' => [
					'response_time' => $response_time, 
					'data' => $rideRequest, 
					'sos' => isset($this->settings->site->sos_number) ? $this->settings->site->sos_number : '911' , 
					'emergency' => isset($this->settings->site->contact_number) ? $this->settings->site->contact_number : [['number' => '911']]  ]]);
		} else {
			return Helper::getResponse(['data' => [
				'data' => [] ]]);
		}

		} catch (Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
		}
	}


	public function cancel_ride(Request $request)
	{
		
		$this->validate($request, [
			'id' => 'required|numeric|exists:transport.ride_requests,id,user_id,'.Auth::guard('user')->user()->id,
		]);

		$request->request->add(['cancelled_by' => 'USER']);
		
		try {
			\Log::info('Initiate api calling');
			$ride = (new Ride())->cancelRide($request);
			return Helper::getResponse(['status' => $ride['status'], 'message' => $ride['message'] ]);
		} catch (Exception $e) {  
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}


	public function extend_trip(Request $request) 
	{
		$this->validate($request, [
			'id' => 'required|numeric|exists:transport.ride_requests,id,user_id,'.Auth::guard('user')->user()->id,
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'address' => 'required',
		]);

		try{

			$ride = (new Ride())->extendTrip($request);

			return Helper::getResponse(['message' => 'Destination location has been changed', 'data' => $ride]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
		}
	}

	public function update_payment_method(Request $request)
	{
		$this->validate($request, [
			'id' => 'required|numeric|exists:transport.ride_requests,id,user_id,'.Auth::guard('user')->user()->id,
			'payment_mode' => 'required',
		]);

		try{

			$rideRequest = RideRequest::findOrFail($request->id);
			if($request->payment_mode != "CASH") {
				$rideRequest->status = 'COMPLETED';
				$rideRequest->save();
			}

			$payment = RideRequestPayment::where('ride_request_id', $rideRequest->id)->first();

			if($payment != null) {
				$payment->payment_mode = $request->payment_mode;
				$payment->save();
			}

			$ride = (new UserServices())->updatePaymentMode($request, $rideRequest, $payment);

			return Helper::getResponse(['message' => trans('api.ride.payment_updated')]);
		}

		catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}

	

	public function search_user(Request $request)
	{

		$results=array();

		$term =  $request->input('stext');  

		$queries = User::where('first_name', 'LIKE', $term.'%')->where('company_id', Auth::user()->company_id)->take(5)->get();

		foreach ($queries as $query)
		{
			$results[]=$query;
		}    

		return response()->json(array('success' => true, 'data'=>$results));

	}
	
	public function search_provider(Request $request){

		$results=array();

		$term =  $request->input('stext');  

		$queries = Provider::where('first_name', 'LIKE', $term.'%')->take(5)->get();

		foreach ($queries as $query)
		{
			$results[]=$query;
		}    

		return response()->json(array('success' => true, 'data'=>$results));

	}
	
	public function searchRideLostitem(Request $request)
	{

		$results=array();

		$term =  $request->input('stext');

		if($request->input('sflag')==1){
			
			$queries = RideRequest::where('provider_id', $request->id)->orderby('id', 'desc')->take(10)->get();
		}
		else{

			$queries = RideRequest::where('user_id', $request->id)->orderby('id', 'desc')->take(10)->get();
		}

		foreach ($queries as $query)
		{
			$LostItem = RideLostItem::where('ride_request_id',$query->id)->first();
			if(!$LostItem)
			$results[]=$query;
		}

		return response()->json(array('success' => true, 'data'=>$results));

	}
	
	public function searchRideDispute(Request $request)
	{

		$results=array();

		$term =  $request->input('stext');

		if($request->input('sflag')==1){
			
			$queries = RideRequest::where('provider_id', $request->id)->orderby('id', 'desc')->take(10)->get();
		}
		else{

			$queries = RideRequest::where('user_id', $request->id)->orderby('id', 'desc')->take(10)->get();
		}

		foreach ($queries as $query)
		{
			$RideRequestDispute = RideRequestDispute::where('ride_request_id',$query->id)->first();
			if(!$RideRequestDispute)
			$results[]=$query;
		}

		return response()->json(array('success' => true, 'data'=>$results));

	}
	
	public function requestHistory(Request $request)
	{
		try {
			$history_status = array('CANCELLED','COMPLETED');
			$datum = RideRequest::where('company_id',  Auth::user()->company_id)
					 ->with('user', 'provider','payment');

			if(Auth::user()->hasRole('FLEET')) {
				$datum->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$datum->Search($request->search_text);
			}
	
			if($request->has('order_by')) {
				$datum->orderby($request->order_by, $request->order_direction);
			}
			$data = $datum->whereIn('status',$history_status)->paginate(10);
			return Helper::getResponse(['data' => $data]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}
	public function requestscheduleHistory(Request $request)
	{
		try {
			$scheduled_status = array('SCHEDULED');
			$datum = RideRequest::where('company_id',  Auth::user()->company_id)
					 ->whereIn('status',$scheduled_status)
					 ->with('user', 'provider');

			if(Auth::user()->hasRole('FLEET')) {
				$datum->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$datum->Search($request->search_text);
			}
	
			if($request->has('order_by')) {
				$datum->orderby($request->order_by, $request->order_direction);
			}
	
			$data = $datum->paginate(10);
	
			return Helper::getResponse(['data' => $data]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function requestStatementHistory(Request $request)
	{
		try {
			$history_status = array('CANCELLED','COMPLETED');
			$rides = RideRequest::where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0])
					 ->with('user', 'provider');
			if($request->has('country_id')) {
				$rides->where('country_id',$request->country_id);
			}
			if(Auth::user()->hasRole('FLEET')) {
				$rides->where('admin_id', Auth::user()->id);  
			}
			if($request->has('search_text') && $request->search_text != null) {
				$rides->Search($request->search_text);
			}

			if($request->has('status') && $request->status != null) {
				$history_status = array($request->status);
			}

			if($request->has('user_id') && $request->user_id != null) {
				$rides->where('user_id',$request->user_id);
			}

			if($request->has('provider_id') && $request->provider_id != null) {
				$rides->where('provider_id',$request->provider_id);
			}

			if($request->has('ride_type') && $request->ride_type != null) {
				$rides->where('ride_type_id',$request->ride_type);
			}
	
			if($request->has('order_by')) {
				$rides->orderby($request->order_by, $request->order_direction);
			}
			$type = isset($_GET['type'])?$_GET['type']:'';
			if($type == 'today'){
				$rides->where('created_at', '>=', Carbon::today());
			}elseif($type == 'monthly'){
				$rides->where('created_at', '>=', Carbon::now()->month);
			}elseif($type == 'yearly'){
				$rides->where('created_at', '>=', Carbon::now()->year);
			}elseif ($type == 'range') {   
				if($request->has('from') &&$request->has('to')) {             
					if($request->from == $request->to) {
						$rides->whereDate('created_at', date('Y-m-d', strtotime($request->from)));
					} else {
						$rides->whereBetween('created_at',[Carbon::createFromFormat('Y-m-d', $request->from),Carbon::createFromFormat('Y-m-d', $request->to)]);
					}
				}
			}else{
				// dd(5);
			}
			$cancelrides = $rides;
			$orderCounts = $rides->count();
			if($request->has('page') && $request->page == 'all') {
	            $dataval = $rides->whereIn('status',$history_status)->get();
	        } else {
	            $dataval = $rides->whereIn('status',$history_status)->paginate(10);
	        }
			
			$cancelledQuery = $cancelrides->where('status','CANCELLED')->count();
			$total_earnings = 0;
			foreach($dataval as $ride){
				//$ride->status = $ride->status == 1?'Enabled' : 'Disable';
				$rideid  = $ride->id;
				$earnings = RideRequestPayment::select('total')->where('ride_request_id',$rideid)->where('company_id',  Auth::user()->company_id)->first();
				if($earnings != null){
					$ride->earnings = $earnings->total;
					$total_earnings = $total_earnings + $earnings->total;
				}else{
					$ride->earnings = 0;
				}
			}
			$data['rides'] = $dataval;
			$data['total_rides'] = $orderCounts;
			$data['revenue_value'] = $total_earnings;
			$data['cancelled_rides'] = $cancelledQuery;
			return Helper::getResponse(['data' => $data]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function destroy(Request $request,$id){
		try {
			$datum = RideRequest::findOrFail($id); 			
			$datum->is_deleted = 1;
			$datum->deleted_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
			$datum->deleted_type = 'ADMIN';
			$datum->deleted_by = Auth::user()->id;
			$datum->save();
			// $datum['body'] = "deleted";
			// $this->sendUserData($datum);

			// return $this->removeModel($id);
			return Helper::getResponse(['message' => trans('admin.delete')]);
		} catch (\Exception $e){
			return Helper::getResponse(['status' => 400,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function requestHistoryDetails($id)
	{
		try {
			$data = RideRequest::with('user', 'provider','rating','payment')->findOrFail($id);

			return Helper::getResponse(['data' => $data]);

		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}
	

	public function statement_provider(Request $request)
	{

		try{

		$datum = Provider::where('company_id', Auth::user()->company_id);

		if($request->has('search_text') && $request->search_text != null) {
			$datum->Search($request->search_text);
		}

		if($request->has('order_by')) {
			$datum->orderby($request->order_by, $request->order_direction);
		}

		if($request->has('page') && $request->page == 'all') {
            $Providers = $datum->get();
        } else {
            $Providers = $datum->paginate(10);
        }

		 

		foreach($Providers as $index => $Provider){

			$Rides = RideRequest::where('provider_id',$Provider->id)
						->where('status','<>','CANCELLED')
						->get()->pluck('id');

			$Providers[$index]->rides_count = $Rides->count();

			$Providers[$index]->payment = RideRequestPayment::whereIn('ride_request_id', $Rides)
							->select(\DB::raw(
							   'SUM(ROUND(provider_pay)) as overall'
							))->get();
		}

			return Helper::getResponse(['data' => $Providers]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

   public function statement_user(Request $request)
   {

	try{

		$datum = User::where('company_id', Auth::user()->company_id);

		if($request->has('search_text') && $request->search_text != null) {
			$datum->Search($request->search_text);
		}

		if($request->has('order_by')) {
			$datum->orderby($request->order_by, $request->order_direction);
		}

		if($request->has('page') && $request->page == 'all') {
            $Users = $datum->get();
        } else {
            $Users = $datum->paginate(10);
        }

			foreach($Users as $index => $User){

				$Rides = RideRequest::where('user_id',$User->id)
							->where('status','<>','CANCELLED')
							->get()->pluck('id');

				$Users[$index]->rides_count = $Rides->count();

				$Users[$index]->payment = RideRequestPayment::whereIn('ride_request_id', $Rides)
								->select(\DB::raw(
								'SUM(ROUND(total)) as overall' 
								))->get();
			}			

			return Helper::getResponse(['data' => $Users]);
		} catch (\Throwable $e) {
			return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
		}
	}

	public function rate(Request $request) {

		$this->validate($request, [
			  'id' => 'required|numeric|exists:transport.ride_requests,id,user_id,'.Auth::guard('user')->user()->id,
			  'rating' => 'required|integer|in:1,2,3,4,5',
			  'comment' => 'max:255',
			  'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
		],['comment.max'=>'character limit should not exceed 255']);

		try {

			$rideRequest = RideRequest::where('id', $request->id)->where('status', 'COMPLETED')->firstOrFail();
			
			$data = (new UserServices())->rate($request, $rideRequest );

			return Helper::getResponse(['status' => isset($data['status']) ? $data['status'] : 200, 'message' => isset($data['message']) ? $data['message'] : '', 'error' => isset($data['error']) ? $data['error'] : '' ]);

		} catch (\Exception $e) {
			return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' =>trans('api.ride.request_not_completed') ]);
		}
	}

	public function  providerAvailability(Request $request) {

		$callback = function ($q) use($request) {
			$q->where('admin_service', 'TRANSPORT');
		};
		$withCallback = ['service' => $callback, 'service.ride_vehicle','provider_vehicle'];
		$whereHasCallback = ['service' => $callback];
		$data = (new UserServices())->availableProvidersforbackend($request, $withCallback, $whereHasCallback);
		return Helper::getResponse(['data' => $data]);

	}
	public function payment(Request $request) {

		$this->validate($request, [
			'id' => 'required|numeric|exists:transport.ride_requests,id',
		]);
		
		try {

			$tip_amount = 0;

			$UserRequest = RideRequest::find($request->id);
			$payment = RideRequestPayment::where('ride_request_id', $request->id)->first();

			$ride = (new UserServices())->payment($request, $UserRequest, $payment);

			return Helper::getResponse(['message' => $ride]);

		} catch (\Throwable $e) {
			 return Helper::getResponse(['status' => 500, 'message' => trans('api.ride.request_not_completed'), 'error' => $e->getMessage() ]);
		}
	}

	public function invoicePDF(Request $request, $id){
		$jsonResponse = [];
		$jsonResponse['type'] ='transport';
		$userrequest = RideRequest::with(['provider','payment','service_type','ride','provider_vehicle','dispute'=> function($query){  
			$query->where('dispute_type','user'); 
		        },'lostItem']);
		$request->request->add(['admin_service'=>'TRANSPORT','id'=>$id]);
		$data=(new UserServices())->userTripsDetails($request,$userrequest);
        $jsonResponse['transport'] = $data;
		$data['data'] = $jsonResponse;
		// echo '<pre>'; 
		// print_r($data['data']); exit;
		$html = view('invoice', $data)->render();

		$dompdf = app(Dompdf::class);
		//$dompdf->set_paper(array(0,0,750,900));
		$dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        $filePath = 'ride/invoice/'.$id.'.pdf';

        Storage::put($filePath, $pdfContent);

        return Helper::getResponse(['data' => $filePath]);
		
	}
}