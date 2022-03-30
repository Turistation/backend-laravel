<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\BlogGallery;
use App\Models\Visitor;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yish\Imgur\Facades\Upload as Imgur; 


class BlogController extends Controller
{
    //untuk di detail page
    public function getDetailBlog(Request $request)
    {
        try{
            //get relation
            $blog = Blog::with(['blog_category', 'admin_blog', 'blog_gallery.photo'])->findOrFail($request->route('id'));

            if(!$blog) {
                return ResponseFormatter::error([
                    'message' => 'Blog not found',
                ], 'Blog Not Found', 404);
            }

            return ResponseFormatter::success([
                'blog' => $blog,
            ], 'Blog Found');
        } catch(Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 500);
        }
    }


    // untuk admin create blog.
    public function createBlog(Request $request)
    {
        try{
            $request->validate(
                [
                    'title' => ['required', 'string', 'max:255'],
                    'description' => ['required', 'string', 'max:255'],
                    'blog_categories_id' => ['required', 'integer'],
                    'image' => ['required'],
                ]
            );

            $blog = Blog::create([
                'title' => $request->title,
                'description' => $request->description,
                'blog_categories_id' => $request->blog_categories_id,
                'admins_id' => Auth::user()->id,
            ]);

            $imgData = Imgur::upload($request->image);
            $data = Photo::create([
                'photos' => $imgData->link(),
            ]);

            BlogGallery::create([
                'blogs_id' => $blog->id,
                'photos_id' => $data->id,
            ]);

            return ResponseFormatter::success([
                'blog' => $blog,
            ], 'Blog Created');
        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Created', 500);
        }
    }


    // untuk admin delete blog.
    public function deleteBlog(Request $request)
    {
        try{
            $data = Blog::findOrFail($request->route('id'));
            $data->delete();
            return ResponseFormatter::success([], 'Blog Deleted');
        } catch (Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }

    // untuk delete blog
    public function editBlog(Request $request)
    {
        try{
            $request->validate(
                [
                    'title' => ['required', 'string', 'max:255'],
                    'description' => ['required', 'string', 'max:255'],
                    'blog_categories_id' => ['required', 'integer']
                ]
            );
            $data = $request->all();
            $blog = Blog::findOrFail($request->route('id'));
            if(isset($blog)){
                $blog->update($data);

                if(isset($request->image)){
                    $imgData = Imgur::upload($request->image);
                    $data = Photo::create([
                        'photos' => $imgData->link(),
                    ]);
                    BlogGallery::create([
                        'blogs_id' => $blog->id,
                        'photos_id' => $data->id,
                    ]);

                    return ResponseFormatter::success([
                        'blog' => $blog,
                    ], 'Blog Updated with image and galleries.');
                }

                return ResponseFormatter::success([
                    'blog' => $blog,
                ], 'Blog Updated');
            } else {
                return ResponseFormatter::error([
                    'message' => 'Blog not found',
                ], 'Blog Not Found', 404);
            }
        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }

    // sum total visitor
    public function sumTotalVisitor(Request $request)
    {
        try{
            $data = Visitor::updateOrCreate([
                'blogs_id' => $request->route('id'),
            ], [
                'total_view' => $request->total_view + 1,
            ]);

            return ResponseFormatter::success([
                'data' => $data,
            ], 'Total Visitor Updated');
        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Total Visitor Not Updated', 500);
        }
    }

    // get recent data for admin dashboard
    public function getRecentDataBlog(Request $request)
    {
        try{
            $blogs = Blog::with(['blog_category', 'admin_blog', 'blog_gallery.photo'])->orderBy('created_at', 'desc')->limit(5)->get();
            $totalBlog = Blog::count();
            $totalVisitor = Visitor::count();
            return ResponseFormatter::success([
                'blogs' => $blogs,
                'total_blog' => $totalBlog,
                'total_visitor' => $totalVisitor,
            ], 'Recent Blogs');
        } catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }
}
