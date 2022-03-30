<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        try{
            $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'max:255', 'email'],
                    'password' => ['required', 'string', new Password],
                ]
            );

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenData = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenData,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User Teregistrasi');
        } catch(Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function login(Request $request)
    {
        try{
            $request->validate(
                [
                    'email' => ['required', 'email'],
                    'password' => ['required'],
                ]
            );

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return ResponseFormatter::error([
                    'message' => 'User not found',
                ], 'Authentication Error', 404);
            }

            if(!Hash::check($request->password, $user->password)) {
                return ResponseFormatter::error([
                    'message' => 'Password not match',
                ], 'Authentication Error', 401);
            }

            $tokenData = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenData,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User Login');
        } catch(Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Error', 500);
        }
    }

    public function getUserData()
    {
        try{
            $user = Auth::user();
            return ResponseFormatter::success([
                'user' => $user,
            ], 'User Data');
        } catch(Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Error', 500);
        }
    }

    public function editProfile(Request $request)
    {
        try
        {
            $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'max:255', 'email'],
                    'password' => ['required', 'string', new Password],
                ]
            );

            $data = $request->all();
            $user = Auth::user();
            $user->update($data);

            return ResponseFormatter::success([
                'user' => $user
            ], 'User Data');

        }catch(Exception $e)
        {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function logout()
    {
        try{
            auth()->user()->tokens()->delete();
            return ResponseFormatter::success([], 'User Logout');
        } catch(Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Error', 500);
        }
    }
}
