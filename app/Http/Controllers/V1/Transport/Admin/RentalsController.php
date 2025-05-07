<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Transport\RideRental;
use App\Models\Transport\RideCars;
use App\Models\Transport\RideCarsImages;
use App\Models\Transport\RideCarsType;
use App\Models\Transport\RideRentalCharges;
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


class RentalsController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(RideRental $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = RideRental::select('ride_common.owners.first_name as firstname', 'ride_common.owners.last_name as lasttname', 'ride_common.users.first_name as first_name', 'ride_common.users.last_name as last_tname', 'ride_rentals.*')->join('ride_common.owners', 'ride_rentals.owner_id', '=', 'ride_common.owners.id')->join('ride_common.users', 'ride_rentals.user_id', '=', 'ride_common.users.id')->where(['ride_rentals.is_deleted' => 0]);

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

    public function bookingList(Request $request)
    {
        $datum = RideRental::where(['is_deleted' => 0]);

        if ($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if ($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        $data = $datum->paginate(10);

        return Helper::getResponse(['data' => $data]);
    }

    public function show()
    {
        try {
            $rentalCharges = RideRentalCharges::findOrFail('1');
            return Helper::getResponse(['data' => $rentalCharges]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
       

        try {
            $rentalCharges = RideRentalCharges::findOrFail(1);
           
                $rentalCharges->booking_fee = $request->booking_fee;
                $rentalCharges->insurance_fee = $request->insurance_fee;
                $rentalCharges->cancellation_fee = $request->cancellation_fee;
                $rentalCharges->sales_tax = $request->sales_tax;
                
                $rentalCharges->save();
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
?>