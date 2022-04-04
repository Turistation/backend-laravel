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
                    ]);
                }
            } else {
                $imgurData = Imgur::upload($images);
                $data[] = Photo::create([
                    'photos' => $imgurData->link(),
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
            if ($request->query('category_id')) {
                $photos = Photo::where('category_id', $request->query('category_id'))->get();
            } else {
                $photos = Photo::all();
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

}
