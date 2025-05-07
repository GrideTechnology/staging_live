<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Transport\RideDeliveryVehicle;
use App\Models\Transport\RideCity;
use App\Models\Transport\RideCityPrice;
use App\Models\Transport\RidePeakPrice;
use App\Models\Common\AdminService;
use App\Models\Common\MenuCity;
use App\Models\Common\Menu;
use App\Models\Common\CompanyCity;
use App\Models\Common\GeoFence;

use App\Models\Common\CompanyCountry;
use App\Models\Common\PeakHour;
use App\Models\Transport\RideType;
use App\Models\Common\Setting;

use Illuminate\Support\Facades\Storage;
use Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class VehicleController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(RideDeliveryVehicle $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = RideDeliveryVehicle::with('ride_type')->where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        if ($request->has('page') && $request->page == 'all') {
            $data = $datum->get();
        } else {
            $data = $datum->paginate(10);
        }

        return Helper::encryptResponse(['data' => $data]);
    }

    public function vehicleList(Request $request)
    {
        $datum = RideDeliveryVehicle::with('ride_type')->where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'vehicle_name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'ride_type_id' => 'required',
            'vehicle_image' => 'sometimes|nullable|mimes:ico,png',
            'vehicle_marker' => 'sometimes|nullable|mimes:ico,png',
        ]);

        try {

            $RideType = RideType::findOrFail($request->ride_type_id);

            $rideDeliveryVehicle = new RideDeliveryVehicle;
            $rideDeliveryVehicle->company_id = Auth::user()->company_id;
            $rideDeliveryVehicle->vehicle_name = $request->vehicle_name;
            $rideDeliveryVehicle->capacity = $request->capacity;
            $rideDeliveryVehicle->vehicle_type = 'RIDE';
            $rideDeliveryVehicle->status = $request->status;
            $rideDeliveryVehicle->ride_type_id = $request->ride_type_id;
            $rideDeliveryVehicle->is_female = $RideType->is_female;

            if ($request->hasFile('vehicle_image')) {
                $rideDeliveryVehicle->vehicle_image = Helper::upload_file($request->file('vehicle_image'), 'vehicle/image');
            }
            if ($request->hasFile('vehicle_marker')) {
                $rideDeliveryVehicle->vehicle_marker = Helper::upload_file($request->file('vehicle_marker'), 'vehicle/marker');
            }
            $rideDeliveryVehicle->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


    public function show($id)
    {
        try {
            $rideDeliveryVehicle = RideDeliveryVehicle::findOrFail($id);
            return Helper::getResponse(['data' => $rideDeliveryVehicle]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'vehicle_name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'ride_type_id' => 'required',
            'vehicle_image' => 'mimes:ico,png',
            'vehicle_marker' => 'mimes:ico,png',
        ]);

        try {
            $rideDeliveryVehicle = RideDeliveryVehicle::findOrFail($id);
            if ($rideDeliveryVehicle) {
                if ($request->hasFile('vehicle_image')) {
                    if ($rideDeliveryVehicle->vehicle_image) {
                        Helper::delete_picture($rideDeliveryVehicle->vehicle_image);
                    }
                    $rideDeliveryVehicle->vehicle_image = Helper::upload_file($request->file('vehicle_image'), 'vehicle/image');
                }
                if ($request->hasFile('vehicle_marker')) {
                    if ($rideDeliveryVehicle->vehicle_marker) {
                        Helper::delete_picture($rideDeliveryVehicle->vehicle_marker);
                    }
                    $rideDeliveryVehicle->vehicle_marker = Helper::upload_file($request->file('vehicle_marker'), 'vehicle/marker');
                }
                $rideDeliveryVehicle->vehicle_name = $request->vehicle_name;
                $rideDeliveryVehicle->capacity = $request->capacity;
                $rideDeliveryVehicle->vehicle_type = 'RIDE';
                $rideDeliveryVehicle->status = $request->status;
                $rideDeliveryVehicle->ride_type_id = $request->ride_type_id;
                $rideDeliveryVehicle->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]);
            }
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function vehicletype()
    {
        $vehicle_type = RideDeliveryVehicle::where('company_id', Auth::user()->company_id)->where('status', 1)->get();
        return Helper::getResponse(['data' => $vehicle_type]);
    }

    public function destroy($id)
    {
        try {
            $model = RideDeliveryVehicle::findorFail($id);

            $model->is_deleted = 1;
            $model->deleted_at = \Carbon\Carbon::now();
            $model->deleted_type = Auth::user()->type;
            $model->deleted_by = Auth::user()->id;
            $model->save();

            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } catch (\Exception $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
        // return $this->removeModel($id);
    }

    public function multidestroy(Request $request)
    {
        $this->request = $request;
        return $this->removeMultiple();
    }

    public function statusChange(Request $request)
    {
        $this->request = $request;
        return $this->changeStatus();
    }

    public function statusChangeMultiple(Request $request)
    {
        $this->request = $request;
        return $this->changeStatusAll();
    }
    public function comission(Request $request)
    {

        $this->validate($request, [
            'country_id' => 'required',
            'city_id' => 'required',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE',
            'comission' => 'required',
            'fleet_comission' => 'required',
            'tax' => 'required',
            'night_charges' => 'required',

        ], [
            'comission.required' => 'Please Enter Commission',
            'fleet_comission.required' => 'Please Enter Fleet Commission'
        ]);

        try {
            if ($request->ride_city_id != '') {
                $rideCity = RideCity::findOrFail($request->ride_city_id);
            } else {
                $rideCity = new RideCity;
            }

            $rideCity->company_id = Auth::user()->company_id;
            $rideCity->country_id = $request->country_id;
            $rideCity->city_id = $request->city_id;
            $rideCity->admin_service = $request->admin_service;
            $rideCity->comission = $request->comission;
            $rideCity->fleet_comission = $request->fleet_comission;
            $rideCity->tax = $request->tax;
            $rideCity->night_charges = $request->night_charges;
            $rideCity->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function getComission($country_id, $city_id, $admin_service)
    {

        $rideCity = RideCity::where([
            ['company_id', Auth::user()->company_id],
            ['country_id', $country_id],
            ['city_id', $city_id],
            ['admin_service', $admin_service]
        ])->first();
        if ($rideCity) {
            return Helper::getResponse(['data' => $rideCity]);
        }
        return Helper::getResponse(['data' => '']);
    }
    
    public function gettaxiprice($id)
    {
        // $admin_service = AdminService::where('admin_service', 'TRANSPORT')->where('company_id', Auth::user()->company_id)->value('id');
        // if ($admin_service) {
        $cityList = CompanyCountry::with('country','companyCountryCities.state')->where('company_id',Auth::user()->company_id)->where('status',1)->get();        
        $countries = [];
        $countries = $cityList->map(function ($response) {
            $country = new \stdClass;
            $country->id = $response->country->id;
            $country->country_name = $response->country->country_name;
            $country->country_code = $response->country->country_code;
            $country->country_phonecode = $response->country->country_phonecode;
            $country->country_currency = $response->country->country_currency;
            $country->country_symbol = $response->country->country_symbol;
            $country->status = $response->country->status;
            $country->timezone = $response->country->timezone;
            $state = collect();
            $city = collect();            
            foreach ($response->companyCountryCities as $value) {
                if(!$city->contains($value->city)){
                    $city->push($value->city);
                }

                if(!$state->contains($value->state)){
                    $state->push($value->state);
                    // if(!$city->contains($value->city)){
                    //     $city = $value->city;
                    // }                    
                }

                // if(!empty($value->city)){                    
                //     if($value->state->id = $value->city->state_id){
                //         $cities[] = $value->city;
                //         $value->state->city = $cities;     
                //     }                    
                // } else {
                //     $cities = [];
                // }                
            }
            // usort($state, function($a, $b) {return strcmp($a->state_name, $b->state_name);});
            $country->state = $state;
            $country->city = $city;
            return $country;
        });
        return Helper::getResponse(['data' => $countries]);
        //     $cityLists = CompanyCity::with(['country', 'state', 'city'])->where('status', 1)->get()->groupBy("country_id");
        //     $countryIds = collect();
        //     $stateids = collect();
        //     $i = 0;
        //     $country = array();
        //     foreach ($cityLists as $index => $value) {
        //         $states = $value->groupBy("state_id");
        //         $j = 0;
        //         foreach ($states as $state_index => $state) {
        //             $cities = $state;
        //             foreach ($cities as $city_index => $city) {
        //                 if (!$countryIds->contains($index)) {
        //                     $country[$i] = $city->country->toArray();
        //                     $countryIds->push($index);
        //                 }
        //                 if (!$stateids->contains($state_index)) {
        //                     $stateids->push($state_index);
        //                     $country[$i]["state"][] = $city->state->toArray();
        //                 }
        //                 $country[$i]["state"][$j]['city'][] = $city->city->toArray();                        
        //             }
        //             $j++;
        //         }
        //         $i++;
        //     }
            // print_r($country);
            // die();
            
            // $countryid = [];
            // $stateid = [];
            // $cityid = [];
            // if (!empty($cityList)) {
            //     foreach ($cityList as $city) {
            //         // $key = array_search($city->country->id, array_column($countries, 'id'));                                
            //         if (in_array($city->country->id, $countryid, TRUE)) {
            //             if (in_array($city->state->id, $stateid, TRUE)) {
            //             } else {
            //                 $countries[]['state'] = array(array(
            //                     'id' => $city->state->id,
            //                     'country_id' => $city->state->country_id,
            //                     'state_name' => $city->state->state_name,
            //                     'timezone' => $city->state->timezone,
            //                     'utc_offset' => $city->state->utc_offset,
            //                     'status' => $city->state->status,
            //                     'city' => array(array(
            //                         'id' => $city->city->id,
            //                         'country_id' => $city->city->country_id,
            //                         'state_id' => $city->city->state_id,
            //                         'city_name' => $city->city->city_name,
            //                         'status' => $city->city->status,
            //                     ))
            //                 ));
            //             }
            //         } else {
            //             $countryid[] = $city->country->id;
            //             $stateid[] = $city->state->id;
            //             $cityid[] = $city->city->id;
            //             $countries[] = [
            //                 'id' => $city->country->id,
            //                 'country_name' => $city->country->country_name,
            //                 'country_code' => $city->country->country_code,
            //                 'country_phonecode' => $city->country->country_phonecode,
            //                 'country_currency' => $city->country->country_currency,
            //                 'country_symbol' => $city->country->country_symbol,
            //                 'status' => $city->country->status,
            //                 'timezone' => $city->country->timezone,
            //                 'state' => array(array(
            //                     'id' => $city->state->id,
            //                     'country_id' => $city->state->country_id,
            //                     'state_name' => $city->state->state_name,
            //                     'timezone' => $city->state->timezone,
            //                     'utc_offset' => $city->state->utc_offset,
            //                     'status' => $city->state->status,
            //                     'city' => array(array(
            //                         'id' => $city->city->id,
            //                         'country_id' => $city->city->country_id,
            //                         'state_id' => $city->city->state_id,
            //                         'city_name' => $city->city->city_name,
            //                         'status' => $city->city->status,
            //                     )),
            //                 ))
            //             ];
            //         }
            //     }
            // }
            


            //return $countries;
            //    print_r($cityList);exit;
        // }
        // $rideCity = RideCity::where([['company_id',Auth::user()->company_id],
        //                              ['country_id',$country_id],
        //                              ['city_id',$city_id],
        //                              ['admin_service',$admin_service]])->first();
        //  if($rideCity){
        // $countries->unique();
        // echo '<pre>';print_r($countries);exit;
        // $countries = collect($country)->sortBy("id")->values();
        
        //  }
        //  return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong')]);

    }

    public function rideprice(Request $request)
    {
        // print_r($request->all());exit;
        /* $this->validate($request, [
             'city_id' => 'required',
             'fixed' => 'required',
             'price' => 'required',
             'minute' => 'required',
             'hour' => 'required',
             'distance' => 'required',
             'waiting_free_mins' => 'required',
             'waiting_min_charge' => 'required',
        ]);*/
        $setting = Setting::where('company_id', Auth::user()->company_id)->first();

        $settings = json_decode(json_encode($setting->settings_data));
        $siteConfig = $settings->site;
        $transportConfig = $settings->transport;
        // echo '<pre>';print_r($transportConfig->geofence);exit;
        if ($transportConfig->geofence == 1) {

            $this->validate($request, [
                'geofence_id' => 'required'
            ]);
        }
        $this->validate($request, [
            'state_id' => 'required',
            // 'city_id' => 'required',
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'default_price' => 'required|numeric',
            'cancellation_fee' => 'required|numeric',
            'max_price' => 'required|numeric',
            'min_price' => 'required|numeric',
            'service_fee' => 'required|numeric',
            'airport_fee' => 'required|numeric',
            'scheduled_cancellation_fee' => 'required|numeric',
            'scheduled_minimum_fee' => 'required|numeric',
            'distance_type' => 'required|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR'
        ]);

        try {

            if ($transportConfig->geofence == 1) {
                foreach ($request->geofence_id as $geofence) {

                    $ridePrice = RideCityPrice::where('geofence_id', $geofence)->where('ride_delivery_vehicle_id', $request->ride_delivery_vehicle_id)->first();
                    if ($ridePrice == null) {
                        $ridePrice = new RideCityPrice;
                    }

                    $ridePrice->geofence_id = $geofence;

                    $ridePrice->company_id = Auth::user()->company_id;

                    $ridePrice->fixed = $request->input($geofence . '_fixed') ? $request->input($geofence . '_fixed') : $request->fixed;
                    // $ridePrice->city_id = $request->city_id;
                    $ridePrice->state_id = $request->state_id;
                    $ridePrice->ride_delivery_vehicle_id = $request->ride_delivery_vehicle_id;
                    $ridePrice->calculator = $request->input($geofence . '_calculator') ? $request->input($geofence . '_calculator') : $request->calculator;
                    if (!empty($request->input($geofence . '_price'))) {
                        $ridePrice->pricing_differs = 1;
                    } else {
                        $ridePrice->pricing_differs = 0;
                    }

                    if (!empty($request->input('default_price')) || !empty($request->default_price))
                        $ridePrice->default_price = $request->input('default_price') ? $request->input('default_price') : $request->default_price;
                    else
                        $ridePrice->default_price = 0;

                    if (!empty($request->input('cancellation_fee')) || !empty($request->cancellation_fee))
                        $ridePrice->cancellation_fee = $request->input('cancellation_fee') ? $request->input('cancellation_fee') : $request->cancellation_fee;
                    else
                        $ridePrice->cancellation_fee = 0;

                    if (!empty($request->input('max_price')) || !empty($request->max_price))
                        $ridePrice->max_price = $request->input('max_price') ? $request->input('max_price') : $request->max_price;
                    else
                        $ridePrice->max_price = 0;

                    if (!empty($request->input('min_price')) || !empty($request->min_price))
                        $ridePrice->min_price = $request->input('min_price') ? $request->input('min_price') : $request->min_price;
                    else
                        $ridePrice->min_price = 0;

                    if (!empty($request->input('service_fee')) || !empty($request->service_fee))
                        $ridePrice->service_fee = $request->input('service_fee') ? $request->input('service_fee') : $request->service_fee;
                    else
                        $ridePrice->service_fee = 0;

                    if (!empty($request->input('airport_fee')) || !empty($request->airport_fee))
                        $ridePrice->airport_fee = $request->input('airport_fee') ? $request->input('airport_fee') : $request->airport_fee;
                    else
                        $ridePrice->airport_fee = 0;

                    if (!empty($request->input('scheduled_cancellation_fee')) || !empty($request->scheduled_cancellation_fee))
                        $ridePrice->scheduled_cancellation_fee = $request->input('scheduled_cancellation_fee') ? $request->input('scheduled_cancellation_fee') : $request->scheduled_cancellation_fee;
                    else
                        $ridePrice->scheduled_cancellation_fee = 0;

                    if (!empty($request->input('scheduled_minimum_fee')) || !empty($request->scheduled_minimum_fee))
                        $ridePrice->scheduled_minimum_fee = $request->input('scheduled_minimum_fee') ? $request->input('scheduled_minimum_fee') : $request->scheduled_minimum_fee;
                    else
                        $ridePrice->scheduled_minimum_fee = 0;

                    $ridePrice->distance_type = 2;
                    // if(!empty($request->input('distance_type')) || !empty($request->distance_type))
                    //     $ridePrice->distance_type = $request->input('distance_type') ? $request->input('distance_type'): $request->distance_type;
                    // else
                    //     $ridePrice->distance_type=0;

                    if (!empty($request->input($geofence . '_price')) || !empty($request->price))
                        $ridePrice->price = $request->input($geofence . '_price') ? $request->input($geofence . '_price') : $request->price;
                    else
                        $ridePrice->price = 0;

                    if (!empty($request->input($geofence . '_minute')) || !empty($request->minute))
                        $ridePrice->minute = $request->input($geofence . '_minute') ? $request->input($geofence . '_minute') : $request->minute;
                    else
                        $ridePrice->minute = 0;

                    if (!empty($request->input($geofence . '_hour')) || !empty($request->hour))
                        $ridePrice->hour = $request->input($geofence . '_hour') ? $request->input($geofence . '_hour') : $request->hour;
                    else
                        $ridePrice->hour = 0;

                    if (!empty($request->input($geofence . '_distance')) || !empty($request->distance))
                        $ridePrice->distance = $request->input($geofence . '_distance') ? $request->input($geofence . '_distance') : $request->distance;
                    else
                        $ridePrice->distance = 0;

                    if (!empty($request->input($geofence . '_waiting_free_mins')) || !empty($request->waiting_free_mins))
                        $ridePrice->waiting_free_mins = $request->input($geofence . '_waiting_free_mins') ? $request->input($geofence . '_waiting_free_mins') : $request->waiting_free_mins;
                    else
                        $ridePrice->waiting_free_mins = 0;

                    if (!empty($request->input($geofence . '_waiting_min_charge')) || !empty($request->waiting_min_charge))
                        $ridePrice->waiting_min_charge = $request->input($geofence . '_waiting_min_charge') ? $request->input($geofence . '_waiting_min_charge') : $request->waiting_min_charge;
                    else
                        $ridePrice->waiting_min_charge = 0;

                    if (!empty($request->input($geofence . '_commission')) || !empty($request->commission))
                        $ridePrice->commission = $request->input($geofence . '_commission') ? $request->input($geofence . '_commission') : $request->commission;
                    else
                        $ridePrice->commission = 0;

                    if (!empty($request->input($geofence . '_fleet_commission')) || !empty($request->fleet_commission))
                        $ridePrice->fleet_commission = $request->input($geofence . '_fleet_commission') ? $request->input($geofence . '_fleet_commission') : $request->fleet_commission;
                    else
                        $ridePrice->fleet_commission = 0;

                    if (!empty($request->input($geofence . '_tax')) || !empty($request->tax))
                        $ridePrice->tax = $request->input($geofence . '_tax') ? $request->input($geofence . '_tax') : $request->tax;
                    else
                        $ridePrice->tax = 0;

                    if (!empty($request->input($geofence . '_peak_commission')) || !empty($request->peak_commission))
                        $ridePrice->peak_commission = $request->input($geofence . '_peak_commission') ? $request->input($geofence . 'peak_commission') : $request->peak_commission;
                    else
                        $ridePrice->peak_commission = 0;

                    if (!empty($request->input($geofence . '_waiting_commission')) || !empty($request->waiting_commission))
                        $ridePrice->waiting_commission = $request->input($geofence . '_waiting_commission') ? $request->input($geofence . 'waiting_commission') : $request->waiting_commission;
                    else
                        $ridePrice->waiting_commission = 0;

                    $ridePrice->save();

                    $incoming_peak_request = $request->input($geofence . '_peak_price') ? $request->input($geofence . '_peak_price') : $request->peak_price;
                    if ($incoming_peak_request) {

                        foreach ($incoming_peak_request as $key => $peak_price) {

                            if (!empty($peak_price['value'])) {

                                if ($peak_price['id'] != '') {
                                    $RidePeakPrice = RidePeakPrice::findOrFail($peak_price['id']);
                                } else {
                                    $RidePeakPrice = new RidePeakPrice;
                                }
                                //$RidePeakPrice = new RidePeakPrice;
                                $RidePeakPrice->ride_city_price_id = $ridePrice->id;
                                $RidePeakPrice->ride_delivery_id = $request->ride_delivery_vehicle_id;
                                $RidePeakPrice->peak_hour_id = $key;
                                $RidePeakPrice->peak_price = ($peak_price['value'] != '') ? $peak_price['value'] : '0.00';
                                $RidePeakPrice->save();
                            }
                        }
                    }
                }
            } else {
                // where('city_id', $request->city_id)

                $ridePrice = RideCityPrice::where('state_id', $request->state_id)->where('ride_delivery_vehicle_id', $request->ride_delivery_vehicle_id)->whereNull('city_id')->whereNull('geofence_id')->first();
                if ($ridePrice == null) {
                    $ridePrice = new RideCityPrice;
                }
                $ridePrice->geofence_id = null;

                $ridePrice->company_id = Auth::user()->company_id;

                $ridePrice->fixed = $request->fixed;
                // $ridePrice->city_id = $request->city_id;
                $ridePrice->state_id = $request->state_id;
                $ridePrice->ride_delivery_vehicle_id = $request->ride_delivery_vehicle_id;
                $ridePrice->calculator = $request->calculator;
                $ridePrice->pricing_differs = 0;


                if (!empty($request->input('default_price')) || !empty($request->default_price))
                    $ridePrice->default_price = $request->input('default_price') ? $request->input('default_price') : $request->default_price;
                else
                    $ridePrice->default_price = 0;

                if (!empty($request->input('cancellation_fee')) || !empty($request->cancellation_fee))
                    $ridePrice->cancellation_fee = $request->input('cancellation_fee') ? $request->input('cancellation_fee') : $request->cancellation_fee;
                else
                    $ridePrice->cancellation_fee = 0;

                if (!empty($request->input('max_price')) || !empty($request->max_price))
                    $ridePrice->max_price = $request->input('max_price') ? $request->input('max_price') : $request->max_price;
                else
                    $ridePrice->max_price = 0;

                if (!empty($request->input('min_price')) || !empty($request->min_price))
                    $ridePrice->min_price = $request->input('min_price') ? $request->input('min_price') : $request->min_price;
                else
                    $ridePrice->min_price = 0;

                if (!empty($request->input('service_fee')) || !empty($request->service_fee))
                    $ridePrice->service_fee = $request->input('service_fee') ? $request->input('service_fee') : $request->service_fee;
                else
                    $ridePrice->service_fee = 0;

                if (!empty($request->input('airport_fee')) || !empty($request->airport_fee))
                    $ridePrice->airport_fee = $request->input('airport_fee') ? $request->input('airport_fee') : $request->airport_fee;
                else
                    $ridePrice->airport_fee = 0;

                if (!empty($request->input('scheduled_cancellation_fee')) || !empty($request->scheduled_cancellation_fee))
                    $ridePrice->scheduled_cancellation_fee = $request->input('scheduled_cancellation_fee') ? $request->input('scheduled_cancellation_fee') : $request->scheduled_cancellation_fee;
                else
                    $ridePrice->scheduled_cancellation_fee = 0;

                if (!empty($request->input('scheduled_minimum_fee')) || !empty($request->scheduled_minimum_fee))
                    $ridePrice->scheduled_minimum_fee = $request->input('scheduled_minimum_fee') ? $request->input('scheduled_minimum_fee') : $request->scheduled_minimum_fee;
                else
                    $ridePrice->scheduled_minimum_fee = 0;

                $ridePrice->distance_type = 2;
                // if(!empty($request->input('distance_type')) || !empty($request->distance_type))
                //     $ridePrice->distance_type = $request->input('distance_type') ? $request->input('distance_type'): $request->distance_type;
                // else
                //     $ridePrice->distance_type=0;

                if (!empty($request->price))
                    $ridePrice->price = $request->price;
                else
                    $ridePrice->price = 0;

                if (!empty($request->minute))
                    $ridePrice->minute = $request->minute;
                else
                    $ridePrice->minute = 0;

                if (!empty($request->hour))
                    $ridePrice->hour = $request->hour;
                else
                    $ridePrice->hour = 0;

                if (!empty($request->distance))
                    $ridePrice->distance = $request->distance;
                else
                    $ridePrice->distance = 0;

                if (!empty($request->waiting_free_mins))
                    $ridePrice->waiting_free_mins = $request->waiting_free_mins;
                else
                    $ridePrice->waiting_free_mins = 0;

                if (!empty($request->waiting_min_charge))
                    $ridePrice->waiting_min_charge = $request->waiting_min_charge;
                else
                    $ridePrice->waiting_min_charge = 0;

                if (!empty($request->commission))
                    // $ridePrice->commission = $request->input($geofence.'commission') ? $request->input($geofence.'_commission'): $request->commission;
                    $ridePrice->commission = $request->commission;
                else
                    $ridePrice->commission = 0;

                if (!empty($request->fleet_commission))
                    $ridePrice->fleet_commission = $request->fleet_commission;
                else
                    $ridePrice->fleet_commission = 0;

                if (!empty($request->tax))
                    $ridePrice->tax = $request->tax;
                else
                    $ridePrice->tax = 0;

                if (!empty($request->peak_commission))
                    $ridePrice->peak_commission = $request->peak_commission;
                else
                    $ridePrice->peak_commission = 0;

                if (!empty($request->waiting_commission))
                    $ridePrice->waiting_commission = $request->waiting_commission;
                else
                    $ridePrice->waiting_commission = 0;

                $ridePrice->save();

                $incoming_peak_request = $request->peak_price;
                if ($incoming_peak_request) {

                    foreach ($incoming_peak_request as $key => $peak_price) {

                        if (!empty($peak_price['value'])) {

                            if ($peak_price['id'] != '') {
                                $RidePeakPrice = RidePeakPrice::findOrFail($peak_price['id']);
                            } else {
                                $RidePeakPrice = new RidePeakPrice;
                            }
                            //$RidePeakPrice = new RidePeakPrice;
                            $RidePeakPrice->ride_city_price_id = $ridePrice->id;
                            $RidePeakPrice->ride_delivery_id = $request->ride_delivery_vehicle_id;
                            $RidePeakPrice->peak_hour_id = $key;
                            $RidePeakPrice->peak_price = ($peak_price['value'] != '') ? $peak_price['value'] : '0.00';
                            $RidePeakPrice->save();
                        }
                    }
                }
            }

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function getRidePrice($ride_delivery_vehicle_id, $state_id, $city_id)
    {

        $rideCityPrice = RideCityPrice::where([
            ['company_id', Auth::user()->company_id],
            ['ride_delivery_vehicle_id', $ride_delivery_vehicle_id],
            ['state_id', $state_id]
        ])
            ->whereNull('city_id')->where('pricing_differs', 0)->first();
        $rideCityPriceList = RideCityPrice::where([
            ['company_id', Auth::user()->company_id],
            ['ride_delivery_vehicle_id', $ride_delivery_vehicle_id],
            ['state_id', $state_id]
        ])
            ->whereNull('city_id')->get();
        $peakHour = PeakHour::where("city_id", $city_id)->where('company_id', Auth::user()->company_id)->get();
        $geofence = Geofence::where("city_id", $city_id)->where('company_id', Auth::user()->company_id)->get();

        if (count($rideCityPriceList) > 0) {
            $ridePeakhour = [];
            foreach ($rideCityPriceList as $key => $ride_city_price_list) {
                foreach ($peakHour as $value) {
                    $peakPrice = RidePeakPrice::where([['ride_city_price_id', $ride_city_price_list->id], ['peak_hour_id', $value->id]])->first();
                    if ($peakPrice) {
                        $peakPrice['started_time'] = $value->started_time;
                        $peakPrice['ended_time'] = $value->ended_time;
                        $ridePeakhour[] = $peakPrice;
                    }
                }
                $rideCityPriceList[$key]['ridePeakhour'] = $ridePeakhour;
            }
        }

        if ($rideCityPrice) {
            foreach ($peakHour as $key => $value) {
                $RidePeakPrice = RidePeakPrice::where([['ride_city_price_id', $rideCityPrice->id], ['peak_hour_id', $value->id]])->first();
                if ($RidePeakPrice) {
                    $peakHour[$key]['ridePeakhour'] = $RidePeakPrice;
                }
            }
            return Helper::getResponse(['data' => ['price' => $rideCityPrice, 'peakHour' => $peakHour, 'geofence' => $geofence, 'priceList' => $rideCityPriceList]]);
        }

        return Helper::getResponse(['data' => ['price' => '', 'peakHour' => $peakHour, 'geofence' => $geofence, 'priceList' => $rideCityPriceList]]);
    }


    public function getcity(Request $request)
    {
        $menudetails = Menu::select('menu_type_id')->where('id', $request->menu_id)->first();

        $rideprice = RideCityPrice::select('state_id', 'city_id')->whereHas('ridedelivery_type', function ($query) use ($menudetails) {
            $query->where('ride_type_id', $menudetails->menu_type_id);
        })->get()->toArray();

        $company_cities = CompanyCity::with(['country', 'state', 'city', 'menu_city' => function ($query) use ($request) {
            $query->where('menu_id', '=', $request->menu_id);
        }])->where('company_id', Auth::user()->company_id);


         if ($request->has('country_id') && $request->country_id != null) {
            $company_cities = $company_cities->where('country_id', $request->country_id);
        }

         if ($request->has('state_id') && $request->state_id != null) {
            $company_cities = $company_cities->where('state_id', $request->state_id);
        }



       
        
        $cities = $company_cities->get()->toArray();

        foreach ($cities as $key => $value) {

            $cities[$key]['city_price'] = 0;
            $cities[$key]['state_price'] = 0;

            if (in_array($value['city_id'], array_column($rideprice, 'city_id'))) {

                $cities[$key]['city_price'] = 1;
            }

            if (in_array($value['state_id'], array_column($rideprice, 'state_id'))) {

                $cities[$key]['state_price'] = 1;
            }
        }
        $data = $this->paginate($cities, 6000, $request->page);


        return Helper::getResponse(['data' => $data]);
    }


    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function getvehicletype()
    {

        $vehicle_type = RideType::all();

        return Helper::getResponse(['data' => ['vehicle_type' => $vehicle_type]]);
    }

    public function updateStatus(Request $request, $id)
    {

        try {

            $datum = RideDeliveryVehicle::findOrFail($id);

            if ($request->has('status')) {
                if ($request->status == 1) {
                    $datum->status = 0;
                } else {
                    $datum->status = 1;
                }
            }
            $datum->save();


            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /* 
        Get company country list for proper categorization of country, state and city our price list 
    */
    public function getcompanycountry(){
        try{
            $companyCountry = CompanyCity::with(['country'])->where('status', 1)->groupBy("country_id")->get();
            $data = [];
            if(!empty($companyCountry)){                
                foreach($companyCountry as $country){
                    $data[] = [
                        'id' => $country->country->id,
                        'country_name' => $country->country->country_name,
                        'country_code' => $country->country->country_code,
                        'country_phonecode' => $country->country->country_phonecode,
                        'country_currency' => $country->country->country_currency,
                        'country_symbol' => $country->country->country_symbol,
                        'status' => $country->country->status,
                        'timezone' => $country->country->timezone,
                    ];
                }
            }            
            return Helper::getResponse(['data' => $data]);
        } catch(\Throwable $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /* 
        Get company state list for proper categorization of country, state and city our price list 
    */
    public function getcompanystatelist($id){
        try{
            $companyState = CompanyCity::with(['state'])->where(['country_id' => $id,'status' => 1])->groupBy("state_id")->get();            
            $data = [];
            if(!empty($companyState)){
                foreach($companyState as $state){
                    $data[] = [
                        'id' => $state->state->id,
                        'country_id' => $state->state->country_id,
                        'state_name' => $state->state->state_name,
                        'timezone' => $state->state->timezone,
                        'utc_offset' => $state->state->utc_offset,                    
                        'status' => $state->state->status,                    
                    ];
                }
            }            
            return Helper::getResponse(['data' => $data]);
        } catch(\Throwable $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /* 
        Get company city list for proper categorization of country, state and city our price list 
    */
    public function getcompanycitylist($id){
        try{
            $companyCity = CompanyCity::with(['city'])->where(['state_id' => $id,'status' => 1])->groupBy("city_id")->get();            
            $data = [];
            if(!empty($companyCity)){
                foreach($companyCity as $city){
                    
                    $data[] = [
                        'id' => $city->city->id,
                        'country_id' => $city->city->country_id,
                        'state_id' => $city->city->state_id,
                        'city_name' => $city->city->city_name,                                          
                        'status' => $city->city->status,                    
                    ];
                }
            }            
            return Helper::getResponse(['data' => $data]);
        } catch(\Throwable $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
