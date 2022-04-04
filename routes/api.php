<?php

use App\Http\Controllers\API\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\PhotoController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/verifylogin', [UserController::class, 'verifyLogin']);
    Route::post('/admin/blogs', [BlogController::class, 'createBlog']);
    Route::get('/admin/blogs', [BlogController::class, 'getAllBlog']);
    Route::put('/admin/blogs/{id}', [BlogController::class, 'editBlog']);
    Route::get('/admins', [UserController::class, 'getUserData']);
    Route::post('/admin/categories', [CategoryController::class, 'createCategory']);
    Route::post('/admin/photos', [PhotoController::class, 'uploadPhoto']);
});
Route::get('/blog/{id}', [BlogController::class, 'getDetailBlog']);

// admin routes
Route::post('/admin/registers', [UserController::class, 'register']);
Route::post('/admin/logins', [UserController::class, 'login']);
Route::post('/admin/logouts', [UserController::class, 'logout']);

// comments post
Route::post('/comments', [CommentController::class, 'postComment']);

// blog controller without login
Route::get('/blogs', [BlogController::class, 'getAllBlog']);
Route::get('/blogs/recents', [BlogController::class, 'getRecentDataBlog']);
Route::get('/blogs/{id}', [BlogController::class, 'getDetailBlog']);


Route::get('/categories', [CategoryController::class, 'showCategory']);

Route::get('/photos', [PhotoController::class, 'getAllPhotos']); // sudah include query, tinggal tambahkan /photos?categoriy_id=1 etc.

// blog exception to count total visitor when visited detail blog.
Route::post('/blog/exceptions/{id}', [BlogController::class, 'sumTotalVisitor']);

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return "Cleared!";
});
