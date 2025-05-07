<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use App\Traits\Actions;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transport\CarBrand;

class CarBrandController extends Controller
{
    use Actions;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datum = CarBrand::leftJoin('ride_delivery_vehicles', function($join) {
            $join->on('ride_delivery_vehicles.id', '=', 'car_brand_master.ride_delivery_vehicles_id');
          })
          ->select(['car_brand_master.id','car_brand_master.brand_name','car_brand_master.slug','ride_delivery_vehicles.vehicle_name','car_brand_master.is_active','car_brand_master.is_deleted'])
          ->where(['car_brand_master.is_active' => 1, 'car_brand_master.is_deleted' => 0]);        
        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }
        if($request->has('page') && $request->page == 'all') {
            $datum = $datum->get();
        } else {
            $datum = $datum->paginate(10);
        }                
        return Helper::getResponse(['data' => $datum]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'brand_name' => 'required|unique:car_brand_master,brand_name|max:255',            
            'ride_delivery_vehicles_id' => 'required|integer',            
        ]);

        try {
            $model = new CarBrand();
            $slug = Helper::make_slug($request->brand_name);            
            $model->fill($request->all());
            $slug = CarBrand::slugExist($slug,$slug);
            $model->slug= $slug;            
            if($model->save()){
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
            } else {
                return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => trans('admin.something_wrong')]);
            }
        } catch (\Exception $e){
            return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {        
        $this->validate($request, [
            'brand_name' => 'required|unique:car_brand_master,brand_name,'.$id.',id|max:255',            
            'ride_delivery_vehicles_id' => 'required|integer',            
        ]);

        try {
            $model = CarBrand::where(['id' => $id])->first();
            $model->fill($request->all());                          
            if($model->save()){
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } else {
                return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => trans('admin.something_wrong')]);
            }
        } catch (\Exception $e){
            return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $model = CarBrand::where(['id' => $id])->first();
            $model->is_deleted = 1;                       
            if($model->save()){
                return Helper::getResponse(['status' => 200, 'message' => trans('admin.delete')]);
            } else {
                return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => trans('admin.something_wrong')]);
            }
        } catch (\Exception $e){
            return Helper::getResponse(['status' => 400, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
}
