<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;

use App\Models\Order\Store;
use App\Models\Order\StoreCuisines;
use App\Models\Order\StoreTiming;
use App\Models\Common\CompanyCity;
use App\Models\Order\Cuisine;
use App\Models\Common\Zone;
use App\Models\Order\StoreWallet;
use App\Models\Order\StoreOrder;
use App\Models\Order\StoreOrderInvoice;
use App\Models\Common\AuthLog;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\CompanyCountry;
use App\Http\Controllers\Controller;
use App\Traits\Actions;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use Exception;
use Setting;
use Auth;
use DB;
use Carbon\Carbon;

class ShopsController extends Controller
{
    use Actions,Encryptable;

    private $model; 
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Store $model)
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
        $datum = Store::with('storetype')->where(['company_id' => Auth::user()->company_id,'is_deleted' => 0]);
        
        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('search_state') && $request->search_state != null) {
            $datum->where('store_location','LIKE', '%'.$request->search_state.'%');
        }

         if($request->has('start_date') && $request->start_date != null) {
            $datum->where('created_at', '>=', $request->start_date);
        }

        if($request->has('end_date') && $request->end_date != null) {
            $datum->where('created_at', '<=', $request->end_date);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        
        if($request->has('page') && $request->page == 'all') {
            $datum = $datum->get();
        } else {
            $datum = $datum->paginate(10);
        }
        return Helper::getResponse(['data' => $datum]);
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
             'store_name' => 'required',
             //'cuisine_id'=>'required|exists:store,store_type_id,1',
             'store_type_id'=>'required',
             // 'is_veg'=>'required',
             'email'=>'required|email|max:255',
             // 'estimated_delivery_time'=>'required',
             'contact_number'=>'required',
             'password'=>'required|min:6',
             'store_location'=>'required',
             'latitude' => 'required',
             'longitude' => 'required',
             'store_zipcode'=>'required',
             'zone_id'=>'required',
             'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
             
         ]);

         if($request->store_type_id==1){
             $this->validate($request, [  
                 'cuisine_id'=>'required',
                 'is_veg'=>'required',
                 'estimated_delivery_time'=>'required',
             ]);
         }

           $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            'contact_number' => $this->cusencrypt($request->contact_number,env('DB_SECRET')),
        ]);

           $email=$request->email;
           $contact_number=$request->contact_number;
             $company_id=Auth::user()->company_id;

                 $this->validate($request, [          
            'email' =>[ Rule::unique('order.stores')->where(function ($query) use($email,$company_id) {
                            return $query->where('email', $email)->where('company_id', $company_id);
                         }),
                       ],
             'contact_number' =>[ Rule::unique('order.stores')->where(function ($query) use($contact_number,$company_id) {
                            return $query->where('contact_number', $contact_number)->where('company_id', $company_id);
                         }),
                       ],          
        ]);
       
        try{

              $request->merge([
                'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                'contact_number' => $this->cusdecrypt($request->contact_number,env('DB_SECRET')),
            ]);
            $store=new Store;
            $store->store_name = $request->store_name; 
            $store->store_type_id = $request->store_type_id; 
            $store->status = $request->status;      
            if($request->has('is_veg')){
                $store->is_veg = $request->is_veg;    
            }else{
                $store->is_veg = 0; 
            }      
            $store->email = $request->email; 

            if($request->has('estimated_delivery_time')) {
                $store->estimated_delivery_time = $request->estimated_delivery_time;     
            }else{
                $store->estimated_delivery_time = 0;   
            }     
            $store->contact_number = $request->contact_number;      
            $store->password = Hash::make($request->password);;      
            $store->store_location = $request->store_location; 
            $store->latitude = $request->latitude;
            $store->longitude = $request->longitude;        
            $store->store_zipcode = $request->store_zipcode;      
            $store->contact_person = $request->contact_person;      
            $store->picture = $request->picture;      
            $store->store_packing_charges = $request->store_packing_charges;
            $country = CompanyCountry::where('company_id',$company_id)->where('country_id', $request->country_id)->first();
            $store->currency_symbol = '$';      
            $store->store_gst = $request->store_gst;      
            $store->offer_min_amount = $request->offer_min_amount;      
            $store->offer_percent = $request->offer_percent;      
            $store->description = $request->description;      
            $store->offer_percent = $request->offer_percent;      
            $store->country_id = $request->country_id;  
            $store->city_id = $request->city_id;  
            $store->store_gst = $request->store_gst;  
            $store->commission = $request->commission;  
            $store->country_code = $request->country_code;
            $store->zone_id = $request->zone_id;  
            if($request->has('free_delivery')){
                $store->free_delivery = $request->free_delivery;
            }else{
                $store->free_delivery = 0;
            }
            if($request->hasFile('picture')) {
            
        $store->picture = Helper::upload_file($request->file('picture'), 'shops/profile');
            }
            $store->company_id=Auth::user()->company_id;    
            $store->save();
            if($request->store_type_id==1){
                  foreach($request->cuisine_id as $k=>$v){
                    $cuisine= new StoreCuisines;
                    $cuisine->store_type_id=$request->store_type_id;
                    $cuisine->store_id=$store->id;
                    $cuisine->cuisines_id=$v;
                    $cuisine->company_id=Auth::user()->company_id;
                    $cuisine->save();

                  }
            }

              if($request->has('day')) {
                $start_time = $request->hours_opening;
                $end_time = $request->hours_closing;
                foreach($request->day as $key => $day) 
                {  
                    $timing[] = [
                        'store_start_time' =>date("H:i", strtotime($start_time[$day])),
                        'store_end_time' =>date("H:i", strtotime($end_time[$day])),
                        'store_id' => $store->id,
                        'store_day' => $day,
                        'company_id' => Auth::user()->company_id,
                    ];
                }
                StoreTiming::insert($timing); 
            }



                 
            
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage(),'data'=>$store]);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $account
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        try {

            $store = Store::findOrFail($id);
            $store['cui_selectdata']=StoreCuisines::where('store_id',$id)->pluck('cuisines_id')->all();
            $store['cuisine_data']=Cuisine::where('store_type_id',$store['store_type_id'])->where('status',1)->get();
            $store['time_data']=StoreTiming::where('store_id',$id)->get();
            foreach($store['time_data'] as $k=>$v){
                $store['time_data'][$k]['store_start_time']=date("g:i a", strtotime($v['store_start_time']));
                $store['time_data'][$k]['store_end_time']=date("g:i a", strtotime($v['store_end_time']));
            }
            $store['city_data']=CompanyCity::where("country_id",$store['country_id'])->with('city')->get();
             if(!empty(Auth::user())){          
            $this->company_id = Auth::user()->company_id;
            }
            else{          
                $this->company_id = Auth::guard('shop')->user()->company_id;
            } 
            $store['zone_data'] = Zone::where("city_id",$store['city_id'])->where('company_id',$this->company_id)->where('user_type',"SHOP")->get();            

            return Helper::getResponse(['data' => $store]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $this->validate($request, [
             'store_name' => 'required',
             //'cuisine_id'=>'required',
             'store_type_id'=>'required',
             // 'is_veg'=>'required',
             'email'=>'required|email|max:255',
             // 'estimated_delivery_time'=>'required',
             'contact_number'=>'required',
             
             'store_location'=>'required',
             'latitude' => 'required',
             'longitude' => 'required',
             'store_zipcode'=>'required',
             'zone_id'=>'required',
             
         ]);

        if($request->store_type_id==1){
             $this->validate($request, [  
                 'cuisine_id'=>'required',
                 'is_veg'=>'required',
                 'estimated_delivery_time'=>'required',
             ]);
         }


  $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            'contact_number' => $this->cusencrypt($request->contact_number,env('DB_SECRET')),
        ]);

           $email=$request->email;
           $contact_number=$request->contact_number;
           if(!empty(Auth::user())){          
            $this->company_id=$company_id= Auth::user()->company_id;
            }
            else{          
                $this->company_id=$company_id = Auth::guard('shop')->user()->company_id;
            }
             

                 $this->validate($request, [          
            'email' =>[ Rule::unique('order.stores')->where(function ($query) use($email,$company_id,$id) {
                            return $query->where('email', $email)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                         }),
                       ],
             'contact_number' =>[ Rule::unique('order.stores')->where(function ($query) use($contact_number,$company_id,$id) {
                            return $query->where('contact_number', $contact_number)->where('company_id', $company_id)->whereNotIn('id', [$id]);
                         }),
                       ],          
        ]);


    
        try {
      
          $request->merge([
                'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
                'contact_number' => $this->cusdecrypt($request->contact_number,env('DB_SECRET')),
            ]);


             $store = Store::findOrFail($id);
             $store->store_name = $request->store_name; 
            $store->store_type_id = $request->store_type_id; 
            $store->status = $request->status;      
            if($request->has('is_veg')){
                $store->is_veg = $request->is_veg;    
            }else{
                $store->is_veg = 0; 
            }     
            $store->email = $request->email;      
            if($request->has('estimated_delivery_time')) {
                $store->estimated_delivery_time = $request->estimated_delivery_time;     
            }else{
                $store->estimated_delivery_time = 0;   
            }     
            $store->contact_number = $request->contact_number;
            if($request->has('password')){     
            $store->password = Hash::make($request->password);;
            }      
            $store->store_location = $request->store_location; 
            $store->latitude = $request->latitude;
            $store->longitude = $request->longitude;     
            $store->store_zipcode = $request->store_zipcode;      
            $store->contact_person = $request->contact_person;      
            $store->store_packing_charges = $request->store_packing_charges;
            $store->zone_id = $request->zone_id;      
            $store->store_gst = $request->store_gst;      
            $store->offer_min_amount = $request->offer_min_amount;      
            $store->offer_percent = $request->offer_percent;      
            $store->description = $request->description;      
            $store->country_id = $request->country_id;  
            $store->city_id = $request->city_id;
            $store->commission = $request->commission;   
            $store->country_code = $request->country_code; 
            if($request->has('free_delivery')){
                $store->free_delivery = $request->free_delivery;
            }else{
                $store->free_delivery = 0;
            }  
             if($request->hasFile('picture')) {
           $store->picture = Helper::upload_file($request->file('picture'), 'shops/profile');
            }
            $storedata=$store->update();
            if($request->store_type_id==1){
                StoreCuisines::where('store_id',$id)->delete();
                  foreach($request->cuisine_id as $k=>$v){
                    $cuisine= new StoreCuisines;
                    $cuisine->store_type_id=$request->store_type_id;
                    $cuisine->store_id=$store->id;
                    $cuisine->cuisines_id=$v;
                    $cuisine->company_id=$this->company_id;
                    $cuisine->save();

                  }
            }
              if($request->has('day')) {
                StoreTiming::where('store_id',$id)->delete();
                $start_time = $request->hours_opening;
                $end_time = $request->hours_closing;
                foreach($request->day as $key => $day) 
                {   
                    $timing[] = [
                    'store_start_time' =>date("H:i", strtotime($start_time[$day])),
                        'store_end_time' =>date("H:i", strtotime($end_time[$day])),
                        'store_id' => $store->id,
                        'store_day' => $day,
                        'company_id' => $this->company_id,
                    ];
                }
                StoreTiming::insert($timing); 
            }                   
            $store->update();
           return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Store::findOrFail($id);
        if($model){
            $model->is_deleted = 1;
            $model->deleted_at = \Carbon\Carbon::now();
            $model->deleted_type = Auth::user()->type;
            $model->deleted_by = Auth::user()->id;
            $model->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.delete')]);
        } else{
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
        }
        // StoreCuisines::where('store_id',$id)->delete();   
        // StoreTiming::where('store_id',$id)->delete();   
        // return $this->removeModel($id);

       
    }


     public function shoptimings(Request $request)
    {
        
        try {
             $datum = StoreTiming::where('store_id',Auth::guard('shop')->user()->id)->get();
             return Helper::getResponse(['data'=>$datum]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

     public function updateshoptimings(Request $request)
    {
        
        try {

            if(!empty(Auth::user())){          
                $this->company_id = Auth::user()->company_id;
            }
            else{          
                $this->company_id = Auth::guard('shop')->user()->company_id;
            } 
                
            StoreTiming::where('store_id',Auth::guard('shop')->user()->id)->delete();
            $start_time = $request->hours_opening;
            $end_time = $request->hours_closing;
            foreach($request->day as $key => $day) 
            {   
                $timing[] = [
                    'store_start_time' =>date("H:i", strtotime($start_time[$day])),
                    'store_end_time' =>date("H:i", strtotime($end_time[$day])),
                    'store_id' => Auth::guard('shop')->user()->id,
                    'store_day' => $day,
                    'company_id' => $this->company_id,
                ];
            }
            StoreTiming::insert($timing); 
                
            return Helper::getResponse(['status' => 200,'message' => trans('admin.update')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }






    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = Store::findOrFail($id);
            
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

    public function walletDetails($type, $id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(15);
        
            $wallet_details = UserWallet::where('user_id', $id)->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(10);

        return Helper::getResponse(['data' => $wallet_details]);
    }

    public function logDetails($id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(7);

        $datum = AuthLog::where('user_type', "Shop")->where('user_id', $id)->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(5);

        return Helper::getResponse(['data' => $datum]);
    }

    public function getStorePriceCities()
    {
        $cityList = CompanyCountry::with('country','companyCountryStates','companyCountryCities')->where('company_id',Auth::user()->company_id)->where('status',1)->get();
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
                }
            }
            // usort($state, function($a, $b) {return strcmp($a->state_name, $b->state_name);});
            $country->state = $state;
            $country->city = $city;
            return $country;
        });
        return Helper::getResponse(['data' => $countries]);
    }


    public function dashboarddata($id)
    {
      try{
          $data['storedata']=Store::where('country_id', $id)->where('company_id',\Auth::user()->company_id)->count();
          $data['overall_data']=StoreOrder::where('country_id', $id)->where('company_id',\Auth::user()->company_id)->where('status','COMPLETED')->count();
          return Helper::getResponse(['status' => 200,'data'=>$data]);

         }
         catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
      
   } 

    public function storedashboard($id)
    {
      try{
          
          $completed= StoreOrder::where('country_id',$id)->where('status','COMPLETED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
          });
          $cancelled= StoreOrder::where('country_id',$id)->where('status','CANCELLED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
          });

          $month=array('01','02','03','04','05','06','07','08','09','10','11','12');
           
          foreach($month as $k => $v){
              if(empty($completed[$v])){
                $complete[]=0;
              }else{
                $complete[]=count($completed[$v]);
              }

              if(empty($cancelled[$v])){
                $cancel[]=0;
              }else{
                $cancel[]=count($cancelled[$v]);
              }
          }

          $data['cancelled_data']=$cancel;
          $data['completed_data']=$complete;
          $data['max']=max($complete);

          if(max($complete) < max($cancel)){
            $data['max']=max($cancel);
          }
          
          
          return Helper::getResponse(['status' => 200,'data'=> $data]);

         }
         catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
      
   }

   public function storeStatementHistory(Request $request)
	{  
		try {
            $history_status = array('CANCELLED','COMPLETED');
            if($request->has('search_text') && $request->search_text != null) {
                $storeLists = Store::testsearch($request->search_text)->select('*','created_at as joined')->where('company_id',  Auth::user()->company_id);
            }else{
                $storeLists = Store::select('*','created_at as joined')->where('company_id',  Auth::user()->company_id);
            }
            if($request->has('country_id')) {
                $storeLists->where('country_id',$request->country_id);
            }
            if(Auth::user()->hasRole('FLEET')) {
                $storeLists->where('admin_id', Auth::user()->id);  
            }
            
    
            if($request->has('order_by')) {
                $storeLists->orderby($request->order_by, $request->order_direction);
            }
            $type = isset($_GET['type'])?$_GET['type']:'';
            if($type == 'today'){
				$storeLists->where('created_at', '>=', Carbon::today());
			}elseif($type == 'monthly'){
				$storeLists->where('created_at', '>=', Carbon::now()->month);
			}elseif($type == 'yearly'){
				$storeLists->where('created_at', '>=', Carbon::now()->year);
			}elseif ($type == 'range') {   
                if($request->has('from') &&$request->has('to')) {             
                    if($request->from == $request->to) {
                        $storeLists->whereDate('created_at', date('Y-m-d', strtotime($request->from)));
                    } else {
                        $storeLists->whereBetween('created_at',[Carbon::createFromFormat('Y-m-d', $request->from),Carbon::createFromFormat('Y-m-d', $request->to)]);
                    }
                }
			}else{
                // dd(5);
            }
            $cancelservices = $storeLists;
            $orderCounts = $storeLists->count();
            $dataval = $storeLists->where('status',1)->paginate(10);
            $cancelledQuery = $cancelservices->where('status',1)->count();
            $total_earnings = 0;
            foreach($dataval as $shop){
                $shop->status = $shop->status == 1?'Enabled' : 'Disable';
                $shopid  = $shop->id;
                $earnings = StoreOrderInvoice::select('cart_details',DB::raw('sum(total_amount - delivery_amount - commision_amount) as total_amount'))
                ->where('store_id',$shopid)->where('company_id',  Auth::user()->company_id)->first();
                if($earnings != null){
                    $shop->earnings = $earnings->total_amount;
                    $total_earnings = $total_earnings + $earnings->total_amount;
                }else{
                    $shop->earnings = 0;
                }
            }           
            
            $data['stores'] = $dataval;
            $data['total_orders'] = $orderCounts;
            $data['revenue_value'] = $total_earnings;
            $data['cancelled_orders'] = $cancelledQuery;
            return Helper::getResponse(['data' => $data]);

        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


    public function searchprovider(Request $request,$id){
        $provider = Store::where('zone_id',$id)
       ->where('store_name', 'like', "%" . $request->term . "%")
       ->select(\DB::raw("CONCAT(first_name,' ',last_name,'(',id,')') AS label"),\DB::raw("CONCAT(store_name,'(',id,')') AS value"),'id','wallet_balance','zone_id','store_name')->get()->toArray();
       return $provider;
    }

}
