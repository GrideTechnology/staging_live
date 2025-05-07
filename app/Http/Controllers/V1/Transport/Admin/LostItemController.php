<?php

namespace App\Http\Controllers\V1\Transport\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\Actions;
use App\Helpers\Helper;
use App\Models\Transport\RideLostItem;

use Illuminate\Support\Facades\Storage;
use Auth;

class LostItemController extends Controller
{
    use Actions;

    private $model;
    private $request;

    public function __construct(RideLostItem $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
    	$datum = RideLostItem::where('company_id', Auth::user()->company_id)->with(['user','riderequests']);

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
            'user_id' => 'required',           
            'lost_item_name' => 'required',           
        ]);

        try{

            $LostItem = new RideLostItem;
            $LostItem->company_id = Auth::user()->company_id;
            $LostItem->ride_request_id = $request->request_id;
            $LostItem->user_id = $request->user_id;                    
            $LostItem->lost_item_name = $request->lost_item_name;

            if($request->has('comments')) {
                $LostItem->comments = $request->comments;
            }    

            if($request->has('status')) {    
                $LostItem->status = $request->status;
            }    

            if($request->has('is_admin')) {                   
                $LostItem->is_admin = $request->is_admin;
                $LostItem->comments_by = 'admin';
            }
            
            if($request->has('comments_by')) {
                $LostItem->comments_by = $request->comments_by;
            }

            $LostItem->save();

                return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
           } 
           catch (\Throwable $e) 
           {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
           }
     }


    public function show($id)
    {
        try {
            $RideLostItem = RideLostItem::findOrFail($id);
            return Helper::getResponse(['data' => $RideLostItem]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
       
        $this->validate($request, [
            'comments' => 'required',
        ]);

        try {

            $LostItem = RideLostItem::findOrFail($id);
           if($LostItem){
            if($request->has('comments')) {
                $LostItem->comments = $request->comments;
            }
           
            if($request->has('status')) {    
                $LostItem->status = $request->status;
            }    

            $LostItem->save();

                    return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
                }
                else
                {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.not_found')]); 
                }
            } 
            catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }
  

    public function destroy($id)
    {
        return $this->removeModel($id);
    }

    
}

