<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Exception;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    //
    public function getAllData(Request $request)
    {
        try{
            // get data categories , blogs, and category that related and where category.name like %
            $categories = BlogCategory::with(['blogs' => function($query){
                $query->with(['blog_gallery'])->orderBy('created_at', 'desc');
            }])->where('name', 'like', '%' . $request->name . '%')->get();
            return ResponseFormatter::success([
                'categories' => $categories,
            ], 'Data Found');
            // $dataCategories = BlogGallery::with(['photo,blog.blog_category'])->limit(6)->get();  // summer, winter, etc.
            // $dataGaller
        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }
}
