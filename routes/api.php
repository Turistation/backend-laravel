<?php

use App\Http\Controllers\API\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;



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
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/blogs', [BlogController::class, 'createBlog']);
    Route::get('/admins', [UserController::class, 'getUserData']);
    Route::post('/admin/categories', [CategoryController::class, 'createCategory']);
    Route::get('/admin/getrecentblog', [BlogController::class, 'getRecentDataBlog']);
});
Route::get('/blog/{id}', [BlogController::class, 'getDetailBlog']);

// admin routes
Route::post('/admin/registers', [UserController::class, 'register']);
Route::post('/admin/logins', [UserController::class, 'login']);
Route::post('/admin/logouts', [UserController::class, 'logout']);
Route::get('/admin/blogs', [BlogController::class, 'getRecentAddedBlog']);

// blog exception to count total visitor when visited detail blog.
Route::post('/blog/exceptions/{id}', [BlogController::class, 'sumTotalVisitor']);

