<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Transport\RideCars;
use App\Models\Transport\RideCarsImages;
use App\Models\Transport\RideCarsType;
use App\Models\Common\AdminService;
use App\Models\Common\MenuCity;
use App\Models\Common\Menu;
use App\Models\Common\Setting;
use App\Models\Common\Owner;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class CarsController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(RideCars $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = RideCars::select('ride_cars.*', 'ride_common.owners.first_name', 'ride_common.owners.last_name', 'ride_cars_types.name as type_name')->join('ride_common.owners', 'ride_common.owners.id', '=', 'ride_cars.owner_id')->join('ride_cars_types','ride_cars_types.id','=','ride_cars.type')->where('ride_cars.company_id', '=', Auth::user()->company_id)->where('ride_cars.is_deleted', '=', 0);

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

    public function carsList(Request $request)
    {
        $datum = RideCars::select('ride_cars.*', 'ride_common.owners.first_name', 'ride_common.owners.last_name', 'ride_cars_types.name as type_name')->join('ride_common.owners', 'ride_common.owners.id', '=', 'ride_cars.owner_id')->join('ride_cars_types','ride_cars_types.id','=','ride_cars.type')->where(['ride_cars.company_id' => Auth::user()->company_id, 'ride_cars.is_deleted' => 0]);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function ownerList(Request $request)
    {
        $datum = Owner::where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);
        $data = $datum->get();

        return Helper::getResponse(['data' => $data]);
    }

    public function store(Request $request)
    {	
        $this->validate($request, [
            'owner_id' => 'required|max:255',
            'model' => 'required|numeric',
            'type' => 'required',
            'capacity' => 'required'
        ]);

        try {

            //$RideType = RideCars::findOrFail($request->ride_type_id);

            $rideDeliveryVehicle = new RideCars;
            $rideDeliveryVehicle->company_id = Auth::user()->company_id;
            $rideDeliveryVehicle->model = $request->model;
            $rideDeliveryVehicle->owner_id = $request->owner_id;
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
            $rideDeliveryVehicle->protection = $request->protection;
            // $rideDeliveryVehicle->driver_name = $request->driver_name;
            $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
            $rideDeliveryVehicle->pickup_address = $request->pickup_address;
            $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
            $rideDeliveryVehicle->about = $request->about;
            $rideDeliveryVehicle->hourly_charges = $request->hourly_charges;
            $rideDeliveryVehicle->daily_charges = $request->daily_charges;
            $rideDeliveryVehicle->weekly_charges = $request->weekly_charges;
            $rideDeliveryVehicle->trip_fee = $request->trip_fee;
            $rideDeliveryVehicle->cancellation_charges = $request->cancellation_charges;
            $rideDeliveryVehicle->insurance_charges = $request->insurance_charges;
            $rideDeliveryVehicle->booking_fee = $request->booking_fee;
            $rideDeliveryVehicle->sales_tax = $request->sales_tax;  

            if ($request->hasFile('inspection')) {
                $rideDeliveryVehicle->inspection = Helper::upload_file($request->file('inspection'), 'vehicle/inspection');
            }
            if ($request->hasFile('insurance')) {
                $rideDeliveryVehicle->insurance = Helper::upload_file($request->file('insurance'), 'vehicle/insurance');
            }
            if ($request->hasFile('registration')) {
                $rideDeliveryVehicle->registration = Helper::upload_file($request->file('registration'), 'vehicle/registration');
            }
            // if ($request->hasFile('driver_profile')) {
            //     $rideDeliveryVehicle->driver_profile = Helper::upload_file($request->file('driver_profile'), 'vehicle/driver');
            // }

            $rideDeliveryVehicle->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


    public function show($id)
    {
        try {
            $rideDeliveryVehicle = RideCars::findOrFail($id);
            return Helper::getResponse(['data' => $rideDeliveryVehicle]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'model' => 'required|max:255',
            'capacity' => 'required|numeric',
            'registration' => 'mimes:ico,png',
            'insurance' => 'mimes:ico,png',
            'inspection' => 'mimes:ico,png',
        ]);

        try {
            $rideDeliveryVehicle = RideCars::findOrFail($id);
            if ($rideDeliveryVehicle) {
                
                if ($request->hasFile('inspection')) {
                    if ($rideDeliveryVehicle->inspection) {
                        Helper::delete_picture($rideDeliveryVehicle->inspection);
                    }
                    $rideDeliveryVehicle->inspection = Helper::upload_file($request->file('inspection'), 'cars/inspection');
                    $rideDeliveryVehicle->inspection_exp = $request->inspection_exp;
                }
                if ($request->hasFile('insurance')) {
                    if ($rideDeliveryVehicle->insurance) {
                        Helper::delete_picture($rideDeliveryVehicle->insurance);
                    }
                    $rideDeliveryVehicle->insurance = Helper::upload_file($request->file('insurance'), 'cars/insurance');
                    $rideDeliveryVehicle->insurance_exp = $request->insurance_exp;
                }
                if ($request->hasFile('registration')) {
                    if ($rideDeliveryVehicle->insurance) {
                        Helper::delete_picture($rideDeliveryVehicle->registration);
                    }
                    $rideDeliveryVehicle->registration = Helper::upload_file($request->file('registration'), 'cars/registration');
                    $rideDeliveryVehicle->registration_exp = $request->registration_exp;
                }
                // if ($request->hasFile('driver_profile')) {
                //     if ($rideDeliveryVehicle->driver_profile) {
                //         Helper::delete_picture($rideDeliveryVehicle->driver_profile);
                //     }
                //     $rideDeliveryVehicle->driver_profile = Helper::upload_file($request->file('driver_profile'), 'cars/driver');
                // }
                $rideDeliveryVehicle->model = $request->model;
                $rideDeliveryVehicle->owner_id = $request->owner_id;
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
                // $rideDeliveryVehicle->licence_number = $request->licence_number;
                // $rideDeliveryVehicle->licence_state = $request->licence_state;
                $rideDeliveryVehicle->protection = 1;
                // $rideDeliveryVehicle->driver_name = $request->driver_name;
                $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
                $rideDeliveryVehicle->pickup_address = $request->pickup_address;
                $rideDeliveryVehicle->milleage_allowed = $request->milleage_allowed;
                $rideDeliveryVehicle->about = $request->about;
                $rideDeliveryVehicle->hourly_charges = $request->hourly_charges;
                $rideDeliveryVehicle->daily_charges = $request->daily_charges;
                $rideDeliveryVehicle->weekly_charges = $request->weekly_charges;
                $rideDeliveryVehicle->trip_fee = $request->trip_fee;
                $rideDeliveryVehicle->cancellation_charges = $request->cancellation_charges;
                $rideDeliveryVehicle->insurance_charges = $request->insurance_charges;
                $rideDeliveryVehicle->booking_fee = $request->booking_fee;
                $rideDeliveryVehicle->sales_tax = $request->sales_tax;  
                $rideDeliveryVehicle->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]);
            }
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $model = RideCars::findorFail($id);

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
    


    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    public function updateStatus(Request $request, $id)
    {

        try {

            $datum = RideCars::findOrFail($id);

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



    public function carstype(Request $request){
        $datum = RideCarstype::where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

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

    public function carstypeList(Request $request)
    {
        $datum = RideCarstype::where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function storetype(Request $request)
    {   
        try {

            //$RideType = RideCars::findOrFail($request->ride_type_id);

            $rideCarsType = new RideCarstype;
            $rideCarsType->company_id = Auth::user()->company_id;
            $rideCarsType->name = $request->name;
            $rideCarsType->price = $request->price;
            $rideCarsType->status = $request->status;
            $rideCarsType->pricing_method = $request->pricing_method;
            
            if ($request->hasFile('image')) {
                $rideCarsType->image = Helper::upload_file($request->file('image'), 'carstype/image');
            }
            $rideCarsType->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


    public function showtype($id)
    {
        try {
            $rideCarsType = RideCarstype::findOrFail($id);
            return Helper::getResponse(['data' => $rideCarsType]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function updatetypeStatus(Request $request, $id)
    {

        try {

            $datum = RideCarstype::findOrFail($id);

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

    public function updatetype(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'pricing_method' => 'required|max:255',
        ]);

        try {
            $rideCarsType = RideCarstype::findOrFail($id);
            if ($rideCarsType) {
                if ($request->hasFile('image')) {
                    if ($rideCarsType->image) {
                        Helper::delete_picture($rideCarsType->image);
                    }
                    $rideCarsType->image = Helper::upload_file($request->file('image'), 'carstype/image');
                }
                $rideCarsType->name = $request->name;
                $rideCarsType->price = $request->price;
                $rideCarsType->status = $request->status;
                $rideCarsType->pricing_method = $request->pricing_method;
                
                $rideCarsType->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]);
            }
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function destroytype($id)
    {
        try {
            $model = RideCarstype::findorFail($id);

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

    public function images($id)
    {
        try {
            $images = RideCarsImages::where('car_id',$id)->get();
            return Helper::getResponse(['data' => $images]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function updateimages(Request $request, $id)
    {
        try {
            $rideCarsImages=array();
            
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

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
