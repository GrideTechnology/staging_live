<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;

use App\Models\Order\StoreAddon;
use App\Models\Order\StoreItemAddon;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Order\StoreItem;
use App\Traits\Actions;
use Exception;
use Auth;

class ShopsaddonController extends Controller
{
    use Actions;
 
    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreAddon $model)
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
      
        $datum = StoreAddon::where('company_id', $this->company_id)->where('store_id',$id);

        if($request->has('search_text') && $request->search_text != null) { 
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        
        if($request->has('limit')) {
           $data = $datum->paginate($request->limit);
        } else {
            $data = $datum->paginate(10);
        }

         
        
        return Helper::getResponse(['data' => $data]);
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
             'addon_name' => 'required',
             
             
        ]);
       
        try{
            $storeaddon = new StoreAddon;
            $storeaddon->company_id = $this->company_id;  
            $storeaddon->addon_name = $request->addon_name; 
            $storeaddon->store_id = $request->store_id; 
            $storeaddon->addon_status = $request->addon_status; 
            
            $storeaddon->save();
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

            $storeaddon = StoreAddon::findOrFail($id);
            

            return Helper::getResponse(['data' => $storeaddon]);
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
             'addon_name' => 'required',
        ]);
    
        try {
            $storeaddon = StoreAddon::findOrFail($id);
            $storeaddon->addon_name = $request->addon_name;
            $storeaddon->addon_status = $request->addon_status;                    
            $storeaddon->update();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function addonlist($store_id, $item_id = null)
    {
        try {
            $cuisines = StoreAddon::select("id", "store_id", "addon_name","addon_status")->where('store_id',$store_id)->where('addon_status',1)->get();
            $data = [];
            foreach ($cuisines as $cuisine) {
                $cuisine_list = new \stdClass;
                foreach ($cuisine->getAttributes() as $key => $value) {
                    $cuisine_list->$key = $value;
                }
                if($item_id != null) {
                    $storeitem = StoreItem::with(['itemsaddon' => function($query) use($cuisine_list) {
                        $query->where('store_addon_id', $cuisine_list->id);
                    }])->findOrFail($item_id);
                    $cuisine_list->storeitem = count($storeitem->itemsaddon) > 0 ? $storeitem->itemsaddon[0] : null;
                } else {
                    $cuisine_list->storeitem = null;
                }
                

                $data[] = $cuisine_list;
            }



            return Helper::getResponse(['data' => $data]);
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
    public function destroy(Request $request,$id)
    {
        $connected=StoreItemAddon::where('store_addon_id',$id)->get();
        if($request->confirm==1 && count($connected) > 0 ){
            $data['status']='1';
            return Helper::getResponse(['message' => 'Addon Is Used In Shop Items Are you Confirm want to Delete','data'=>$data]);
        }elseif($request->confirm==0){
            StoreItemAddon::where('store_addon_id',$id)->delete();
            return $this->removeModel($id);

        }else{
             return $this->removeModel($id);
        }


       
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = StoreAddon::findOrFail($id);
            
            if($request->has('status')){
                if($request->status == 1){
                    $datum->addon_status = 0;
                }else{
                    $datum->addon_status = 1;
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
