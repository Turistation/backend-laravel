<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yish\Imgur\Facades\Upload as Imgur;
use App\Models\Photo;
use App\Helpers\ResponseFormatter;
use Exception;


class PhotoController extends Controller
{
    //

    public function uploadPhoto(Request $request)
    {
        $request->validate(
            [
                'photos' => ['required'],
            ]
        );

        try {
            $photos = $request->photos;
            $uploadedPhotos = [];
            foreach ($photos as $photo) {
                $imgData = Imgur::upload($photo);
                array_push($uploadedPhotos, $imgData->link());
            }

            DB::beginTransaction();
            foreach ($uploadedPhotos as $uploadedPhoto) {
                $data = Photo::create([
                    'photos' => $uploadedPhoto,
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
}
