<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\Actions;
use App\Models\Common\Document;
use App\Helpers\Helper;
use Auth;

class DocumentController extends Controller
{
    use Actions;
    private $model;
    private $request;
    
    public function __construct(Document $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        

        $datum = Document::whereHas('service' ,function($query){  
            $query->where('status',1);  
        })->where('company_id', Auth::user()->company_id)->where('status', 1);

        $all = Document::where('service' ,'ALL')->where('company_id', Auth::user()->company_id)->where('status', 1)->get()->toArray();

        $documents = $datum->get()->toArray();

        $document_list = array_merge($all, $documents);

        if($request->has('search_text') && $request->search_text != null) {
            $datum->Search($request->search_text);
        }

        if($request->has('order_by')) {
            $datum->orderby($request->order_by, $request->order_direction);
        }
        
        if($request->has('page') && $request->page == 'all') {
            $data = $document_list;
        } else {
            $data = $this->paginate($document_list);
        }
        
        return Helper::getResponse(['data' => $data]);
    }

    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }

    public function store(Request $request)
    {
        $this->validate($request, [
             'name' => 'required|max:255',
             'type' => 'required|in:TRANSPORT,ORDER,SERVICE,ALL',
        ]);

        try{
            $document = new Document;
            $document->name = $request->name;  
            $document->company_id = Auth::user()->company_id;                    
            $document->type = $request->type;
            $document->service = $request->type;   
            $document->file_type = $request->file_type;
            $document->is_backside = $request->is_backside;
            $document->number_of_file = $request->number_of_file;
            $document->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $document = Document::findOrFail($id);
            return Helper::getResponse(['data' => $document]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'type' => 'required|in:TRANSPORT,ORDER,SERVICE,ALL',
        ]);
    
        try {
            Document::where('id',$id)->update([
                    'name' => $request->name,
                    'type' => $request->type,
                ]);
            
            $document = Document::where('id',$id)->first();
            $document->name = $request->name;                   
            $document->type = $request->type;  
            $document->service = $request->type;  
            $document->file_type = $request->file_type;
            if($request->has('is_backside')){
                $document->is_backside = $request->is_backside;
            }else{
                $document->is_backside = null;
            }

            $document->number_of_file = $request->number_of_file;
            
            $document->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
            } catch (\Throwable $e) {
                return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
            }
    }

    public function destroy($id)
    {
        return $this->removeModel($id);
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {

            $datum = Document::findOrFail($id);
            
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
