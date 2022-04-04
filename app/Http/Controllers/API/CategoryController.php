<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use Exception;
use App\Helpers\ResponseFormatter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //
    public function createCategory(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }

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
            $rules = [
                'name' => ['required', 'string'],
            ];

            $category = BlogCategory::findOrFail($request->route('id'));

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }
            $data = $request->all();

            $category->name = $data['name'];
            $category->save();
            return ResponseFormatter::success([
                'message' => 'Category Edited',
            ], 'Category Edited');
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error([
                'message' => 'category not found',
                'error' => $e->getMessage(),
            ], 'Category Not Found', 404);
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
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error([
                'message' => 'category not found',
                'error' => $e->getMessage(),
            ], 'Category Not Found', 404);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }
}
