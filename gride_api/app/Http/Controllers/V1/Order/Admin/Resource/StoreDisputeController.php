<?php

namespace App\Http\Controllers\V1\Order\Admin\Resource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Order\StoreOrder;
use App\Models\Order\Store;
use App\Models\Order\StoreOrderDispute;
use App\Models\Order\StoreOrderStatus;
use App\Models\Common\Dispute;
use App\Models\Common\UserRequest;
use App\Models\Common\RequestFilter;
use App\Models\Common\AdminService;
use App\Models\Common\Setting;
use App\Models\Common\Provider;
use App\Models\Order\StoreOrderInvoice;
use DB;
use App\Services\Transactions;
use App\Services\SendPushNotification; 

use Illuminate\Support\Facades\Storage;
use Auth;

class StoreDisputeController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(StoreOrderDispute $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = StoreOrderDispute::with('request','user','provider')->where('company_id', Auth::user()->company_id)->orderBy('created_at' , 'desc');

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }

        if($request->has('page') && $request->page == 'all') {
            $data = $datum->get();
        } else {
            $data = $datum->paginate(10);
        }

        return Helper::getResponse(['data' => $data]);
    }

    public function store(Request $request)
    {
       
        $this->validate($request, [
            'request_id' => 'required',
            'dispute_type' => 'required', 
            'dispute_name' => 'required',        
        ]);

        try{
            $Dispute = new StoreOrderDispute();
            $Dispute->company_id = Auth::user()->company_id; 
            $Dispute->store_order_id = $request->request_id;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->user_id = $request->user_id;
            $Dispute->provider_id = $request->provider_id;
            $Dispute->dispute_name = $request->dispute_name;
            $Dispute->store_id = $request->store_id;
            if(!empty($request->dispute_other))
                $Dispute->dispute_name = $request->dispute_other;
            $Dispute->comments = $request->comments;                    
            $Dispute->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
     }


    public function show($id)
    {
        try {
            $RequestDispute = StoreOrderDispute::with('user','provider','request')->findOrFail($id);
            $serviceQuery = Store::where('id',$RequestDispute->request->store_id)->first();
            $storeOrderInvoice = StoreOrderInvoice::where(['store_order_id' => $RequestDispute->store_order_id])->first();            
            $RequestDispute->service = $serviceQuery;
            $RequestDispute->refund_amount = !empty($storeOrderInvoice->payable)?$storeOrderInvoice->payable:0;            
            return Helper::getResponse(['data' => $RequestDispute]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
      
        $this->validate($request, [            
            'comments' => 'required', 
            'provider_id' =>['required_if:admin_status,resign'],        
        ]);

        //try{
            $Dispute = StoreOrderDispute::findOrFail($id);
            $Dispute->comments = $request->comments;                    
            $Dispute->refund_amount = $request->refund_amount;
            $Dispute->status ='closed'; 
            $Dispute->save();

            if($request->refund_amount>0 && $Dispute->dispute_type!='system'){
                $transaction['message']='Order amount refund';
                $transaction['amount']=$request->refund_amount;
                $transaction['company_id']=$Dispute->company_id;
                    if($Dispute->dispute_type=='user'){
                       $transaction['id']=$Dispute->user_id;
                       (new Transactions)->disputeCreditDebit($transaction);
                    } 
                    else
                    {
                        $transaction['id']=$Dispute->provider_id;
                        (new Transactions)->disputeCreditDebit($transaction,0);
                    }
            }
            $storeorder=StoreOrder::findOrFail($Dispute->store_order_id);
            $setting = Setting::where('company_id',$storeorder->company_id)->first();
            if($request->admin_status=='reject'){
                $storeorder->cancelled_by ='NONE'; 
                $storeorder->cancel_reason =$request->comments; 
                $storeorder->status ='CANCELLED'; 
                $storeorder->save();
                if(!empty($storeorder->provider_id)){
                 Provider::where('id',$storeorder->provider_id)->update(['is_assigned'=>0]);  
                }
                UserRequest::where('request_id',$Dispute->store_order_id)->where('admin_service','ORDER')->delete();
                (new SendPushNotification)->StoreCanlled($storeorder->user_id, 'order');
                
                if($request->refund_amount>0){
                    $transaction['message']='Order amount refund';
                    $transaction['amount']=$request->refund_amount;
                    $transaction['company_id']=$Dispute->company_id;
                    $transaction['id']=$Dispute->user_id;
                    (new Transactions)->disputeCreditDebit($transaction);
                }

           } 
            else if($request->admin_status=='reorder')
            {
                 $storeorder->status ='ORDERED'; 
                  $storeorder->save();
                  $storeorderstatus = StoreOrderStatus::where('status','ORDERED')->where('store_order_id',$storeorder->id)->first();
                  $storeorderstatus->updated_at = \Carbon\Carbon::now();
                  $storeorderstatus->save();

            } 
            else if($request->admin_status=='resign')
            {
              $userrequest= UserRequest::where('request_id',$Dispute->store_order_id)->where('admin_service',$storeorder->admin_service)->where('company_id',Auth::user()->company_id)->first();
              $userrequest->status='ACCEPTED';
              $userrequest->provider_id=$request->provider_id;
              $userrequest->save();
                 
                $Filter = new RequestFilter;
                $Filter->admin_service = $storeorder->admin_service;
                $Filter->request_id = $userrequest->id;
                $Filter->provider_id = $request->provider_id; 
                $Filter->company_id = $storeorder->company_id; 
                $Filter->save();


                Provider::where('id',$request->provider_id)->update(['is_assigned'=>1]);

                $storeorder->status ='PROCESSING'; 
                $storeorder->provider_id =$request->provider_id; 
                $storeorder->save();
                // Send Push Notification to Provider
                (new SendPushNotification)->ProviderAssign($request->provider_id, 'order');
            }

                $requestData = ['type' => 'ORDER', 'room' => 'room_'.$storeorder->company_id, 'id' => $storeorder->id,'shop'=> $storeorder->store_id, 'user' => $storeorder->user_id ];
                  app('redis')->publish('newRequest', json_encode( $requestData ));

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        /*} 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }*/
    }

  

    public function dispute_list(Request $request)
    {
        $this->validate($request, [
            'dispute_type' => 'required'         
        ]);

        $dispute = Dispute::select('dispute_name')->where('dispute_type' , $request->dispute_type)->where('service','ORDER')->where('status' , 'active')->get();

        return $dispute;
    }

    public function searchOrderDispute(Request $request)
    {
        $results=array();
        if($request->input('sflag')==1){            
            $queries = StoreOrder::where('provider_id', $request->id)->with('store')->orderby('id', 'desc')->take(10)->get();
        }else{
            $queries = StoreOrder::where('user_id', $request->id)->with('store')->orderby('id', 'desc')->take(10)->get();
        }
        foreach ($queries as $query)
        {
            $RequestDispute = StoreOrderDispute::where('store_order_id',$query->id)->first();
            if(!$RequestDispute){
                $results[]=$query;
            }
        }
        return response()->json(array('success' => true, 'data'=>$results));
    }


    public function findprovider($id)
    {
      try{
         $store = Store::findOrFail($id);
         $settings = json_decode(json_encode(Setting::where('company_id',$store->company_id)->first()->settings_data));
         $orderConfig = $settings->order;
            $distance = isset($orderConfig->store_search_radius) ? $orderConfig->store_search_radius : 100;
            $latitude = $store->latitude;
            $longitude = $store->longitude;
            $Providers = Provider::with('service');
            $Providers->select(DB::Raw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id','first_name','last_name');//6371
            $Providers->where('status', 'APPROVED');
            $Providers->where('is_online', 1);
            $Providers->where('is_assigned', 0);
            $Providers->whereRaw("(3959 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");//6371
            $Providers->whereHas('service', function($query) use ($store) {
                $query->where('admin_service', 'ORDER');
                $query->where('category_id', $store->store_type_id);
            });
            $Providers->where('city_id', $store->city_id);
            $Providers->orderBy('distance','asc');
            $Providers = $Providers->get(); 

        return response()->json(array('success' => true, 'data'=>$Providers));
      }catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    
}

