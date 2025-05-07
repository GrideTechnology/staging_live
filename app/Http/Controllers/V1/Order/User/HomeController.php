<?php
namespace App\Http\Controllers\V1\Order\User;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Order\Store;
use App\Models\Order\Cuisine;
use App\Models\Common\UserAddress;
use App\Models\Common\RequestFilter;
use App\Models\Order\StoreItemAddon;
use App\Models\Order\StoreItem;
use App\Models\Order\StoreCart;
use App\Models\Common\Rating;
use App\Models\Common\User;
use App\Models\Common\State;
use App\Models\Order\StoreCityPrice;
use App\Models\Order\StoreOrderDispute;
use Auth;
use DB;
use Carbon\Carbon;
use App\Models\Common\Setting;
use App\Models\Order\StoreCartItemAddon;
use App\Models\Common\Promocode;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreOrderInvoice;
use App\Models\Order\StoreOrderStatus;
use App\Models\Common\AdminService;
use App\Models\Common\UserRequest;
use App\Models\Common\PaymentLog;
use App\Services\PaymentGateway;
use App\Models\Common\Card;
use App\Models\Common\Item;
use App\Services\Transactions;
use App\Services\SendPushNotification;
use App\Services\V1\Common\UserServices;
use App\Services\V1\Order\Order;
use App\Traits\Actions;
use App\Models\Common\CompanyCity;
use App\Models\Common\Provider;
use App\Models\Common\UserVisit;
use Illuminate\Support\Facades\Log;
use Braintree\ClientToken;
use Braintree;
use Braintree\Configuration;


class HomeController extends Controller
{
    use Actions;
    //Store Type

    public function recent_store_list(Request $request, $id)
    //public function store_list(Request $request, $id)
    {
        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        // Get the IDs of the last visited stores
        return $lastVisitedStores = UserVisit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->pluck('store_id');

        // Retrieve store list, including the filter for recent stores
        return $store_list = Store::with('categories', 'storetype', 'StoreCusinie', 'StoreCusinie.cuisine')
    ->where('company_id', $company_id)
    ->where('store_type_id', $id)
    ->whereIn('id', $lastVisitedStores) // Filter stores by last visited IDs
    ->select([
        'id', 'store_type_id', 'company_id', 'store_name', 'store_location',
        'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time',
        'free_delivery', 'is_veg', 'rating', 'offer_percent',
    ])
    ->where('status', 1)
    ->whereHas('storetype', function ($q) use ($request) {
        $q->where('status', 1);
    })
    ->get(); // Execute the query and retrieve results

    return Helper::getResponse(['data' => $store_list]);

    }

