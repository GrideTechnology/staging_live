<?php

namespace App\Http\Controllers\V1\Common\Admin\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Post;
use App\Helpers\Helper;

class BlogController extends Controller
{
    //
    public function index(Request $request)
    {
        $datum = Post::where('deleted_at',null);

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

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'time' => 'required',
            'video' => 'url',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {
            $blog = new Post();
            $blog->title=$request->title;
            $blog->content=$request->content;
            $blog->time=$request->time;
            $blog->slug=str_slug($request->title);
            $blog->video=$request->video;
            $blog->save();
            if($request->hasFile('picture')) {
                $blog->image = Helper::upload_file($request->file('picture'), 'blogimages', $blog->id.'.png');
            }
            $blog->save();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.create')]);

        } 
        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'time' => 'required',
            'video' => 'url',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $blog = Post::findOrFail($id);
            $blog->title=$request->title;
            $blog->content=$request->content;
            $blog->time=$request->time;
            $blog->slug=str_slug($request->title);
            $blog->video=$request->video;
            if($request->hasFile('picture')) {
                $blog->image = Helper::upload_file($request->file('picture'), 'blogimages', $blog->id.'.png');
            }
            $blog->save();

            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } 

        catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            $blog = Post::findOrFail($id);
            $blog->delete();
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.delete')]);
        }
        catch(Exception $e)
        {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $blog = Post::findOrFail($id);
            return Helper::getResponse(['status' => 200, 'data' => $blog]);
        }
        catch(Exception $e)
        {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }


}
