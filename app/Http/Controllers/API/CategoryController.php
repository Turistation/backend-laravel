<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use Exception;
use App\Helpers\ResponseFormatter;

class CategoryController extends Controller
{
    //
    public function createCategory(Request $request)
    {
        try{
            $data = $request->all();
            $category = BlogCategory::create($data);
            return ResponseFormatter::success([
                'category' => $category,
            ], 'Category Created');
        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }

    }
}