    public function store_list(Request $request, $id)
    {
        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        $settings = json_decode(json_encode(Setting::where('company_id', $company_id)->first()->settings_data));

        $city_id = $user ? $user->city_id : $request->city_id;

        $store_list_all = Store::with('categories', 'storetype', 'StoreCusinie', 'StoreCusinie.cuisine')->where('company_id', $company_id)->where('store_type_id', $id)->select('id', 'store_type_id', 'company_id', 'store_name', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent');
        $store_list_all->whereHas('storetype', function ($q) use ($request) {
            $q->where('status', 1);
        });
        if ($request->has('filter') && $request->filter != '') {
            $store_list_all->whereHas('StoreCusinie', function ($q) use ($request) {
                $q->whereIn('cuisines_id', [$request->filter]);
            });
        }
        if ($request->has('qfilter') && $request->qfilter != '') {
            if ($request->qfilter == 'non-veg') {
                $store_list_all->where('is_veg', 'Non Veg');
            }
            if ($request->qfilter == 'pure-veg') {
                $store_list_all->where('is_veg', 'Pure Veg');
            }
            if ($request->qfilter == 'freedelivery') {
                $store_list_all->where('free_delivery', '1');
            }
        }
        if ($request->has('latitude') && $request->has('latitude') != '' && $request->has('longitude') && $request->has('longitude') != '') {
            if(!empty($user->id)){
            $userModel = User::where(['id' => $user->id])->first();
            $userModel->latitude = $request->latitude;
            $userModel->longitude = $request->longitude;
            $userModel->save();
            }

            $longitude = $request->longitude;
            $latitude = $request->latitude;
            $distance = $settings->order->search_radius;
            // config('constants.store_search_radius', '10');
            if ($distance > 0) {
                $store_list_all->select('*', \DB::raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"))
                    ->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance"); //6371
            }
        }

        $store_list_all->where('status', 1);
        $store_list_all->where('is_deleted', 0);
        $store_list = $store_list_all->get();
        $store_list->map(function ($shop) {
            if ($shop->StoreCusinie->count() > 0) {
                foreach ($shop->StoreCusinie as $cusine) {
                    $cusines_list[] = $cusine->cuisine->name;
                }
            } else {
                $cusines_list = [];
            }
            $cuisinelist = implode(',', $cusines_list);
            $shop->cusine_list = $cuisinelist;
            $shop->shopstatus = $this->shoptime($shop->id);

            return $shop;
        });

        return Helper::getResponse(['data' => $store_list]);
    }

    public function store_list_rating(Request $request, $id)
    {
        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        $settings = json_decode(json_encode(Setting::where('company_id', $company_id)->first()->settings_data));

        $city_id = $user ? $user->city_id : $request->city_id;

        $store_list_all = Store::with('categories', 'storetype', 'StoreCusinie', 'StoreCusinie.cuisine')->where('company_id', $company_id)->where('store_type_id', $id)->select('id', 'store_type_id', 'company_id', 'store_name', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent');
        $store_list_all->whereHas('storetype', function ($q) use ($request) {
            $q->where('status', 1);
        });
        if ($request->has('filter') && $request->filter != '') {
            $store_list_all->whereHas('StoreCusinie', function ($q) use ($request) {
                $q->whereIn('cuisines_id', [$request->filter]);
            });
        }
        if ($request->has('qfilter') && $request->qfilter != '') {
            if ($request->qfilter == 'non-veg') {
                $store_list_all->where('is_veg', 'Non Veg');
            }
            if ($request->qfilter == 'pure-veg') {
                $store_list_all->where('is_veg', 'Pure Veg');
            }
            if ($request->qfilter == 'freedelivery') {
                $store_list_all->where('free_delivery', '1');
            }
        }
        if ($request->has('latitude') && $request->has('latitude') != '' && $request->has('longitude') && $request->has('longitude') != '') {
            $userModel = User::where(['id' => $user->id])->first();
            $userModel->latitude = $request->latitude;
            $userModel->longitude = $request->longitude;
            $userModel->save();

            $longitude = $request->longitude;
            $latitude = $request->latitude;
            $distance = $settings->order->search_radius;
            // config('constants.store_search_radius', '10');
            if ($distance > 0) {
                $store_list_all->select('*', \DB::raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"))
                    ->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance"); //6371
            }
        }
        $store_list_all->orderBy('rating', 'desc');
        $store_list_all->where('status', 1);
        $store_list = $store_list_all->get();
        $store_list->map(function ($shop) {
            if ($shop->StoreCusinie->count() > 0) {
                foreach ($shop->StoreCusinie as $cusine) {
                    $cusines_list[] = $cusine->cuisine->name;
                }
            } else {
                $cusines_list = [];
            }
            $cuisinelist = implode(',', $cusines_list);
            $shop->cusine_list = $cuisinelist;
            $shop->shopstatus = $this->shoptime($shop->id);
            return $shop;
        });
        return Helper::getResponse(['data' => $store_list]);
    }
    //Service Sub Category
    public function cusine_list(Request $request, $id)
    {

        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        $cusine_list = Cuisine::where('company_id', $company_id)->where('store_type_id', $id)
            ->get();
        return Helper::getResponse(['data' => $cusine_list]);
    }
    //store details 
    public function store_details(Request $request, $id)
    {

        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        $settings = json_decode(json_encode(Setting::where('company_id', $company_id)->first()->settings_data));

        $store_details = Store::with([
            'categories', 'storetype',
            'storecart' => function ($query) use ($request, $user) {
                $query->where('user_id', $user ? $user->id : null);
            }, 'products' => function ($query) use ($request) {
                $query->where('status', 1);
                if ($request->has('filter') && $request->filter != '') {
                    $query->where('store_category_id', $request->filter);
                }
                if ($request->has('search') && $request->search != '') {
                    $query->where('item_name', 'LIKE', '%' . $request->search . '%');
                }
                if ($request->has('qfilter') && $request->qfilter != '') {
                    if ($request->qfilter == 'non-veg') {
                        $query->where('is_veg', 'Non Veg');
                    }
                    if ($request->qfilter == 'pure-veg') {
                        $query->where('is_veg', 'Pure Veg');
                    }
                    if ($request->qfilter == 'discount') {
                        $query->where('item_discount', '<>', '');
                    }
                }
            }, 'products.itemsaddon', 'products.itemsaddon.addon', 'products.itemcart' => function ($query) use ($request, $user) {
                $query->where('user_id', $user ? $user->id : null);
            }
        ])
            ->whereHas('storetype', function ($q) use ($request) {
                $q->where('status', 1);
            });
        $store_details->where('status', 1)->where('company_id', $this->company_id);
        if ($request->has('latitude') && $request->has('latitude') != '' && $request->has('longitude') && $request->has('longitude') != '') {
            $longitude = $request->longitude;
            $latitude = $request->latitude;
            $distance = $settings->order->search_radius;
            // config('constants.store_search_radius', '10');
            $store_details->select('id', 'store_type_id', 'company_id', 'store_name', 'currency_symbol', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent', \DB::raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"))

                ->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance"); //6371
        } else {

            $store_details->select('id', 'store_type_id', 'company_id', 'store_name', 'currency_symbol', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent');
        }
        $store_detail = $store_details->find($id);

        $store_detail->products->map(function ($products) {
            $productId = $products->id;

            //$productIds = $store_detail->products->pluck('id')->toArray();

            $countLikesDislikeItemId = Item::where('item_id', $productId)->count();
            $countLikes = Item::where('item_id', $productId)
                ->where('type', 'like')
                ->count();
            $countDisLikes = Item::where('item_id', $productId)
                ->where('type', 'dislike')
                ->count();

            if ($countLikesDislikeItemId > 0) {
                $likePercentage = $countLikes / $countLikesDislikeItemId * 100;
                $disLikePercentage = $countDisLikes / $countLikesDislikeItemId * 100;
            } else {
                // Handle the case where there are no records that match the criteria
                $likePercentage = 0;
                $disLikePercentage = 0;
            }
            $products->likePercentage = $likePercentage;
            $products->disLikePercentage = $disLikePercentage;

            $products->offer = 0;
            if ($products->item_discount)
                $products->offer = 1;

            if ($products->item_discount_type == "PERCENTAGE") {
                $products->product_offer = ($products->item_price - ($products->item_discount / 100) * $products->item_price);
            } else if ($products->item_discount_type == "AMOUNT") {
                $products->product_offer = $products->item_price - $products->item_discount;
            }
            $products->product_offer = ($products->product_offer > 0) ? $products->product_offer : 0;

            $products->itemsaddon->filter(function ($addon) {
                $addon->addon_name = $addon->addon->addon_name;;
                unset($addon->addon);
                return $addon;
            });

            return $products;
        });

        $totalcartprice = 0;
        $store_detail->totalstorecart = count($store_detail->storecart);
        // foreach($store_detail->storecart as $cart){
        //     $totalcartprice = $totalcartprice + $cart->total_item_price;
        // }

        unset($store_detail->storecart);
        if (!empty($store_detail)) {
            $store_detail->shopstatus = $this->shoptime($id);
        }
        $newtotalitemprice = $this->totalusercart()->sum('total_item_price');
        $store_detail->usercart = count($this->totalusercart());
        $store_detail->totalcartprice = (float)number_format((float)$newtotalitemprice, 2, '.', '');

        // $productIds = $store_detail->products->pluck('id')->toArray();

        // $countLikesDislikeItemId = Item::whereIn('item_id', $productIds)->count();
        // $countLikes = Item::whereIn('item_id', $productIds)
        //     ->where('type', 'like')
        //     ->count();
        // $countDisLikes = Item::whereIn('item_id', $productIds)
        //     ->where('type', 'dislike')
        //     ->count();

        // $likePercentage = $countLikes / $countLikesDislikeItemId * 100;
        // $disLikePercentage = $countDisLikes / $countLikesDislikeItemId * 100;



        return Helper::getResponse(['data' => $store_detail]);
    }

    public function shoptime($id)
    {
        $Shop = Store::find($id);
        $day_short = strtoupper(\Carbon\Carbon::now()->format('D'));

        if ($shop_timing = $Shop->timings->where('store_day', 'ALL')
            ->pluck('store_start_time', 'store_end_time')->toArray()
        ) {
        } else {
            $shop_timing = $Shop->timings->where('store_day', $day_short)
                ->pluck('store_start_time', 'store_end_time')->toArray();
        }
        if (!empty($shop_timing)) {
            $state_id = CompanyCity::select('state_id')->where('city_id', $Shop->city_id)->where('company_id', $Shop->company_id)->first();

            $timezone = isset($state_id->state_id) ? State::find($state_id->state_id)->timezone : 'UTC';

            $key = key($shop_timing);
            $current_time = \Carbon\Carbon::now($timezone);
            $start_time = (Carbon::createFromFormat('H:i', (Carbon::parse($key)->format('H:i')), $timezone))->setTimezone('UTC');
            //$start_time = \Carbon\Carbon::parse($key); 
            $end_time = (Carbon::createFromFormat('H:i', (Carbon::parse($shop_timing[$key])->format('H:i')), $timezone))->setTimezone('UTC');
            $end_time = \Carbon\Carbon::parse($shop_timing[$key]);
            if ($current_time->between($start_time, $end_time)) {
                return $timeout_class = 'OPEN';
            } else {
                return $timeout_class = 'CLOSED';
            }
        } else {
            return 'CLOSED';
        }
    }

    public function useraddress(Request $request)
    {
        $CartStoreDetails = StoreCart::with('store')->select('store_id')->where('user_id', $this->user->id)->first();
        if ($CartStoreDetails != null) {
            $storeId = $CartStoreDetails->store_id;
            $distance = isset($this->settings->order->store_search_radius) ? $this->settings->order->store_search_radius : '';
            $latitude = $CartStoreDetails->store->latitude;
            $longitude = $CartStoreDetails->store->longitude;
            $user_address = UserAddress::select(
                DB::raw("(CASE WHEN ((3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance)  THEN 1 ELSE 0 END) AS is_nearby"),
                DB::Raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),
                'id',
                'user_id',
                'company_id',
                'address_type',
                'map_address',
                'latitude',
                'longitude',
                'flat_no',
                'street',
                'title'
            )
                ->where('user_id', $this->user->id)
                ->where('company_id', $this->company_id) //6371
                // ->select('id','user_id','company_id','address_type','map_address','latitude','longitude','flat_no','street','title')
                ->get();
        } else {
            $user_address = UserAddress::where('user_id', $this->user->id)->where('company_id', $this->company_id)
                ->select('id', 'user_id', 'company_id', 'address_type', 'map_address', 'latitude', 'longitude', 'flat_no', 'street', 'title')->get();
        }
        return Helper::getResponse(['data' => $user_address]);
    }

    public function show_addons(Request $request, $id)
    {
        $item_addons = $Item = StoreItem::with(['itemsaddon', 'itemsaddon.addon', 'itemcartaddon'])->where('company_id', $this->company_id)->select('id', 'item_name', 'item_price', 'item_discount_type', 'item_discount')->find($id);
        $itemcartaddon = $item_addons->itemcartaddon->pluck('store_item_addons_id', 'store_item_addons_id')->toArray();
        if ($item_addons->item_discount_type == "PERCENTAGE") {
            $item_addons->item_price = $Item->item_price = $Item->item_price - (($Item->item_discount / 100) * $Item->item_price);
        } else if ($Item->item_discount_type == "AMOUNT") {
            $item_addons->item_price = $Item->item_price = $Item->item_price - ($Item->item_discount);
        }
        $item_addons->item_price = $Item->item_price = $Item->item_price > 0 ? $Item->item_price : 0;
        //dd($item_addons->item_price);

        unset($item_addons->itemcartaddon);
        $item_addons->itemcartaddon = $itemcartaddon;
        $item_addons->itemsaddon->map(function ($da) {
            $da->addon_name = $da->addon->addon_name;;
            unset($da->addon);
            return $da;
        });
        return Helper::getResponse(['data' => $item_addons]);
    }

    public function cart_addons(Request $request, $id)
    {
        $cart = StoreCart::find($id);
        $item_addons = $Item = StoreItem::with(['itemsaddon', 'itemsaddon.addon', 'itemcartaddon' => function ($query) use ($cart) {
            $query->where('store_cart_id', $cart->id);
        }, 'itemcart'])->where('company_id', Auth::guard('user')->user()->company_id)->select('id', 'item_name', 'item_price', 'item_discount_type', 'item_discount')->find($cart->store_item_id);
        $itemcartaddon = $item_addons->itemcartaddon->pluck('store_item_addons_id', 'store_item_addons_id')->toArray();
        if ($item_addons->item_discount_type == "PERCENTAGE") {
            $item_addons->item_price = $Item->item_price = $Item->item_price - (($Item->item_discount / 100) * $Item->item_price);
        } else if ($Item->item_discount_type == "AMOUNT") {
            $item_addons->item_price = $Item->item_price = $Item->item_price - ($Item->item_discount);
        }
        $item_addons->item_price = $Item->item_price = $Item->item_price > 0 ? $Item->item_price : 0;
        unset($item_addons->itemcartaddon);
        $item_addons->itemcartaddon = $itemcartaddon;
        $item_addons->itemsaddon->map(function ($da) {
            $da->addon_name = $da->addon->addon_name;
            unset($da->addon);
            return $da;
        });
        return Helper::getResponse(['data' => $item_addons]);
    }


    public function addcart(Request $request)
    {
        $this->validate($request, [
            'item_id'    => 'required',
            'qty' => 'required'
        ]);

        $addonStatus = true;
        $quantity = $request->qty;
        $cart = StoreCart::where('user_id', $this->user->id)->orderBy('id', 'desc')->first();
        $Item = StoreItem::find($request->item_id);
        $checkAddons = $request->addons ? @explode(',', $request->addons) : [];
        if (!empty($cart)) {
            if ($Item->store_id != $cart->store_id) {
                StoreCart::where('user_id', $this->user->id)->delete();
                $cart = 0;
            } else {
                $response = $this->findCart($request, $checkAddons);
                $addonStatus = $response['addonStatus'];
                ($request->repeat ? $checkAddons = $response['totalAddons'] : '');
                $cart = $response['cart'] ? StoreCart::where('user_id', $this->user->id)->where('store_item_id', $request->item_id)->where('id', $response['cart'])->first() : 0;
                $quantity = $request->repeat ? (($request->cart_id) ? $quantity : $cart->quantity + 1) : ($cart ? $cart->quantity + 1 : $quantity);
            }
        }

        if (!$cart) {
            $cart = new StoreCart();
        }

        if ($request->customize == 1) { //$request->customize == "true"            
            $oldCart = StoreCart::where('user_id', $this->user->id)->where('id', $request->cart_id)->first();
            $quantity = $oldCart->quantity;
            if ($oldCart->id != $cart->id) {
                $oldCart->delete();
                $quantity = $oldCart->quantity + $cart->quantity;
            } else {
                $quantity = $oldCart->quantity + 1;
            }
        }

        $cart->quantity = $quantity;
        $cart->user_id = $this->user->id;
        $cart->store_id = $Item->store_id;
        $cart->store_item_id = $request->item_id;
        $newitemprice = $Item->item_price;
        $cart->item_price = $Item->item_price = (float)number_format((float)$newitemprice, 2, '.', '');
        // if ($Item->item_discount_type == "PERCENTAGE") {
        //     $newitemprice = $Item->item_price - (($Item->item_discount / 100) * $Item->item_price);
        //     $cart->item_price = $Item->item_price = (float)number_format((float)$newitemprice, 2, '.', '');
        // } else if ($Item->item_discount_type == "AMOUNT") {
        //     $newitemprice = $Item->item_price = $Item->item_price - ($Item->item_discount);
        //     $cart->item_price = $Item->item_price = (float)number_format((float)$newitemprice, 2, '.', '');
        // }
        $newitemprice = $Item->item_price = $cart->item_price > 0 ? $cart->item_price : 0;
        $cart->item_price = (float)number_format((float)$newitemprice, 2, '.', '');

        $cart->company_id = $this->company_id;
        $cart->note = $request->note;
        $newtotalitemprice = ($quantity) * ($Item->item_price);
        $cart->total_item_price = (float)number_format((float)$newtotalitemprice, 2, '.', '');
        $cart->save();
        $tot_item_addon_price = 0;
        $cart = StoreCart::find($cart->id);
        if ($addonStatus) {
            if ($request->customize == 1) { //$request->customize == "true"
                StoreCartItemAddon::where('store_cart_id', $oldCart->id)->delete();
            } else {
                StoreCartItemAddon::where('store_cart_id', $cart->id)->delete();
            }
        }
        if (count($checkAddons) > 0) {
            $addons = StoreItemAddon::whereIn('id', $checkAddons)->pluck('price', 'id')->toArray();
            foreach ($addons as $key => $item) {
                if (in_array($key, $checkAddons)) {
                    $cartaddon = StoreCartItemAddon::where('store_cart_id', $cart->id)->where('store_item_addons_id', $key)->where('store_cart_item_id', $cart->store_item_id)->first();
                    $cartaddon = $cartaddon ? $cartaddon : new StoreCartItemAddon();
                    $cartaddon->store_cart_item_id = $cart->store_item_id;
                    $cartaddon->store_item_addons_id = $key;
                    $cartaddon->store_cart_id = $cart->id;
                    $cartaddon->addon_price = $item;
                    $cartaddon->company_id = $this->company_id;
                    $cartaddon->save();
                    $tot_item_addon_price += $item;
                }
            }
        }
        $cart->tot_addon_price = $quantity * $tot_item_addon_price;
        $newtotalitemprice = ($quantity * $tot_item_addon_price);
        $cart->total_item_price += (float)number_format((float)$newtotalitemprice, 2, '.', '');
        $cart->save();
        return $this->viewcart($request);
    }

    public function findCart($request, $checkAddons)
    {
        $cart = 0;
        $status = true;
        $totalAddons = [];

        if ($request->repeat) {
            if ($request->cart_id) {
                $cartId = StoreCart::where('user_id', Auth::guard('user')->user()->id)->where('store_item_id', $request->item_id)->where('id', $request->cart_id)->orderBy('id', 'desc')->first();
            } else {
                $cartId = StoreCart::where('user_id', Auth::guard('user')->user()->id)->where('store_item_id', $request->item_id)->orderBy('id', 'desc')->first();
            }
            $totalAddons = StoreCartItemAddon::where('store_cart_id', $cartId->id)->pluck('store_item_addons_id')->toArray();
            $cart = $cartId->id;
            $status = false;
            $response = [
                'cart' => $cart,
                'addonStatus' => $status,
                'totalAddons' => $totalAddons
            ];
            return $response;
        }

        $cartIds = StoreCart::where('user_id', Auth::guard('user')->user()->id)->where('store_item_id', $request->item_id)->pluck('id');
        if (trim($request->cart_id) && !$request->customize) {
            $cartIds = StoreCart::where('user_id', Auth::guard('user')->user()->id)->where('store_item_id', $request->item_id)->where('id', $request->cart_id)->pluck('id');
        }

        foreach ($cartIds as $cartId) {
            $totalAddons = [];
            $addonStatus = 0;
            $addonCheck = StoreCartItemAddon::where('store_cart_id', $cartId)->count();
            if ($addonCheck == 0 && count($checkAddons) == 0) {
                $cart = $cartId;
                break;
            }

            if ($addonCheck == count($checkAddons)) {

                foreach ($checkAddons as $checkAddon) {

                    $add = StoreCartItemAddon::where('store_cart_id', $cartId)->where('store_item_addons_id', $checkAddon)->first();

                    if ($add) {
                        $totalAddons[] = $checkAddon;
                        $addonStatus++;
                    } else {
                        $addonStatus--;
                    }
                    if ($addonStatus == count($checkAddons)) {
                        $status = false;
                        break;
                    }
                }

                if ($addonStatus == count($checkAddons)) {
                    $cart = $cartId;
                    break;
                }
            }
        }

        if ($request->repeat) {
            $status = false;
        }

        if ($request->customize) {
            $addons = StoreCartItemAddon::where('store_cart_id', $request->cart_id)->pluck('store_item_addons_id')->toArray();
            if (count($addons) != count($checkAddons)) {
                $status = true;
            } else {
                foreach ($addons as $addon) {
                    if (!in_array($addon, $checkAddons)) {
                        $status = true;
                    }
                }
            }
        }

        $response = [
            'cart' => $cart,
            'addonStatus' => $status,
            'totalAddons' => $totalAddons
        ];
        return $response;
    }

    public function viewcart(Request $request)
    {

        try {
            $CartItems  = StoreCart::with('product', 'product.itemsaddon', 'product.itemsaddon.addon', 'store', 'store.storetype', 'store.StoreCusinie', 'store.StoreCusinie.cuisine', 'cartaddon', 'cartaddon.addon.addon')
                ->where('company_id', $this->company_id)
                ->where('user_id', $this->user->id)->get();

            $tot_price = 0;
            $discount = 0;
            $tax  = 0;
            $promocode_amount = 0;
            $total_net = 0;
            $total_wallet_balance = 0;
            $payable = 0;
            $discount_promo = 0;
            $delivery_charges = 0;
            $userRatio = 0;
            $cusines_list = [];
            $distance = 0;

            $userCount = User::where(['status' => 1, 'is_deleted' => 0])->count();
            $providerCount = Provider::where(['providers.activation_status' => 1, 'providers.is_deleted' => 0])->whereHas('providerservice', function ($q) {
                $q->where('admin_service', '=', 'ORDER');
            })->count();
            $onlineuserCount = User::where(['is_online' => 1, 'status' => 1, 'is_deleted' => 0])->count();
            $onlineproviderCount = Provider::where(['providers.is_online' => 1, 'providers.activation_status' => 1, 'providers.is_deleted' => 0])->whereHas('providerservice', function ($q) {
                $q->where('admin_service', '=', 'ORDER');
            })->count();

            $totalUserProvider = $userCount + $providerCount;
            $totalonlineUserProvider = $onlineuserCount + $onlineproviderCount;
            $userRatio = ($totalonlineUserProvider / $totalUserProvider) * 100;

            if (!$CartItems->isEmpty()) {
                if ($CartItems[0]->store->StoreCusinie->count() > 0) {
                    foreach ($CartItems[0]->store->StoreCusinie as $cusine) {
                        $cusines_list[] = $cusine->cuisine->name;
                    }
                }
                $store_type_id = $CartItems[0]->store->store_type_id;
                $store_id = $CartItems[0]->store->id;
                $city_id = $CartItems[0]->store->city_id;
                $cityprice = StoreCityPrice::where('store_type_id', $store_type_id)->where('company_id', $this->company_id)
                    ->where('city_id', $city_id)
                    ->first();

                if (empty($cityprice)) {
                    Helper::getResponse(['status' => 400, 'message' => 'Price not found for this city', 'error' => 'Price not found for this city']);
                }

                foreach ($CartItems as $Product) {
                    $tot_qty = $Product->quantity;
                    //$Product->quantity. '--' .$Product->product->item_price;
                    // if($Product->product->item_discount_type=="PERCENTAGE"){
                    //      $Product->product->item_price= $Product->product->item_price-(($Product->product->item_discount/100)*$Product->product->item_price);
                    // } else if($Product->product->item_discount_type=="AMOUNT"){
                    //      $Product->product->item_price= $Product->product->item_price-$Product->product->item_discount;
                    // }
                    $Product->product->item_price = $Product->item_price > 0 ? $Product->item_price : 0;
                    $tot_price += $Product->quantity * $Product->item_price;
                    $tot_price_addons = 0;

                    if (count($Product->cartaddon) > 0) {
                        foreach ($Product->cartaddon as $Cartaddon) {

                            $tot_price_addons += $Cartaddon->addon_price;
                        }
                    }
                    $tot_price += $tot_qty * $tot_price_addons;
                }
                //dd($tot_price);
                $tot_price = $tot_price;
                if ($tot_price < 10 && $cityprice) {
                    $small_order_fee = (float)$cityprice->small_order_fee;
                    if (empty($small_order_fee)) {
                        $small_order_fee = 0;
                    }
                } else {
                    $small_order_fee = 0;
                }
                $net = (float)number_format((float)$tot_price, 2, '.', '');
                if(!empty($cityprice->service_fee)){
                    $store_tax = ($net * $cityprice->service_fee / 100); 
                }else{
                    $store_tax = 0;
                }
               // $store_tax = ($net * $cityprice->service_fee / 100); //($net*$Product->store->store_gst/100);
                if ($Product->store->offer_percent) {
                    if ($tot_price > $Product->store->offer_min_amount) {
                        //$discount = roundPrice(($tot_price*($Order->shop->offer_percent/100)));
                        $discount = ($tot_price * ($Product->store->offer_percent / 100));
                        //if()
                        $net = $tot_price - $discount;
                    }
                }
                $total_wallet_balance = 0;

                $store_package_charge = $Product->store->store_packing_charges;
                // $free_delivery = 0;
                // old logic of delivery charge
                // if($Product->store->free_delivery==1){
                //  $free_delivery = 0;
                // }else{
                //     if($cityprice){
                //     $free_delivery = $cityprice->delivery_charge;
                //     }else{
                //         $free_delivery = 0;
                //     }
                // }


                //New logic of Delivery Charge

                if (!empty($store_id)) {
                
                    if (!empty($request->user_address_id)) {
                        $charges = Order::getDeliveryCharge($cityprice, $this->user->id, $store_id, $this->settings->site->browser_key, $request, 1);
                    } else {
                        $charges = Order::getDeliveryCharge($cityprice, $this->user->id, $store_id, $this->settings->site->browser_key, $request);
                    }

                    if ($charges['responseCode'] == 200) {
                        $delivery_charges = $charges['responseData'];
                        if(!empty($charges['distance'])){
                            $distance = $charges['distance'];
                        }else{
                            $distance = 0;
                        }
                        
                    } else {
                        Helper::getResponse(['status' => 400, 'message' => $charges['responseMessage'], 'error' => $charges['responseMessage']]);
                    }
                } else {
                    $delivery_charges = 0;
                    $distance = 0;
                }

                $total_net = ($net + $store_tax + $delivery_charges + $store_package_charge + $small_order_fee);

                //Calculate peak price
                $peakprice = 0;
                $peak_percent = 0;
                if (($userRatio > 0) && ($userRatio <= 14)) {
                    $peakprice = ($total_net * 1.3) / 100;
                    $peak_percent = 1.3;
                } else if (($userRatio >= 15) && ($userRatio < 35)) {
                    $peakprice = ($total_net * 1.7) / 100;
                    $peak_percent = 1.7;
                } else if ($userRatio >= 35) {
                    $peakprice = ($total_net * 2.3) / 100;
                    $peak_percent = 2.3;
                }

                // \Log::info("***************Peak price for Order********************");
                // \Log::info("***************".time()."********************");
                // \Log::info("totalUserProvider ==".$totalUserProvider);
                // \Log::info("totalonlineUserProvider ==".$totalonlineUserProvider);
                // \Log::info('$userRatio = ($totalonlineUserProvider / $totalUserProvider) * 100');
                // \Log::info('$userRatio ='.$userRatio);
                // \Log::info('$peak_price ='.$peakprice);
                // \Log::info('$peak_percent ='.$peak_percent);
                // \Log::info('$service_fee ='.$cityprice->service_fee);
                // \Log::info("***************End of peak price for Order********************");
                
                $total_net += $peakprice;
                $promocode_id = 0;
                $discount_promo = 0;
                if ($request->has('promocode_id') && $request->promocode_id != '') {
                    $find_promo = Promocode::where('id', $request->promocode_id)->first();
                    if ($find_promo != null) {
                        $promocode_id = $find_promo->id;
                        $my_promo_discount = Helper::decimalRoundOff($total_net * ($find_promo->percentage / 100));
                        if ($my_promo_discount > $find_promo->max_amount) {
                            $discount_promo = Helper::decimalRoundOff($find_promo->max_amount);
                            $total_net = $total_net - $find_promo->max_amount;
                        } else {
                            $discount_promo = Helper::decimalRoundOff($my_promo_discount);
                            $total_net = $total_net - $my_promo_discount;
                        }
                    }
                }
                $total_net = $payable = $total_net;

                if ($request->wallet && $request->wallet != "" && $request->wallet == 1) {
                    if (Auth::guard('user')->user()->wallet_balance > $total_net) {
                        $total_wallet_balance_left = Auth::guard('user')->user()->wallet_balance - $total_net;

                        $total_wallet_balance = $total_net;
                        $payable = 0;
                    } else {
                        //$total_net = $total_net - $request->user()->wallet_balance;
                        $total_wallet_balance = Auth::guard('user')->user()->wallet_balance;
                        if ($total_wallet_balance > 0) {
                            $payable = ($total_net - $total_wallet_balance);
                        }
                    }
                }

                //print($CartItems);exit;
                $CartItems->map(function ($data) {

                    if (count($data->product->itemsaddon) > 0) {
                        $data->product->itemsaddon->filter(function ($itmad) {
                            $itmad->addon_name = $itmad->addon->addon_name;
                            unset($itmad->addon);
                            return $itmad;
                        });
                    }

                    if (count($data->cartaddon) > 0) {
                        $data->cartaddon->filter(function ($da) {
                            $da->addon_name = $da->addon->addon->addon_name;
                            unset($da->addon);
                            return $da;
                        });
                    }
                    return $data;
                });

                $gross = $payable - $delivery_charges;
                if(!empty($cityprice->commission_fee)){
                    $store_commission =  100 - $cityprice->commission_fee;
                    $gride_commission = ($gross * $cityprice->commission_fee) / 100;
                }else{
                    $store_commission =  0;
                    $gride_commission = 0;
                }
               
               // $gross = $payable - $delivery_charges;
               // $gride_commission = ($gross * $cityprice->commission_fee) / 100;
                if(!empty($cityprice->service_fee)){
                    $service_fee = $cityprice->service_fee;
                }else{
                    $service_fee = 0;
                }
                $Cart = [
                    'delivery_charges' => (float)number_format((float)$delivery_charges, 2, '.', ''), //$free_delivery,
                    'delivery_free_minimum' => 0,
                    'gross' => (float)number_format((float)$gross, 2, '.', ''),
                    'tax_percentage' => 0,
                    'carts' => $CartItems,
                    'total_price' => (float)number_format((float)$total_net, 2, '.', ''), //round($total_net),
                    'shop_discount' => (float)number_format((float)$discount, 2, '.', ''), //round($discount,2),                
                    'store_type' => $CartItems[0]->store->storetype->category,
                    'total_item_price' => (float)number_format((float)$tot_price, 2, '.', ''),
                    //'tax' => round($tax,2),
                    'promocode_id' => $promocode_id,
                    'promocode_amount' => (float)number_format((float)$discount_promo, 2, '.', ''), //round($discount_promo,2),
                    'net' => (float)number_format((float)$total_net, 2, '.', ''), //round($total_net,2),
                    'wallet_balance' => $total_wallet_balance, //round($total_wallet_balance,2),
                    'payable' => (float)number_format((float)$payable, 2, '.', ''), //round($payable),
                    'total_cart' => count($CartItems),
                    'shop_gst' => (float)number_format((float)$service_fee, 2, '.', ''), //$CartItems[0]->store->store_gst,
                    'shop_gst_amount' => (float)number_format((float)$store_tax, 2, '.', ''), //round($store_tax,2),
                    'shop_package_charge' =>  (float)number_format((float)$store_package_charge, 2, '.', ''),
                    'store_id' => $CartItems[0]->store->id,
                    'store_commision_per' => (float)number_format((float)$store_commission, 2, '.', ''), //$CartItems[0]->store->commission,
                    'shop_cusines' => implode(',', $cusines_list),
                    'rating' => ($CartItems[0]->store->rating) ? $CartItems[0]->store->rating : 0.00,
                    'user_wallet_balance' => Auth::guard('user')->user()->wallet_balance,
                    'user_currency' => Auth::guard('user')->user()->currency_symbol,
                    'subtotal' => (float)number_format((float)$tot_price, 2, '.', ''),
                    'small_order_fee' => (float)number_format((float)$small_order_fee, 2, '.', ''),
                    'gride_commission_per' => (float)number_format((float)$gride_commission, 2, '.', ''),
                    'gride_commission_amount' => (float)number_format((float)$gride_commission, 2, '.', ''),
                    'distance' => $distance,
                    'peak_price' => (float)number_format((float)$peakprice, 2, '.', ''),
                    'peak_percent' => $peak_percent,
                    'user_ratio' => $userRatio,
                ];
            } else {

                $Cart = [
                    'delivery_charges' => 0,
                    'delivery_free_minimum' => 0,
                    'gross' => 0,
                    'tax_percentage' => 0,
                    'carts' => [],
                    'total_price' => $tot_price, //round($tot_price,2),
                    'shop_discount' => $discount, //round($discount,2),                
                    'store_type' => '',
                    'total_item_price' => (float)number_format((float)$tot_price, 2, '.', ''),
                    //'tax' => round($tax,2),
                    'promocode_amount' => $promocode_amount, //round($promocode_amount,2),
                    'net' => $total_net, //round($total_net,2),
                    'wallet_balance' => $total_wallet_balance, //round($total_wallet_balance,2),
                    'payable' => $payable, //round($payable),
                    'total_cart' => count($CartItems),
                    'shop_gst' => 0,
                    'shop_gst_amount' => 0.00,
                    'shop_package_charge' =>  0,
                    'store_id' => 0,
                    'store_commision_per' => 0,
                    'total_cart' => count($CartItems),
                    'shop_cusines' => '',
                    'rating' => 0.00,
                    'user_wallet_balance' => Auth::guard('user')->user()->wallet_balance,
                    'user_currency' => Auth::guard('user')->user()->currency_symbol,
                    'subtotal' => $tot_price, //round($tot_price,2),
                    'small_order_fee' => 0,
                    'gride_commission_per' => 0,
                    'gride_commission_amount' => 0,
                    'distance' => 0,
                    'peak_price' => 0,
                    'peak_percent' => 0,
                    'user_ratio' => 0,
                ];
            }

            if ($request->has('user_address_id') && $request->user_address_id != '' && empty($request->is_change_address)) {
                return $Cart;
            } else {
                return Helper::getResponse(['data' => $Cart]);
            }


            return Helper::getResponse(['data' => $Cart]);
        } catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.provider.provider_not_found'), 'error' => trans('api.provider.provider_not_found')]);
        } catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.provider.provider_not_found'), 'error' => trans('api.provider.provider_not_found')]);
        }
    }

    public function removecart(Request $request)
    {
        $this->validate($request, [
            'cart_id'    => 'required'
        ]);

        $cart = StoreCart::where(['id' => $request->cart_id])->first();
        // $cart = StoreCart::find($request->cart_id)->delete();
        if (!empty($cart)) {
            $cart->delete();
            $cart_addon = StoreCartItemAddon::where('store_cart_id', $request->cart_id)->delete();
            return $this->viewcart($request);
        } else {
            return Helper::getResponse(['status' => 500, 'message' => 'Cart not found', 'error' => 'Cart not found']);
        }
    }

    public function totalremovecart(Request $request)
    {
        $cart = StoreCart::where('user_id', Auth::guard('user')->user()->id)->delete();
        return Helper::getResponse(['data' => $cart]);
    }

    public function totalusercart()
    {

        $user = Auth::guard('user')->user();

        $company_id = $user ? $user->company_id : 1;

        $CartItems = StoreCart::select('total_item_price')->where('company_id', $company_id)->where('user_id', $user ? $user->id : null)->get();
        return $CartItems;
    }


    public function promocodelist(Request $request)
    {

        $Promocodes = Promocode::with('promousage')
            ->where('status', 'ADDED')
            ->where('service', 'ORDER')
            ->where('company_id', $this->company_id)
            ->where('expiration', '>=', date("Y-m-d H:i"))
            ->whereDoesntHave('promousage', function ($query) {
                $query->where('user_id', $this->user->id);
            })
            ->get();
        return Helper::getResponse(['data' => $Promocodes]);
    }

    public function orderTip(Request $request)
    {
        $this->validate($request, [
            'request_id'    => 'required',
            'tip' => 'required',
            'card_id' => 'required',
            'payment_mode' => 'required'
        ]);

        $tip = $request->tip;

        $order = StoreOrder::find($request->request_id);

        if($order){
            $orderInvoice = StoreOrderInvoice::where('store_order_id', $request->request_id)->firstOrFail(); 

            if($orderInvoice->tip==0){
                $request->request->add(['company_id' => Auth::guard('user')->user()->company_id]);
                $request->request->add(['user_id' => Auth::guard('user')->user()->id]);
                $payment_id = Order::orderTipPayment($tip, $request);
                if ($payment_id['responseCode'] == 400) {
                    return Helper::getResponse(['status' => $payment_id['responseCode'], 'message' => $payment_id['responseMessage']]); //trans('Transaction Failed')
                }

                if(is_array($payment_id)){
                    $orderinvoice->tip_payment_id = $payment_id['responseMessage'];
                }
                
                $orderInvoice->tip =  $tip;
                $orderInvoice->subtotal =  $orderInvoice->subtotal + $tip;
                $orderInvoice->total_amount = $orderInvoice->total_amount + $tip;
                $orderInvoice->cash =  $orderInvoice->cash + $tip;
                $orderInvoice->payable =  $orderInvoice->payable + $tip;
                $orderInvoice->save();
            }else{
                return Helper::getResponse(['status' => 422, 'message' => 'Tip Already Paid', 'error' => 'tip already paid']);
            }


                return Helper::getResponse(['status' => 200, 'message' => 'Tip Successfully Paid.']);
            
        }else{
            return Helper::getResponse(['status' => 404, 'message' => 'Order Not Found', 'error' => 'order not found']);
        }

        
    }

    public function checkout(Request $request)
    {
        Log::info('checkout api request params');
        Log::info(json_encode($request->all()));
        $messages = [
            'user_address_id.required' => trans('validation.custom.user_address_id_required')
        ];
        $this->validate($request, [
            'payment_mode'    => 'required',
            'user_address_id' => 'required|exists:user_addresses,id,deleted_at,NULL',
        ], $messages);
        $cart =  $this->viewcart($request);
        if (empty($cart['carts'])) {
            return Helper::getResponse(['status' => 404, 'message' => 'user cart is empty', 'error' => 'user cart is empty']);
        }

        // $ActiveRequests = StoreOrder::PendingRequest($this->user->id, $cart['store_id'])->count();
        // if ($ActiveRequests > 0) {
        //     return ['status' => 422, 'message' => trans('api.order.request_inprogress')];
        // }


        $store_details = Store::with('storetype')
            ->whereHas('storetype', function ($q) use ($request) {
                $q->where('status', 1);
            })
            ->select('id', 'picture', 'contact_number', 'store_type_id', 'latitude', 'longitude', 'store_location', 'store_name', 'currency_symbol')->find($cart['store_id']);
        $address_details = UserAddress::select('id', 'latitude', 'longitude', 'map_address', 'flat_no', 'street')->find($request->user_address_id);
        $payment_id = '';
        $paymentMode = $request->payment_mode;

        $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $address_details->latitude . "," . $address_details->longitude . "&destination=" . $store_details->latitude . "," . $store_details->longitude . "&mode=driving&key=" . $this->settings->site->browser_key;
        $json = Helper::curl($details);
        $details = json_decode($json, TRUE);
        $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';
        // $distancemeter = (count($details['routes'])> 0) ? $details['routes'][0]['legs'][0]['distance']['value']:0;
        // $distance = abs(round($distancemeter/1609,2));

        // // $delivery_charges = 0;        
        // if(($distance >= 0)&&($distance <= 4)){
        //     $delivery_charges = 4;
        // } else if(($distance > 4)&&($distance <= 9)){
        //     $delivery_charges = 8;
        // } else if(($distance > 9)&&($distance <= 15)){
        //     $delivery_charges = 13;
        // } else if(($distance > 15)&&($distance <= 24)){
        //     $delivery_charges = 16;
        // } else if($distance > 24){
        //     $delivery_charges = 25;
        // }             

        // $cart['total_price'] += $delivery_charges;
        // $cart['net'] += $delivery_charges;
        // $cart['payable'] += $delivery_charges;

        if ($request->payment_mode == 'CARD') {
            $payable = $cart['payable'];
            if ($payable != 0) {
                $request->request->add(['company_id' => Auth::guard('user')->user()->company_id]);
                $request->request->add(['user_id' => Auth::guard('user')->user()->id]);
                $payment_id = Order::orderpayment($payable, $request);
                // if($payment_id=='failed'){
                if ($payment_id['responseCode'] == 400) {
                    return Helper::getResponse(['status' => $payment_id['responseCode'], 'message' => $payment_id['responseMessage']]); //trans('Transaction Failed')
                }
                // return Helper::getResponse(['message' => trans('Transaction Failed')]);
                // }  
            }
        }
        if ($request->payment_mode == 'BRAINTREE') {
            $payable = $cart['payable'];
            if ($payable != 0) {
                $request->request->add(['company_id' => Auth::guard('user')->user()->company_id]);
                $request->request->add(['user_id' => Auth::guard('user')->user()->id]);
                $payment_id = Order::orderpayment($payable, $request);
                // if($payment_id=='failed'){
                if ($payment_id['responseCode'] == 400) {
                    return Helper::getResponse(['status' => $payment_id['responseCode'], 'message' => $payment_id['responseMessage']]); //trans('Transaction Failed')
                }
                // return Helper::getResponse(['message' => trans('Transaction Failed')]);
                // }  
            }
        }

        //return $request->all();
        try {
            $order = new StoreOrder();
            $order->description = isset($request->description) ? $request->description : '';
            $bookingprefix = $this->settings->order->booking_prefix;
            $order->store_order_invoice_id = $bookingprefix . time() . rand('0', '999');
            if (!empty($payment_id)) {
                $order->paid = 1;
            }
            $order->user_id = $this->user->id;
            $order->user_address_id = $request->user_address_id;
            $order->assigned_at = (Carbon::now())->toDateTimeString();
            $order->order_type = $request->order_type;
            if ($this->settings->order->manual_request == 1) {
                $order->request_type = 'MANUAL';
            }

            $order->order_otp = mt_rand(1000, 9999);
            // echo'<pre>'; print_r($request->payment_mode);
            // echo'<pre>'; print_r($request->order_type);
            // exit;
            if (($request->payment_mode != 'CASH') && ($request->order_type == 'DELIVERY')) {
                if (!empty($request->leave_at_door)) {
                    $order->leave_at_door =  $request->leave_at_door;
                } else {
                    $order->leave_at_door = 0;
                }
            } else {
                $order->leave_at_door = 0;
            }

            $order->timezone = (Auth::guard('user')->user()->state_id) ? State::find(Auth::guard('user')->user()->state_id)->timezone : '';
            $order->route_key = $route_key;
            $order->city_id = Auth::guard('user')->user()->city_id;
            $order->country_id = Auth::guard('user')->user()->country_id;
            $order->promocode_id = !empty($cart['promocode_id']) ? $cart['promocode_id'] : 0;
            if ($request->has('delivery_date') && $request->delivery_date != '') {
                $order->delivery_date = Carbon::parse($request->delivery_date)->format('Y-m-d H:i:s');
                $order->schedule_status = 1;
            }
            $order->store_id = $cart['store_id'];
            $order->store_type_id = $store_details->store_type_id;
            $order->admin_service = 'ORDER';
            $order->order_ready_status = 0;
            $order->company_id = $this->company_id;
            $order->currency = Auth::guard('user')->user()->currency_symbol;
            $order->status = 'ORDERED';
            $order->delivery_address = json_encode($address_details);
            $order->distance = $cart['distance'];;
            $order->pickup_address = json_encode($store_details);
            $order->save();
            if ($order->id) {
                $store_commision_amount = ($cart['gross'] * ($cart['store_commision_per'] / 100));
                $orderinvoice = new StoreOrderInvoice();
                $orderinvoice->store_order_id = $order->id;
                $orderinvoice->tip = $request->tip;
                $orderinvoice->store_id = $order->store_id;
                $orderinvoice->payment_mode = $request->payment_mode;
                if(is_array($payment_id)){
                    $orderinvoice->payment_id = $payment_id['responseMessage'];
                }
                $orderinvoice->company_id = $this->company_id;
                $orderinvoice->item_price = (float)number_format((float)$cart['total_item_price'], 2, '.', '');
                $orderinvoice->gross = $cart['gross'];
                $orderinvoice->net = $cart['net'];
                $orderinvoice->discount = $cart['shop_discount'];
                $orderinvoice->promocode_id = $cart['promocode_id'];
                $orderinvoice->promocode_amount = $cart['promocode_amount'];
                $orderinvoice->wallet_amount = $cart['wallet_balance'];
                $orderinvoice->tax_per = $cart['shop_gst'];
                $orderinvoice->tax_amount = $cart['shop_gst_amount'];
                $orderinvoice->commision_per = $cart['store_commision_per'];
                $orderinvoice->commision_amount = $store_commision_amount;
                /*$orderinvoice->delivery_per = $cart['total_price'];*/
                $orderinvoice->delivery_amount = $cart['delivery_charges'];
                $orderinvoice->store_package_amount = $cart['shop_package_charge'];
                $orderinvoice->total_amount = $cart['total_price']+$request->tip;
                $orderinvoice->cash = $cart['payable']+$request->tip;
                $orderinvoice->payable = $cart['payable']+$request->tip;
                $orderinvoice->subtotal = $cart['subtotal']+$request->tip;
                $orderinvoice->small_order_fee = $cart['small_order_fee'];
                $orderinvoice->gride_commission_per = $cart['gride_commission_per'];
                $orderinvoice->gride_commission_amount = $cart['gride_commission_amount'];
                $orderinvoice->peak_amount = $cart['peak_price'];
                $orderinvoice->peak_percent = $cart['peak_percent'];
                $orderinvoice->user_ratio = $cart['user_ratio'];
                $orderinvoice->status = 0;
                $orderinvoice->cart_details = json_encode($cart['carts']);
                $orderinvoice->save();
                $orderstatus = new StoreOrderStatus();
                $orderstatus->company_id = $this->company_id;
                $orderstatus->store_order_id = $order->id;
                $orderstatus->store_order_id = $order->id;
                $orderstatus->status = 'ORDERED';
                $orderstatus->save();

                //payment log update order id
                if (is_array($payment_id) && $payment_id['responseCode'] == 200) {
                    $log = PaymentLog::where('transaction_id', $payment_id['responseMessage'])->first();
                    $log->transaction_id = $order->id;
                    $log->transaction_code = $order->store_order_invoice_id;
                    $log->response = json_encode($order);
                    $log->save();
                }
                //$User = User::find($this->user->id);
                $Wallet = Auth::guard('user')->user()->wallet_balance;
                //$Total = 
                //
                if ($cart['wallet_balance'] > 0) {
                    // charged wallet money push 
                    // (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,$Wallet, 'wallet');
                    (new SendPushNotification)->ChargedWalletMoney($this->user->id, Helper::currencyFormat($cart['wallet_balance'], Auth::guard('user')->user()->currency_symbol), 'wallet', 'Wallet Info');

                    $transaction['amount'] = $cart['wallet_balance'];
                    $transaction['id'] = $this->user->id;
                    $transaction['transaction_id'] = $order->id;
                    $transaction['transaction_alias'] = $order->store_order_invoice_id;
                    $transaction['company_id'] = $this->company_id;
                    $transaction['transaction_msg'] = 'order deduction';
                    $transaction['admin_service'] = $order->admin_service;
                    $transaction['country_id'] = $order->country_id;

                    (new Transactions)->userCreditDebit($transaction, 0);
                }
                //user request
                $user_request = new UserRequest();
                $user_request->company_id = $this->company_id;
                $user_request->user_id = $this->user->id;
                $user_request->request_id = $order->id;
                $user_request->request_data = json_encode(StoreOrder::with('invoice', 'store.storetype')->where('id', $order->id)->first());
                $user_request->admin_service = 'ORDER';
                $user_request->status = 'ORDERED';
                $user_request->save();

                $CartItem_ids  = StoreCart::where('company_id', $this->company_id)->where('user_id', $this->user->id)->pluck('id', 'id')->toArray();
                $CartItems  = StoreCart::where('company_id', $this->company_id)->where('user_id', $this->user->id)->delete();
                StoreCartItemAddon::whereIN('store_cart_id', $CartItem_ids)->delete();

                if ($request->has('delivery_date') && $request->delivery_date != '') {
                    // scheduling
                    $schedule_status = 1;
                } else {
                    //Send message to socket
                    $requestData = ['type' => 'ORDER', 'room' => 'room_' . $this->company_id, 'id' => $order->id, 'city' => ($this->settings->demo_mode == 0) ? $order->store->city_id : 0, 'user' => $order->user_id];
                    app('redis')->publish('checkOrderRequest', json_encode($requestData));
                }


                //Send message to socket
                $requestData = ['type' => 'ORDER', 'room' => 'room_' . $this->company_id, 'id' => $order->id, 'shop' => $cart['store_id'], 'user' => $order->user_id];
                app('redis')->publish('newRequest', json_encode($requestData));

                (new SendPushNotification)->ShopRequest($order->store_id, $order->admin_service);
                $finalres = $this->orderdetails($order->id);
                Log::info('Coming to the last step upto order details');
                Log::info(json_encode($finalres));
                return $finalres;
            }
        } catch (\Exception $e) {
            Log::info('Last step try catch exception: ' . $e->getMessage());
            return Helper::getResponse(['status' => 400, 'error' => $e->getMessage()]);
        }
    }

    public function getClientToken()
    {
    

      // Initialize Braintree with the configuration
    //   Braintree\Configuration::environment('sandbox');
    //   Braintree\Configuration::merchantId('q4g7z2v795m8mh82');
    //   Braintree\Configuration::publicKey('6vwbhcy9v8sc8yc4');
    //   Braintree\Configuration::privateKey('b475455b92b08a17eb18faf11b27e10a');
      
      Configuration::environment(env('BRAINTREE_ENV'));
      Configuration::merchantId(env('BRAINTREE_merchantId'));
      Configuration::publicKey(env('BRAINTREE_publicKey'));
      Configuration::privateKey(env('BRAINTREE_privateKey'));

      // Generate client token
      $clientToken = ClientToken::generate();
      if($clientToken){
      return Helper::getResponse(['status' => 200, 'data' => $clientToken]);
      }
      else{
        return Helper::getResponse(['status' => 500, 'message' => 'token not created']);
      }
    } 
    public function cancelOrder(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:order.store_orders,id,user_id,' . $this->user->id,
            'cancel_reason' => 'required|max:255',
        ]);

        $request->request->add(['cancelled_by' => 'USER']);
        $request->request->add(['user_id' => Auth::guard('user')->user()->id]);
        $request->request->add(['company_id' => Auth::guard('user')->user()->company_id]);

        try {
            $order = (new Order())->cancelOrder($request);
            // $order['message']
            return Helper::getResponse(['status' => $order['status'], 'message' =>  $order['message']]);
        } catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }
    }
    
