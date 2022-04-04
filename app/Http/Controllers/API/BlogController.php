<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\BlogGallery;
use App\Models\Visitor;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yish\Imgur\Facades\Upload as Imgur;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    //untuk di detail page FE
    public function getDetailBlog(Request $request)
    {
        try {
            //get relation
            $blog = Blog::with(['blog_category', 'admin_blog', 'blog_comments', 'photos'])->findOrFail($request->route('id'));

            return ResponseFormatter::success([
                'blog' => $blog,
            ], 'Blog Found');
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error([
                'message' => 'Blog not found',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 500);
        }
    }

    // sum total visitor
    public function sumTotalVisitor(Request $request)
    {
        try {
            $data = Visitor::updateOrCreate([
                'blogs_id' => $request->route('id'),
            ], [
                'total_view' => $request->total_view + 1,
            ]);

            return ResponseFormatter::success([
                'data' => $data,
            ], 'Total Visitor Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Total Visitor Not Updated', 500);
        }
    }

    // get all blog without login
    public function getAllBlogs(Request $request)
    {
        try {
            $blogs = Blog::with(['blog_category', 'admin_blog', 'photos'])->orderBy('created_at', 'desc')->paginate(10);
            return ResponseFormatter::success([
                'blogs' => $blogs,
            ], 'Blogs Found');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blogs Not Found', 500);
        }
    }



    //--------------------------//
    // ADMIN SIDE ////////////////
    // untuk admin create blog.
    public function createBlog(Request $request)
    {
        try {

            $rules = [
                'title' => ['required', 'string'],
                'description' => ['required', 'string'],
                'blog_categories_id' => ['required', 'integer'],
                'images.*' => ['image', 'mimes:jpg,png,jpeg,gif,svg']
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Validation Failed',
                    'error' => $validator->errors(),
                ], 'Validation Failed', 422);
            }

            DB::beginTransaction();
            $photos = [];
            if ($request->has('photos')) {
                foreach ($request->photos as $photo) {

                    $photos[] = [
                        "id" => $photo,
                    ];
                }
            } else {
                if (!$request->hasFile('images')) {
                    return ResponseFormatter::error([
                        'message' => 'Images not found',
                    ], 'Images Not Found', 422);
                }

                // if request image is array then , loop it and upload one by one
                $images = $request->file('images');


                foreach ($images as $image) {
                    $imgurData = Imgur::upload($image);
                    $data = Photo::create([
                        'photos' => $imgurData->link(),
                        'category_id' => $request->blog_categories_id,
                    ]);
                    $photos[] = $data;
                }
            }

            $createdBlog = Blog::create([
                'title' => $request->title,
                'description' => $request->description,
                'blog_categories_id' => $request->blog_categories_id,
                'admins_id' => Auth::user()->id,
            ]);
            foreach ($photos as $img) {
                $createdBlog->photos()->attach($img["id"]);
            }
            DB::commit();

            return ResponseFormatter::success([
                'blog' => $createdBlog,
            ], 'Blog Created');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Created', 500);
        }
    }


    // untuk admin delete blog.
    public function deleteBlog(Request $request)
    {
        try {
            $data = Blog::findOrFail($request->route('id'));
            $data->delete();
            return ResponseFormatter::success([], 'Blog Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }

    // untuk delete blog
    // belum benar perbaiki nanti
    public function editBlog(Request $request)
    {
        try {

            $rules = [
                'title' => ['required', 'string'],
                'description' => ['required', 'string'],
                'blog_categories_id' => ['required', 'integer'],
                'images.*' => ['image', 'mimes:jpg,png,jpeg,gif,svg']
            ];

            $data = $request->all();
            DB::beginTransaction();
            $blog = Blog::findOrFail($request->route('id'));

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Validation Failed',
                    'error' => $validator->errors(),
                ], 'Validation Failed', 422);
            }


            $blog->update($data);
            if ($request->has('photos')) {
                $photos = [];
                foreach ($request->photos as $photo) {
                    $photos[] = $photo;
                }
                $blog->photos()->sync($photos);
            }

            $imgur = [];
            if ($request->hasFile('images')) {
                // if request image is array then , loop it and upload one by one
                $images = $request->file('images');

                foreach ($images as $image) {
                    $imgurData = Imgur::upload($image);
                    $data = Photo::create([
                        'photos' => $imgurData->link(),
                        'category_id' => $request->blog_categories_id,
                    ]);
                    $imgur[] = $data;
                }
            }

            foreach ($imgur as $img) {
                $blog->photos()->attach($img["id"]);
            }
            DB::commit();

            return ResponseFormatter::success([
                'blog' => $blog,
            ], 'Blog Updated');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'blog not found',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Created', 500);
        }
    }

    // get recent data for admin dashboard
    public function getRecentDataBlog(Request $request)
    {
        try {
            $blogs = Blog::with(['blog_category', 'admin_blog', 'photos'])->orderBy('created_at', 'desc')->limit(6)->get();
            $totalBlog = Blog::count();
            // get visitor from model and pluck column views and count it
            $totalVisitor = Visitor::pluck('total_view')->count();
            return ResponseFormatter::success([
                'blogs' => $blogs,
                'total_blog' => $totalBlog,
                'total_visitor' => $totalVisitor,
            ], 'Recent Blogs');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }

    public function getAllBlog()
    {
        try {
            $blogs = Blog::with(['blog_category', 'admin_blog', 'photos'])->orderBy('created_at', 'desc')->get();
            return ResponseFormatter::success([
                'blogs' => $blogs,
            ], 'All Blogs');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Blog Not Found', 404);
        }
    }
}
