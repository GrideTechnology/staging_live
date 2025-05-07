<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Traits\Actions;
use App\Models\Common\Subscription;
use DB;
use Auth;

class SubscriptionController extends Controller
{
        use Actions;

        private $model;
        private $request;
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Subscription $model)
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
        $subscription = Subscription::select('*');

        if($request->has('search_text') && $request->search_text != null) {
            $subscription->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $subscription->orderby($request->order_by, $request->order_direction);
        }
        
        if($request->has('page') && $request->page == 'all') {
            $subscription = $subscription->get();
        } else {
            $subscription = $subscription->paginate(10);
        }

        return Helper::getResponse(['data' => $subscription]);
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
            'subscription_name' => 'required',
            'amount' => 'required',  
            'duration' => 'required',          
        ]);

        try{

            $duration = $request->duration;
            
            if($request->duration==''){
                $duration=0;
            }

            $subscription = new Subscription;
            $subscription->subscription_name = $request->subscription_name;
            $subscription->amount = $request->amount;   
            $subscription->duration = $request->duration;                  
            $subscription->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $subscription = Subscription::find($id);

            return Helper::getResponse(['data' => $subscription]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subscription_name' => 'required',
            'amount' => 'required',
            'duration' => 'required',       
        ]);

        try {

            $subscription = Subscription::findOrFail($id);
            $subscription->subscription_name = $request->subscription_name;
            $subscription->amount = $request->amount;   
            $subscription->duration = $request->duration;                     
            $subscription->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);   
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reason  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->removeModel($id);
    }

}