    public function orderdetailsPopularProducts($id)
    {
        $order = StoreOrder::with([
            'store', 'store.storetype', 'deliveryaddress', 'invoice', 'user', 'chat',
            'provider' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'country_code', 'mobile', 'rating', 'latitude', 'longitude', 'picture');
            },
        ])->whereHas('store.storetype', function ($q) {
            $q->where('status', 1);
        })->find($id);

        return Helper::getResponse(['data' => $order]);
    }
    public function orderdetails($id)
    {
        $order = StoreOrder::with([
            'store', 'store.storetype', 'deliveryaddress', 'invoice', 'user', 'chat',
            'provider' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'country_code', 'mobile', 'rating', 'latitude', 'longitude', 'picture');
            },
        ])->whereHas('store.storetype', function ($q) {
            $q->where('status', 1);
        })->find($id);

        return Helper::getResponse(['data' => $order]);
    }

    //status check request
    public function status(Request $request)
    {
        try {
            $check_status = ['CANCELLED', 'COMPLETED', 'STORECANCELLED','PROVIDEREJECTED'];
            $admin_service = AdminService::where('admin_service', 'ORDER')->where('company_id', $this->company_id)->first();

            $orderRequest = StoreOrder::OrderRequestStatusCheck($this->user->id, $check_status, $admin_service->id)
                ->get()
                ->toArray();
            $search_status = ['SEARCHING', 'SCHEDULED'];
            $Timeout = $this->settings->order->provider_select_timeout ? $this->settings->order->provider_select_timeout : 60;
            $response_time = $Timeout;

            return Helper::getResponse(['data' => [
                'response_time' => $response_time,
                'data' => $orderRequest,
                'sos' => isset($this->settings->site->sos_number) ? $this->settings->site->sos_number : '911',
                'emergency' => isset($this->settings->site->contact_number) ? $this->settings->site->contact_number : [['number' => '911']]
            ]]);
        } catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function orderdetailsRating(Request $request)
    {
        
        $this->validate($request, [
            'request_id' => 'required|integer|exists:order.store_orders,id,user_id,' . $this->user->id,
            'shopid' => 'required|integer|exists:order.stores,id',
            'rating' => 'required|integer|in:1,2,3,4,5',
            'shoprating' => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
        ], ['comment.max' => 'character limit should not exceed 255']);


        try {

            $orderRequest = StoreOrder::where('id', $request->request_id)->where('status', 'COMPLETED')->firstOrFail();
            $data = (new UserServices())->rate($request, $orderRequest);
            return Helper::getResponse(['status' => isset($data['status']) ? $data['status'] : 200, 'message' => isset($data['message']) ? $data['message'] : '', 'error' => isset($data['error']) ? $data['error'] : '']);
        } catch (\Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.order.request_not_completed') . $e->getMessage(), 'error' => trans('api.order.request_not_completed')]);
        }
    }

    public function tripsList(Request $request)
    {
        try {
            $jsonResponse = [];
            $jsonResponse['type'] = 'order';
            $withCallback = [
                'user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'rating', 'picture', 'currency_symbol');
                },
                'provider' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'rating', 'picture', 'mobile');
                },
                'rating' => function ($query) {
                    $query->select('request_id', 'user_rating', 'provider_rating', 'user_comment', 'provider_comment', 'store_comment', 'store_rating');
                },
                'invoice'
            ];

            $userrequest = StoreOrder::select('store_orders.*', DB::raw('(select total_amount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as total_amount'), DB::raw('(select payment_mode from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as payment_mode'), 'user_rated', 'provider_rated');
            $data = (new UserServices())->userHistory($request, $userrequest, $withCallback);
            // dd($data);
            $jsonResponse['total_records'] = count($data);
            $jsonResponse['order'] = $data;
            return Helper::getResponse(['data' => $jsonResponse]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function getOrderHistorydetails(Request $request, $id)
    {
        try {
            $jsonResponse = [];
            $jsonResponse['type'] = 'order';
            $request->request->add(['admin_service' => 'ORDER', 'id' => $id]);
            $userrequest = StoreOrder::with(array(
                "store.storetype", 'orderInvoice' => function ($query) {
                    $query->select('id', 'store_order_id', 'gross', 'wallet_amount', 'total_amount', 'payment_mode', 'tax_amount', 'delivery_amount', 'promocode_amount', 'payable', 'cart_details', 'commision_amount', 'store_package_amount', 'cash', 'discount', 'item_price');
                }, 'user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'rating', 'picture', 'mobile', 'currency_symbol');
                },
                'provider' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'rating', 'picture', 'mobile');
                }, "dispute" => function ($query) {
                    $query->where('dispute_type', 'user');
                },
            ))->whereHas('store.storetype', function ($q) {
                $q->where('status', 1);
            })
                ->select('id', 'store_id', 'store_order_invoice_id', 'user_id', 'provider_id', 'admin_service', 'company_id', 'pickup_address', 'delivery_address', 'created_at', 'status', 'timezone');
            $data = (new UserServices())->userTripsDetails($request, $userrequest);
            $jsonResponse['order'] = $data;
            return Helper::getResponse(['data' => $jsonResponse]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }


    public function requestHistory(Request $request)
    {
        try {
            $history_status = array('CANCELLED', 'COMPLETED');
            $datum = StoreOrder::select('store_orders.*', DB::raw('(select total_amount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as total_amount'), DB::raw('(select discount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as discount'), DB::raw('(select payment_mode from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as payment_mode'))
                ->where('company_id', Auth::user()->company_id)
                ->whereIn('status', $history_status)
                ->with('user', 'provider');
            /*if(Auth::user()->hasRole('FLEET')) {
                $datum->where('admin_id', Auth::user()->id);  
            }*/
            if ($request->has('search_text') && $request->search_text != null) {
                $datum->Search($request->search_text);
            }

            $data = $datum->orderby('id', 'desc')->paginate(10);
            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function shoprequestHistory(Request $request)
    {
        try {
            $history_status = array('CANCELLED', 'COMPLETED');
            $datum = StoreOrder::select('store_orders.*', DB::raw('(select total_amount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as total_amount'), DB::raw('(select discount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as discount'), DB::raw('(select payment_mode from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as payment_mode'))
                ->where('company_id', Auth::guard('shop')->user()->company_id)->where('store_id', Auth::guard('shop')->user()->id)
                ->with('user', 'provider');

            if ($request->has('search_text') && $request->search_text != null) {
                $datum->Search($request->search_text);
            }
            if ($request->has('limit')) {
                $data = $datum->where("status", $request->type)->paginate($request->limit);
            } else {
                $data = $datum->whereIn('status', $history_status)->orderby('id', 'desc')->paginate(10);
            }

            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }




    public function requestScheduleHistory(Request $request)
    {
        try {
            $scheduled_status = array('SCHEDULED');
            $datum = StoreOrder::select('store_orders.*', DB::raw('(select total_amount from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as total_amount'), DB::raw('(select payment_mode from store_order_invoices where store_orders.id=store_order_invoices.store_order_id) as payment_mode'))
                ->where('company_id', $this->company_id)
                ->where('schedule_status', 1)
                ->with('user', 'provider');
            /*if(Auth::user()->hasRole('FLEET')) {
                $datum->where('admin_id', Auth::user()->id);  
            }*/
            if ($request->has('search_text') && $request->search_text != null) {
                $datum->Search($request->search_text);
            }
            if ($request->has('order_by')) {
                $datum->orderby($request->order_by, $request->order_direction);
            }
            $data = $datum->paginate(10);
            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function requestStatementHistory(Request $request)
    {
        try {
            $history_status = array('CANCELLED', 'COMPLETED');
            $orderRequests = StoreOrder::select('*', 'created_at as joined')->where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0])
                ->with('user', 'provider');
            if ($request->has('country_id')) {
                $orderRequests->where('country_id', $request->country_id);
            }
            if (Auth::user()->hasRole('FLEET')) {
                $orderRequests->where('admin_id', Auth::user()->id);
            }
            if ($request->has('search_text') && $request->search_text != null) {
                $orderRequests->Search($request->search_text);
            }

            if ($request->has('status') && $request->status != null) {
                $history_status = array($request->status);
            }

            if ($request->has('user_id') && $request->user_id != null) {
                $orderRequests->where('user_id', $request->user_id);
            }

            if ($request->has('provider_id') && $request->provider_id != null) {
                $orderRequests->where('provider_id', $request->provider_id);
            }

            if ($request->has('ride_type') && $request->ride_type != null) {
                $orderRequests->where('store_type_id', $request->ride_type);
            }

            if ($request->has('order_by')) {
                $orderRequests->orderby($request->order_by, $request->order_direction);
            }
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            if ($type == 'today') {
                $orderRequests->where('created_at', '>=', Carbon::today());
            } elseif ($type == 'monthly') {
                $orderRequests->where('created_at', '>=', Carbon::now()->month);
            } elseif ($type == 'yearly') {
                $orderRequests->where('created_at', '>=', Carbon::now()->year);
            } elseif ($type == 'range') {
                if ($request->has('from') && $request->has('to')) {
                    if ($request->from == $request->to) {
                        $orderRequests->whereDate('created_at', date('Y-m-d', strtotime($request->from)));
                    } else {
                        $orderRequests->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from), Carbon::createFromFormat('Y-m-d', $request->to)]);
                    }
                }
            } else {
                // dd(5);
            }
            $cancelservices = $orderRequests;
            $orderCounts = $orderRequests->count();
            $dataval = $orderRequests->whereIn('status', $history_status)->paginate(10);
            $cancelledQuery = $cancelservices->where('status', 'CANCELLED')->count();
            $total_earnings = 0;
            foreach ($dataval as $order) {
                //$order->status = $order->status == 1?'Enabled' : 'Disable';
                $orderid  = $order->id;
                $earnings = StoreOrderInvoice::select('total_amount', 'payment_mode')->where('store_order_id', $orderid)->where('company_id',  Auth::user()->company_id)->first();
                if ($earnings != null) {
                    $order->payment_mode = $earnings->payment_mode;
                    $order->earnings = $earnings->total_amount;
                    $total_earnings = $total_earnings + $earnings->total_amount;
                } else {
                    $order->earnings = 0;
                }
            }
            $data['orders'] = $dataval;
            $data['total_orders'] = $orderCounts;
            $data['revenue_value'] = $total_earnings;
            $data['cancelled_orders'] = $cancelledQuery;
            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $datum = StoreOrder::findOrFail($id);
            $datum->is_deleted = 1;
            $datum->deleted_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            $datum->deleted_type = 'ADMIN';
            $datum->deleted_by = Auth::user()->id;
            $datum->save();
            // $datum['body'] = "deleted";
            // $this->sendUserData($datum);

            // return $this->removeModel($id);
            return Helper::getResponse(['message' => trans('admin.delete')]);
        } catch (\Exception $e) {
            return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function requestHistoryDetails($id)
    {
        try {
            $data = StoreOrder::with('user', 'provider', 'orderInvoice')->findOrFail($id);
            return Helper::getResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function search(Request $request, $id)
    {
        $Shops = [];
        $dishes = [];
        if ($request->has('q')) {
            $prodname = $request->q;
            $search_type = $request->t;
            if ($search_type == 'store') {
                $shopps = Store::with(['categories'])->where('company_id', $this->company_id)->where('store_type_id', $id)
                    /*->select('id','store_type_id','company_id','store_name','store_location','latitude','longitude','picture','offer_min_amount','estimated_delivery_time','free_delivery','is_veg','rating','offer_percent')*/
                    ->where('store_name', 'LIKE', '%' . $prodname . '%');
                if ($request->has('latitude') && $request->has('latitude') != '' && $request->has('longitude') && $request->has('longitude') != '') {
                    $longitude = $request->longitude;
                    $latitude = $request->latitude;
                    $distance = $this->settings->order->search_radius;
                    // config('constants.store_search_radius', '10');
                    if ($distance > 0) {
                        $shopps->select('id', 'store_type_id', 'company_id', 'store_name', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent', \DB::raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"))
                            ->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance"); //6371
                    }
                }
                $shops = $shopps->get();
                $shops->map(function ($shop) {
                    $shop->name = $shop->store_name;
                    $shop->item_discount = $shop->offer_percent;
                    $shop->store_id = $shop->id;
                    $shop->delivery_time = $shop->estimated_delivery_time;
                    $shop['shopstatus'] = $this->shoptime($shop->id);
                    //$shop['category'] = $shop->categories()->select(\DB::raw('group_concat(store_category_name) as names'))->names;
                    $cat = [];
                    foreach ($shop->categories as $item) {
                        $cat[] = $item->store_category_name;
                    }
                    $shop['category'] = implode(',', $cat);
                    unset($shop->categories);
                    return $shop;
                });
                $data = $shops;
            } else {
                $data = StoreItem::with(['store', 'categories'])->where('company_id', $this->company_id)->where('item_name', 'LIKE', '%' . $prodname . '%')->select('id', 'store_id', 'store_category_id', 'item_name', 'picture', 'item_discount')
                    ->whereHas('store', function ($q) use ($request, $id) {
                        $q->where('store_type_id', $id);
                        if ($request->has('latitude') && $request->has('latitude') != '' && $request->has('longitude') && $request->has('longitude') != '') {
                            $longitude = $request->longitude;
                            $latitude = $request->latitude;
                            $distance = $this->settings->order->search_radius;
                            // config('constants.store_search_radius', '10');
                            if ($distance > 0) {
                                $q->select('id', 'store_type_id', 'company_id', 'store_name', 'store_location', 'latitude', 'longitude', 'picture', 'offer_min_amount', 'estimated_delivery_time', 'free_delivery', 'is_veg', 'rating', 'offer_percent', \DB::raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"))
                                    ->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance"); //6371
                            }
                        }
                    })
                    ->get();
                $data->map(function ($item) {
                    $item->name = $item->item_name;
                    $item->rating = $item->store->rating;
                    $item->delivery_time = $item->store->estimated_delivery_time;
                    $item['shopstatus'] = $this->shoptime($item->store_id);
                    if ($item->categories->count() > 0) {
                        $item['category'] = $item->categories[0]->store_category_name;
                    } else {
                        $item['category'] = null;
                    }
                    unset($item->store);
                    unset($item->categories);
                    return $item;
                });
            }
        }

        return Helper::getResponse(['data' => $data]);
    }


    public function order_request_dispute(Request $request)
    {

        $this->validate($request, [
            'dispute_name' => 'required',
            'dispute_type' => 'required',
            'provider_id' => 'required',
            'user_id' => 'required',
            'id' => 'required',
            'store_id' => 'required',
        ]);
        $order_request_disputes = StoreOrderDispute::where('company_id', $this->company_id)
            ->where('store_order_id', $request->id)
            ->where('dispute_type', 'user')
            ->first();
        $request->request->add(['admin_service' => 'ORDER']);
        if ($order_request_disputes == null) {
            try {
                $disputeRequest = new StoreOrderDispute;
                $data = (new UserServices())->userDisputeCreate($request, $disputeRequest);
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
        } else {
            return Helper::getResponse(['status' => 404, 'message' => trans('Already Dispute Created for the Ride Request')]);
        }
    }
//comment added
    public function get_order_request_dispute(Request $request, $id)
    {
        $order_request_dispute = StoreOrderDispute::with('request')->where('company_id', $this->company_id)
            ->where('store_order_id', $id)
            ->where('dispute_type', 'user')
            ->first();
        if ($order_request_dispute) {
            $order_request_dispute->created_time = (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $order_request_dispute->created_at, 'UTC'))->setTimezone($order_request_dispute->request->timezone)->format(Helper::dateFormat());
        }

        return Helper::getResponse(['data' => $order_request_dispute]);
    }
}
