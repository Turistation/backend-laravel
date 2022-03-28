<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    //

    public function getDetailBlog(Request $request, $id)
    {
        try{
            //get relation
            $blog = Blog::with(['blog_category', 'admin_blog', 'blog_gallery.photo'])->findOrFail($id);

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
}
