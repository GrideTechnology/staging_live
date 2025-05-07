<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Transport\RideRequestDispute;
use App\Models\Common\Dispute;
use App\Models\Transport\RideRequest;
use App\Services\Transactions;
use Carbon\Carbon;



use Illuminate\Support\Facades\Storage;
use Auth;

class RideRequestDisputeController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(RideRequestDispute $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datum = RideRequestDispute::where('company_id', Auth::user()->company_id)->with('user','provider','request');
                                   

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
            $Dispute = new RideRequestDispute();
            $Dispute->company_id = Auth::user()->company_id; 
            $Dispute->ride_request_id = $request->request_id;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->user_id = $request->user_id;
            $Dispute->provider_id = $request->provider_id;
            $Dispute->dispute_name = $request->dispute_name;
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
            $RideRequestDispute = RideRequestDispute::with('user','provider','request')->findOrFail($id);
            return Helper::getResponse(['data' => $RideRequestDispute]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
      
        $this->validate($request, [            
            'comments' => 'required', 
            'status' => 'required',        
        ]);

        try{

            $Dispute = RideRequestDispute::findOrFail($id);
            $Dispute->comments = $request->comments;                    
            $Dispute->refund_amount = $request->refund_amount;
            $Dispute->status = 'closed';                    
            $Dispute->save();

             if($request->refund_amount>0){
                $transaction['message']=trans('admin.dispute_manager_msgs.transport_refund');
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

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        return $this->removeModel($id);
    }
  

    public function dispute_list(Request $request)
    {
        $this->validate($request, [
            'dispute_type' => 'required'         
        ]);

        $dispute = Dispute::select('dispute_name')->where('dispute_type' , $request->dispute_type)->where('status' , 'active')->get();

        return $dispute;
    }

    public function dashboarddata($id)
    {
      try{
          $completed= RideRequest::where('country_id',$id)->where('status','COMPLETED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
          });

          $cancelled= RideRequest::where('country_id',$id)->where('status','CANCELLED')->where('company_id',Auth::user()->company_id)->get(['id', 'created_at','timezone'])->groupBy(function($date) {
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

          $overall= RideRequest::where('country_id',$id)->where('status','COMPLETED')->where('company_id',Auth::user()->company_id)->count();

          $data['cancelled_data']=$cancel;
          $data['completed_data']=$complete;
          $data['max']=max($complete);
          $data['overall']= $overall;
          if(max($complete) < max($cancel)){
            $data['max']=max($cancel);
          }
          
          
          return Helper::getResponse(['status' => 200,'data'=> $data]);

         }
         catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
      
   } 
    
}

