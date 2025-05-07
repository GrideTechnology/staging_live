<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;

use App\Models\Order\Cuisine;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Traits\Actions;
use Exception;
use Setting;
use Auth;

class CuisinesController extends Controller
{
    use Actions;

    private $model;
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Cuisine $model)
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
        $datum = Cuisine::with('storetype')->where(['company_id' => Auth::user()->company_id, 'is_deleted' => 0]);

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
             'store_type_id'=>'required'
             
        ]);
       
        try{
            $cuisine = new Cuisine;
            $cuisine->company_id = Auth::user()->company_id;  
            $cuisine->name = $request->name; 
            $cuisine->store_type_id = $request->store_type_id; 
            $cuisine->status = $request->status;      
            $cuisine->save();
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

            $storetype = Cuisine::findOrFail($id);
            

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
             'store_type_id'=>'required'
            
        ]);
    
        try {
            $cuisine = Cuisine::findOrFail($id);
            $cuisine->name = $request->name;
            $cuisine->store_type_id = $request->store_type_id;
            $cuisine->status = $request->status;                    
            $cuisine->update();
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
      } 
 catch (\Throwable $e) {
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

        $model = Cuisine::findOrFail($id);
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

            $datum = Cuisine::findOrFail($id);
            
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
