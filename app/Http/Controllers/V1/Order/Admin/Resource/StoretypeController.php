<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;

use App\Models\Order\StoreType;
use App\Models\Order\StoreCityPrice;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Common\AdminService;
use App\Models\Common\MenuCity;
use App\Models\Common\Menu;
use App\Models\Common\CompanyCity;
use App\Traits\Actions;
use Exception;
use Setting;
use Auth;
 
class StoretypeController extends Controller
{
    use Actions;

    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreType $model)
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
        $datum = StoreType::where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
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
            'name' => 'required',
            'category' => 'required',
             
        ]);
       
        try{
            $storetype = new StoreType;
            $storetype->company_id = Auth::user()->company_id;  
            $storetype->name = $request->name; 
            $storetype->category = $request->category; 
            $storetype->status = $request->status;      
            $storetype->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
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

            $storetype = StoreType::findOrFail($id);
            

            return Helper::getResponse(['data' => $storetype]);
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
             'name' => 'required',
             'category' => 'required',
            
        ]);
    
        try {
            $storetype = StoreType::findOrFail($id);
            $storetype->name = $request->name;
            $storetype->category = $request->category; 
            $storetype->status = $request->status;                    
            $storetype->update();
           return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

    public function storetypelist()
  {

  try {
            if(!empty(Auth::user())){          
            $this->company_id = Auth::user()->company_id;
            }
            else{          
                $this->company_id = Auth::guard('shop')->user()->company_id;
            } 

            $storetypelist = StoreType::where('company_id',$this->company_id)->where('status',1)->get();
            return Helper::getResponse(['data' => $storetypelist]);
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
        $model = StoreType::findOrFail($id);
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
        // return $this->removeModel($id);
       
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = StoreType::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->status = 0;
                }else{
                    $datum->status = 1;
                }
            }
            $datum->save();

            $menu=Menu::where('menu_type_id',$id)->where('admin_service','ORDER')->where('company_id',Auth::user()->company_id)->first();
            if(!empty($menu)){

                $menu->status=$datum->status;
                $menu->save();
            }
           
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


     public function webproviderservice(Request $request,$id)
    {
     
     try{
        $storetype=StoreType::with(array('provideradminservice'=>function($query) use ($id){
            $query->where('provider_id',$id)->with('providervehicle');
        }))->where('company_id',Auth::user()->company_id)->get();

        return Helper::getResponse(['data' => $storetype ]);
    }catch (ModelNotFoundException $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
        }

    }

    public function getstorePrice($city_id,$storetype_id){
        $storeCityPrice = StoreCityPrice::where('company_id',Auth::user()->company_id)->where('store_type_id',$storetype_id)->where('city_id',$city_id)->first();
        return Helper::getResponse(['data' => $storeCityPrice]);
    }

    public function storePricePost(Request $request)
    {
        $this->validate($request, [
            'delivery_charge' => 'required',
            'small_order_fee' => 'required',
            'minimum_delivery_charge' => 'required',
            'maximum_delivery_charge' => 'required',
            'minimum_surge_delivery_charge' => 'required',
            'maximum_surge_delivery_charge' => 'required',
            'service_fee' => 'required',
            'cancellation_fee' => 'required',
            'commission_fee' => 'required',
        ]); 

        try{
            if($request->id !=''){
                $storeprice = StoreCityPrice::findOrFail($request->id);
            }else{
                $storeprice = new StoreCityPrice;
            }
            $storeprice->company_id = Auth::user()->company_id;  
            $storeprice->delivery_charge = $request->delivery_charge; 
            $storeprice->small_order_fee = $request->small_order_fee; 
            $storeprice->minimum_delivery_charge = $request->minimum_delivery_charge; 
            $storeprice->maximum_delivery_charge = $request->maximum_delivery_charge; 
            $storeprice->minimum_surge_delivery_charge = $request->minimum_surge_delivery_charge; 
            $storeprice->maximum_surge_delivery_charge = $request->maximum_surge_delivery_charge;
            $storeprice->service_fee = $request->service_fee;
            $storeprice->cancellation_fee = $request->cancellation_fee; 
            $storeprice->commission_fee = $request->commission_fee;
            $storeprice->store_type_id = $request->store_type_id;  
            $storeprice->city_id = $request->city_id;  
            $storeprice->country_id = $request->country_id;  
            $storeprice->admin_service ='ORDER';  
            $storeprice->save();
           return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
           return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
     }

     public function getcity(Request $request)
    {
         //dd($request->city_id);
        $menudetails=Menu::select('menu_type_id')->where('id',$request->menu_id)->first();
       
        $orderprice=StoreCityPrice::select('city_id')->where('store_type_id',$menudetails->menu_type_id)->get()->toArray();
        $company_cities = CompanyCity::with(['country','city','menu_city' => function($query) use($request) {
            $query->where('menu_id','=',$request->menu_id);
        }])->where('company_id', Auth::user()->company_id);

        if ($request->has('country_id') && $request->country_id != null) {
            $company_cities = $company_cities->where('country_id', $request->country_id);
        }

        if ($request->has('state_id') && $request->state_id != null) {
            $company_cities = $company_cities->where('state_id', $request->state_id);
        }

        if ($request->has('search_text') && $request->search_text != null) {
           $company_cities = $company_cities->search($request->search_text);
        }
        
        $cities = $company_cities->paginate(500);

        foreach($cities as $key=>$value){

           $cities[$key]['city_price']=0;
           
           if(in_array($value->city_id,array_column($orderprice,'city_id'))){
            
             $cities[$key]['city_price']=1;
           } 
        }


        return Helper::getResponse(['data' => $cities]);
    }

}
