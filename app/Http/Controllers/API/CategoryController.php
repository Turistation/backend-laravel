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
        try {
            $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                ]
            );
            $data = $request->all();
            $category = BlogCategory::create($data);
            return ResponseFormatter::success([
                'category' => $category,
            ], 'Category Created');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function deleteCategory(Request $request)
    {
        try {

            $category = BlogCategory::findOrFail($request->route('id'));
            $category->delete();
            return ResponseFormatter::success([
                'message' => 'Category Deleted',
            ], 'Category Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function editCategory(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                ]
            );
            $data = $request->all();
            $category = BlogCategory::find($request->route('id'));
            $category->name = $data['name'];
            $category->save();
            return ResponseFormatter::success([
                'message' => 'Category Edited',
            ], 'Category Edited');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function showCategory()
    {
        try {
            $categories = BlogCategory::all();
            return ResponseFormatter::success([
                'categories' => $categories,
            ], 'Categories Found');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function showCategoryById(Request $request)
    {
        try {
            $category = BlogCategory::findOrFail($request->route('id'));
            return ResponseFormatter::success([
                'category' => $category,
            ], 'Category Found');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }
}
