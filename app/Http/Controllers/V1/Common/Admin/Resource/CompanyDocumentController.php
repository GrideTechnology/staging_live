<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\Actions;
use App\Models\Common\CompanyDocument;
use App\Helpers\Helper;
use Auth;

class CompanyDocumentController extends Controller
{
    use Actions;
    private $model;
    private $request;
    
    public function __construct(CompanyDocument $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        

        $datum = CompanyDocument::where('deleted_at', NULL);  

        //$all = CompanyDocument::where('status', 1)->get()->toArray();

        $documents = $datum->get()->toArray();

        $document_list = $documents;

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
             'file.*' => 'mimes:jpg,jpeg,png,pdf,mp4,mov,ogg,qt'
        ]);

        try{
            $document = new CompanyDocument;
            $document->name = $request->name; 
            $document->file_type = $request->file_type; 

            if ($request->hasFile('picture')) {
                $document->file = Helper::upload_file($request->file('picture'), 'provider/documents');
            }

            if ($request->hasFile('pdf')) {
                $document->file = Helper::upload_file($request->file('pdf'), 'provider/documents');
            }

            if ($request->hasFile('video')) {
                $document->file = Helper::upload_file($request->file('video'), 'provider/documents');
            }

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
            $document = CompanyDocument::findOrFail($id);
            return Helper::getResponse(['data' => $document]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404,'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'file.*' => 'mimes:jpg,jpeg,png,pdf,mp4,mov,ogg,qt'
        ]);
    
        try {
            CompanyDocument::where('id',$id)->update([
                    'name' => $request->name,
                ]);
            
            $document = CompanyDocument::where('id',$id)->first();
            $document->name = $request->name;     
            $document->file_type = $request->file_type; 
            
            if ($request->hasFile('picture')) {
                $document->file = Helper::upload_file($request->file('picture'), 'provider/documents');
            }

            if ($request->hasFile('pdf')) {
                $document->file = Helper::upload_file($request->file('pdf'), 'provider/documents');
            }

            if ($request->hasFile('video')) {
                $document->file = Helper::upload_file($request->file('video'), 'provider/documents');
            }
            
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

            $datum = CompanyDocument::findOrFail($id);
            
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
