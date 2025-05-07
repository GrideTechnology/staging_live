<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;

use App\Models\Order\StoreItem;
use App\Models\Order\StoreItemAddon;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Traits\Actions;
use Exception;
use Setting;
use Auth;

class ShopsitemsController extends Controller
{
    use Actions;

    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreItem $model)
    {
        $this->model = $model; 

         if(!empty(Auth::user())){          
            $this->company_id = Auth::user()->company_id;
        }
        else{          
            $this->company_id = Auth::guard('shop')->user()->company_id;
        } 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$id)
    {
        $datum = StoreItem::where('company_id', $this->company_id)->where('store_id',$id);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        
        if($request->has('limit') && $request->has('type')) {
           $datum=$datum->paginate($request->limit);
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
             'item_name' => 'required',
             'store_category_id'=>'required',
             'item_price'=>'required',
             'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880'
             
        ]);
       
        try{
            $storeitem = new StoreItem;
            $storeitem->company_id = $this->company_id;  
            $storeitem->item_name = $request->item_name; 
            $storeitem->store_id = $request->store_id; 
            $storeitem->item_description = $request->item_description; 
            $storeitem->store_category_id = $request->store_category_id; 
            $storeitem->is_veg = $request->is_veg; 
            $storeitem->item_price = $request->item_price;  
            $storeitem->item_discount = $request->item_discount;  
            $storeitem->item_discount_type = $request->item_discount_type;  
              if($request->hasFile('picture')) {
           $storeitem->picture = Helper::upload_file($request->file('picture'), 'shops/items',null,$this->company_id);
            }
            $storeitem->status = $request->status;      
            $storeitem->save();
               if($request->has('addon')) {
                $addon_price = $request->addon_price;
                foreach($request->addon as $key => $addon) 
                {  
                    if($addon_price[$addon] > 0){
                        $addons[] = [
                            'store_addon_id' => $addon,
                            'store_item_id' => $storeitem->id,
                            'price' => $addon_price[$addon],
                            'store_id' => $request->store_id,
                            'company_id' => $this->company_id,
                        ];
                        StoreItemAddon::insert($addons); 
                    }
                }
                
            }
         


            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage(),'data'=>$request->all()]);
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
            $storeitem = StoreItem::with('itemsaddon')->findOrFail($id);
            return Helper::getResponse(['data' => $storeitem]);
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
             'item_name' => 'required',
             'store_category_id'=>'required',
             'item_price'=>'required',
             
        ]);

    
        try {
            $storeitem = StoreItem::findOrFail($id);
            $storeitem->item_name = $request->item_name; 
            $storeitem->item_description = $request->item_description; 
            $storeitem->store_category_id = $request->store_category_id; 
            $storeitem->is_veg = $request->is_veg; 
            $storeitem->item_price = $request->item_price;  
            $storeitem->item_discount = $request->item_discount;  
            $storeitem->item_discount_type = $request->item_discount_type;  
              if($request->hasFile('picture')) {
             $storeitem->picture = Helper::upload_file($request->file('picture'), 'shops/items',null,$this->company_id);
            }

            $storeitem->status = $request->status;      
            $storeitem->update();
            StoreItemAddon::where('store_item_id',$id)->delete();
           
             if($request->has('addon')) {
                $addon_price = $request->addon_price;
                $addons = [];
                foreach($request->addon as $key => $addon) 
                {  
                   if($addon_price[$addon] > 0){ 
                        $addons[] = [
                            'store_addon_id' => $addon,
                            'store_item_id' => $storeitem->id,
                            'price' => $addon_price[$addon],
                            'store_id' => $request->store_id,
                            'company_id' => $this->company_id,
                        ];
                    }
                }

                if(count($addons) > 0) StoreItemAddon::insert($addons); 
            }


           return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

      public function cuisinelist($id)
  {

  try {

            $cuisinelist = Cuisine::where('store_type_id',$id)->where('status',1)->get();
            return Helper::getResponse(['data' => $cuisinelist]);
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
StoreItemAddon::where('store_item_id',$id)->delete();
        
 return $this->removeModel($id);
       
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = StoreItem::findOrFail($id);
            
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

}
