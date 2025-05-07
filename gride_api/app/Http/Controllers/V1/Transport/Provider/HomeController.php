<?php

namespace App\Http\Controllers\V1\Transport\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transport\RideType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use Carbon\Carbon;
use Auth;
use DB;

class HomeController extends Controller
{

    public function ridetype(Request $request)
	{
	try{
		// $ridetype=RideType::with('providerservice','servicelist')->where('company_id',Auth::guard('provider')->user()->company_id)->where(['status' => 1, 'is_deleted' => 0])->get();
		// if(!empty($ridetype)) {
		// 	foreach($ridetype as $ride) {
		// 		if(!empty($ride['providerservice'])) {
		// 			if($ride['providerservice']['admin_service'] == 'TRANSPORT') {
		// 				$provider_vehicles = \App\Models\Common\ProviderVehicle::select('vehicle_service_id','vehicle_model','number_of_seats','vin_number')
		// 									->where('provider_id',Auth::guard('provider')->user()->id)
		// 									->whereNotIn('vehicle_model',['Gride Female','Gride Care'])
		// 									->where('deleted_by',NULL)->get();
		// 				if((!empty($ride['providerservice']['providervehicle']))) {
		// 					$ride['providerservice']['providervehicle']['provider_vehicles'] = $provider_vehicles;
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		$ridetypes = RideType::where('company_id',Auth::guard('provider')->user()->company_id)->where(['status' => 1, 'is_deleted' => 0])->with('servicelist')->get();
		

			if(!empty($ridetypes)) {

				foreach($ridetypes as $ride) {	

						if($ride['service_type']==1) {
							$providerservices = \App\Models\Common\ProviderService::where('provider_id',Auth::guard('provider')->user()->id)->where('status', 1)->where('admin_service', 'TRANSPORT')->orderby('id', 'DESC')->take(1)->get();

							if(!empty($providerservices)){
								$ride['providerservice'] = $providerservices[0];
							}

							if(!empty($ride['providerservice'])) {	
								$provider_vehicles = \App\Models\Common\ProviderVehicle:: where('provider_id', Auth::guard('provider')->user()->id)
													->whereNotIn('vehicle_model',['Gride Female','Gride Care'])
													->where('service_type',1)
													->where('deleted_by',NULL)->get();	

								if(!empty($provider_vehicles)){
									$ride['providerservice']['providervehicle'] = $provider_vehicles;
								}
							}
						}


						if($ride['service_type']==2) {
							$providerservices = \App\Models\Common\ProviderService::where('provider_id',Auth::guard('provider')->user()->id)->where('status', 1)->where('admin_service', 'ORDER')->orderby('id', 'DESC')->take(1)->get();

							if(!empty($providerservices)){
								$ride['providerservice'] = $providerservices[0];
							}

							if(!empty($ride['providerservice'])) {	
								$provider_vehicles = \App\Models\Common\ProviderVehicle:: where('provider_id', Auth::guard('provider')->user()->id)
													->whereNotIn('vehicle_model',['Gride Female','Gride Care'])
													->where('service_type',2)
													->where('deleted_by',NULL)->get();	

								if(!empty($provider_vehicles)){
									$ride['providerservice']['providervehicle'] = $provider_vehicles;
								}
							}
						}
				}
			}
		return Helper::getResponse(['data' => $ridetypes ]);
    }catch (ModelNotFoundException $e) {
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}

	}

	public function ridetypebyService(Request $request, $type){
		try{
			
			$provider_vehicles='';
			//$ridetype=RideType::with('providerservice','servicelist')->where('company_id',Auth::guard('provider')->user()->company_id)->where(['status' => 1, 'service_type'=> $type, 'is_deleted' => 0])->get();

			$ridetypes = RideType::where('company_id',Auth::guard('provider')->user()->company_id)->where(['status' => 1, 'service_type'=> $type, 'is_deleted' => 0])->with('servicelist')->get();
		
			//$ridetypeswithServicelist = RideType::where('company_id',Auth::guard('provider')->user()->company_id)->where(['status' => 1, 'is_deleted' => 0])->with('servicelist')->get();

			//print_r($ridetypeswithServicelist[0]->servicelist); exit;

			if(!empty($ridetypes)) {

				if($type==1){
					$admin_service = 'TRANSPORT';
				}
				else if($type==2){
					$admin_service = 'ORDER';
				}

				$providerservices = \App\Models\Common\ProviderService::where('admin_service',$admin_service)->where('provider_id',Auth::guard('provider')->user()->id)->where('status', 1)->orderby('id', 'DESC')->take(1)->get();

				if(count($providerservices)>0){
					$ridetypes[0]['providerservice'] = $providerservices[0];

					//$ridetypes[0]['servicelist']  = $ridetypeswithServicelist[0]->servicelist;
				}

				foreach($ridetypes as $ride) {	
					
					if(!empty($ride['providerservice'])) {

						if($ride['providerservice']['admin_service'] == 'TRANSPORT' || $ride['providerservice']['admin_service'] == 'ORDER') {

							$provider_vehicles = \App\Models\Common\ProviderVehicle:: where('provider_id', Auth::guard('provider')->user()->id)
												->whereNotIn('vehicle_model',['Gride Female','Gride Care'])
												->where('service_type', $type)
												->where('deleted_by',NULL)->orderBy('id', 'DESC')->get();	

							if(!empty($provider_vehicles)){
								$ride['providerservice']['providervehicle'] = $provider_vehicles;
							}
						}
					}
				}
				//$ridetype = $ridetypes[0];
				//print_r($ridetype['providerservice']);
			}
			return Helper::getResponse(['data' => $ridetypes ]);
	    }catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
			}
	}
	

	public function ProviderVehicles(Request $request, $type){
		try{
			
			$provider_vehicles = \App\Models\Common\ProviderVehicle:: where('provider_id', Auth::guard('provider')->user()->id)
								->where('service_type', $type)
								->whereNull('deleted_by')->orderBy('id', 'DESC')->get();	

							
			return Helper::getResponse(['data' => $provider_vehicles ]);
	    }catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
			}
	}

	public function ProviderVehiclesById(Request $request, $type, $id){
		try{
			
			$vehicle = \App\Models\Common\ProviderVehicle:: where('provider_id', Auth::guard('provider')->user()->id)
								->where('service_type', $type)
								->where('id', $id)
								->where('deleted_by', NULL)->get();	
							
			return Helper::getResponse(['data' => $vehicle ]);
	    }catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}


	public function DefaultVehicle(Request $request, $id){
		try{
			
			$provider_vehicle = \App\Models\Common\ProviderVehicle::where('provider_id', Auth::guard('provider')->id())
								->where('service_type', $request->type)
								->where('deleted_by', NULL)->get();	

			if($provider_vehicle->count()>0){
				foreach($provider_vehicle as $vehicle){
					$providerVehicle = \App\Models\Common\ProviderVehicle::find($vehicle->id);

					if($id==$vehicle->id){
						$providerVehicle->is_default = 1;
					}else
					{
						$providerVehicle->is_default = 0;
					}

					$providerVehicle->save();
				}
				
				return Helper::getResponse(['status' => 200, 'message' => 'Default vehicle is updated']);
				
			}

			return Helper::getResponse(['status' => 402, 'message' => 'Vehicle not found']);
			
	    }catch (ModelNotFoundException $e) {
				return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}
	}	
    
}
