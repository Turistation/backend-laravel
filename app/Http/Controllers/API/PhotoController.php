<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yish\Imgur\Facades\Upload as Imgur;
use App\Models\Photo;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    //

    public function uploadPhoto(Request $request)
    {
        $rules = [
            'category_id' => ['required', 'integer'],
            'images' => ['required'],
            'images.*' => ['image', 'mimes:jpg,png,jpeg,gif,svg']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
        }

        if (!$request->hasFile('images')) {
            return ResponseFormatter::error([
                'message' => 'Images not found',
            ], 'Images Not Found', 422);
        }

        try {
            $images = $request->file('images');

            DB::beginTransaction();
            $data = [];
            if (is_array($images)) {
                foreach ($images as $image) {
                    $imgurData = Imgur::upload($image);
                    $data[] = Photo::create([
                        'photos' => $imgurData->link(),
                        'category_id' => $request->input('category_id'),
                    ]);
                }
            } else {
                $imgurData = Imgur::upload($images);
                $data[] = Photo::create([
                    'photos' => $imgurData->link(),
                    'category_id' => $request->input('category_id'),
                ]);
            }
            DB::commit();

            return ResponseFormatter::success([
                'message' => 'Photos uploaded successfully',
                'photos' => $data,
            ], 'Photos Uploaded');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Photos Not Uploaded', 500);
        }
    }

    public function getAllPhotos(Request $request)
    {
        try {
            $categoryIdsString = $request->query('category_id');
            $categoryIds = explode(',', $categoryIdsString);
            if ($request->query('category_id')) {
                $photos = Photo::with(['blog_category', 'blogs' => function ($q) {
                    $q->select('title');
                }])->whereIn('category_id', $categoryIds)->paginate(10);
            } else {
                $photos = Photo::with(['blog_category', 'blogs' => function ($q) {
                    $q->select('title');
                }])->paginate(10);
            }
            return ResponseFormatter::success([
                'message' => 'Photos fetched successfully',
                'photos' => $photos,
            ], 'Photos Fetched');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Photos Not Fetched', 500);
        }
    }

    public function getRecentPhoto()
    {
        try {
            $photos = Photo::with(['blog_category', 'blogs' => function ($q) {
                $q->select('title');
            }])->orderBy('created_at', 'desc')->limit(4)->get();
            return ResponseFormatter::success([
                'message' => 'Photos fetched successfully',
                'photos' => $photos,
            ], 'Photos Fetched');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Photos Not Fetched', 500);
        }
    }

    public function getAllPhotoWithoutPaginate()
    {
        try {
            $photos = Photo::all();
            return ResponseFormatter::success([
                'message' => 'Photos fetched successfully',
                'photos' => $photos,
            ], 'Photos Fetched');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Photos Not Fetched', 500);
        }
    }
}
