<?php

namespace App\Http\Controllers\V1\Order\Shop;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order\StoreWallet;
use App\Helpers\Helper;
use Auth;
use Carbon\Carbon;

class ShopStatementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function statement_shop(Request $request)
    {
       try{        
            $datum = StoreWallet::with(['order'=>function($q){
               return  $q->select('store_id','created_at','timezone','currency','delivery_address','pickup_address','id');
            },'order.invoice' => function($q){
                return  $q->select('store_id','total_amount','cart_details','store_order_id');
            }])->select('*','created_at as dated')->where('company_id', Auth::guard('shop')->user()->company_id)->where('store_id', Auth::guard('shop')->user()->id);
            if($request->transaction_type == 'order'){
                $datum->whereNotNull('admin_service');
            }else if($request->transaction_type == 'store'){
                //$datum->whereNull('admin_service');
                $datum->whereNotNull('admin_service');
            }
            if($request->has('search_text') && $request->search_text != null) {
                $datum->Search($request->search_text);
            }
            if($request->has('order_by')) {
                $datum->orderby($request->order_by, $request->order_direction);
            }
            /*if($request->has('country_id')) {
                $datum->where('country_id',$request->country_id);
            }*/
            $type = isset($_GET['type'])?$_GET['type']:'';
            if($type == 'today'){
                $datum->where('created_at', '>=', Carbon::today());
            }elseif($type == 'monthly'){
                $datum->where('created_at', '>=', Carbon::now()->month);
            }elseif($type == 'yearly'){
                $datum->where('created_at', '>=', Carbon::now()->year);
            }elseif ($type == 'range') {   
                if($request->has('from') &&$request->has('to')) {             
                    if($request->from == $request->to) {
                        $datum->whereDate('created_at', date('Y-m-d', strtotime($request->from)));
                    } else {
                        $datum->whereBetween('created_at',[Carbon::createFromFormat('Y-m-d', $request->from),Carbon::createFromFormat('Y-m-d', $request->to)]);
                    }
                }
            }else{
                // dd(5);
            }
            if($request->has('page') && $request->page == 'all') {
                $result = $datum->get();
            } else {
                $result = $datum->paginate(10);
            }
            
            if(count($result)>0){
                foreach($result as $value){
                    $value->amount_type = $value->type == 'C' ? 'Credit' :'Debit';
                    /*if($request->transaction_type == 'order'){
                        if($value->admin_service != null && $value->admin_service != ''){
                            $value->admin_service = $value->admin_service;
                        }
                    }else{
                        $value->admin_service = 'Others';
                    }*/
                }  
            }       
            return Helper::getResponse(['data' => $result]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    
}
