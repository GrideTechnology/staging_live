<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Common\Setting;
use App\Models\Common\AuthLog;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Auth;
use DB;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Models\Common\Zone;
use App\Models\Common\PayrollTemplate;
use App\Models\Common\Payroll;
use App\Models\Common\Provider;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollsExport;
class PayrollController extends Controller
{
    use Actions;
    use Encryptable;

    private $model;
    private $request;

    public function __construct(Payroll $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
    	$datum = Payroll::where('company_id',Auth::user()->company_id);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }
        $datum->groupBy('transaction_id');

        
        if($request->has('page') && $request->page == 'all') {
            $data = $datum->get();
        } else {
            $data = $datum->paginate(10);
        }

        return Helper::getResponse(['data' => $data]);
    }

    public function store(Request $request)
    {

        $messages = [                    
            'pid.*' => 'checked atleast anyone checkbox',
            'wallet.*' => 'wallet field is required '
        ];
        $this->validate($request, [
            'pid.*' => 'required',
            'wallet.*' => 'required'
        ],$messages);

        try{

            $type = Zone::find($request->zone_id)->user_type;

            $transaction_id = 'GOPAY'.time();
            $company_id = Auth::user()->company_id;
            $providers = $request->pid;
            $wallets = $request->wallet;
            $zones = $request->zones;
            foreach($providers as $key => $val){

                $transac = [
                    'transaction_id' => $transaction_id,
                    'company_id' => $company_id,
                    'zone_id' => $request->zone_id,
                    'payroll_type' => '',
                    'provider_id' => null,
                    'shop_id' => null,
                    'fleet_id' => null,
                    'wallet' => $wallets[$val],
                    'type' => $type,
                    'admin_service' => ''
                ];

                if($request->type=='manual'){
                    $transac['payroll_type'] = 'MANUAL';
                }else{
                    $transac['payroll_type'] = 'ZONE';
                }

                if($type == 'SHOP'){
                    $transac['shop_id'] = $val;
                }elseif($type == 'FLEET'){
                    $transac['fleet_id'] = $val;
                }else{
                    $transac['provider_id'] = $val;
                }

                Payroll::create($transac); 
            }
            //$zone = $request->all();
            $returndata = Payroll::where('company_id',Auth::user()->company_id)->paginate(10);; 
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create'),'data'=>$returndata]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

    }
     public function show($id)
    {
        try {
            $payroll_item_list = Payroll::with('provider')->where('transaction_id',$id)->get();
           //$zone['city_data']=CompanyCity::where("city_id",$zone['city_id'])->with('city')->get();
           //$zone['country_data']=CompanyCountry::where("company_id",$zone['company_id'])->with('country')->get();

            return Helper::getResponse(['data' => $payroll_item_list]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'provider.*' => 'required',
            'wallet.*' => 'required'
        ]);
        try {

            $type = Zone::find($request->zone_id)->user_type;

            $company_id = Auth::user()->company_id;
            $providers = $request->proid;
            $all_records = $request->pid;
            $wallets = $request->wallet;
            $zones = $request->zones;
            Payroll::where('transaction_id',$id)->delete(); 
            foreach($all_records as $key => $val){
                if($request->type=='manual'){
                    $transac = [
                        'transaction_id' => $id,
                        'company_id' => $company_id,
                        'template_id' => $request->template_id,
                        'provider_id' => $providers[$val],
                        'wallet' => $wallets[$val],
                        'payroll_type' => 'MANUAL',
                        'type' => $type,
                        'admin_service' => ''
                    ];
                }else{
                    $transac = [
                        'transaction_id' => $id,
                        'company_id' => $company_id,
                        'zone_id' => $zones[$val],
                        'provider_id' => $providers[$val],
                        'wallet' => $wallets[$val],
                        'payroll_type' => 'ZONE',
                        'type' => $type,
                        'admin_service' => ''
                    ];
                }
                Payroll::create($transac); 
            }
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updatePayroll(Request $request)
    {
        
        try {

            $payroll = Payroll::findOrFail($request->id);
            $payroll->status = $request->status;
            $payroll->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = PayrollTemplate::findOrFail($id);
            if($request->status=='ACTIVE'){
            	$datum->status = 'INACTIVE';
            }else{
            	$datum->status = 'ACTIVE';
        	}
            
            $datum->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.activation_status')]);

        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        
        try{
            $datum = Payroll::where('transaction_id',$id)->delete();

            //$model->delete();
            return Helper::getResponse(['message' => trans('admin.user_msgs.user_delete')]);
        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.user_msgs.user_not_found'), 'error' => $e->getMessage()]);
        }
    }

    public function zoneprovider(Request $request,$id, $type){
        
        if($type == 'shop') {            
            $data = \App\Models\Order\Store::where('zone_id',$id)->where('wallet_balance','>',1)
            ->select('store_name AS name','id','status','zone_id','wallet_balance');
        } else if($type == 'fleet') {
            $data = \App\Models\Common\Admin::where('zone_id',$id)->where('wallet_balance','>',1)->where('type','FLEET')
            ->select('name','id','status','zone_id','wallet_balance');
        } else {
            $data = Provider::where('zone_id',$id)->where('wallet_balance','>',1)
            ->select( \DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id','status','zone_id','wallet_balance');
        }

        $providers = $data->get();              
        return Helper::getResponse(['data' => $providers]);
    }

    public function PayrollDownload(Request $request,$id){
            $data = Payroll::with(['provider','store','fleet','bankShopDetails','bankShopDetails.title','bankFleetDetails','bankFleetDetails.title','bankProviderDetails','bankProviderDetails.title'])->where('transaction_id',$id)->select('id','transaction_id','type','provider_id','shop_id','fleet_id','wallet')->get();

            $i = 1;

            $data->map(function($payroll) use($i) { 
                $payroll->id = $i;
                    if($payroll->type == 'SHOP') {
                        $payroll->name = $payroll->store->store_name;
                    } else if($payroll->type == 'FLEET') {
                        $payroll->name = $payroll->fleet->name;
                    } else {
                        $payroll->name = $payroll->provider->first_name.' '.$payroll->provider->last_name;
                    }
                    $payroll->amount = $payroll->wallet;

                    foreach($payroll->bankShopDetails as $val){
                        $payroll[$val->title->label] = $val->keyvalue;
                    }
                    foreach($payroll->bankFleetDetails as $val){
                        $payroll[$val->title->label] = $val->keyvalue;
                    }
                    foreach($payroll->bankProviderDetails as $val){
                        $payroll[$val->title->label] = $val->keyvalue;
                    }


                    unset($payroll->provider);
                    unset($payroll->bankShopDetails);
                    unset($payroll->bankFleetDetails);
                    unset($payroll->bankProviderDetails);
                    unset($payroll->provider_id);
                    unset($payroll->shop_id);
                    unset($payroll->fleet_id);
                    unset($payroll->store);
                    unset($payroll->fleet);
                    unset($payroll->type);
                    unset($payroll->wallet);
                    $i++;
                    return $payroll;
               
            });
            $filename = 'payroll'.'.xlsx';

            $headers = count($data) > 0 ? array_keys( (array)$data[0]->toArray() ) : [];
            Excel::store(new PayrollsExport($data, $headers), $filename, 'public');
            return redirect("storage/{$filename}");
    }
}
