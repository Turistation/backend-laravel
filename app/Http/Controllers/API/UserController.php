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
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255', 'email'],
                'password' => ['required', 'string', new Password],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }

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
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }


            if (Auth::guard()->attempt($request->only('email', 'password'))) {
                $request->session()->regenerate();

                return ResponseFormatter::success([], 'user successfully logged in');
            }

            return ResponseFormatter::error([
                'message' => 'invalid email or password',
            ], 'Authentication Error', 401);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Error', 500);
        }
    }

    public function getUserData()
    {
        try {
            $user = Auth::user();
            return ResponseFormatter::success([
                'user' => $user,
            ], 'User Data');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Error', 500);
        }
    }

    public function editProfile(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255', 'email'],
                'password' => ['required', 'string', new Password],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }


            $data = $request->all();
            $user = Auth::user();
            $user->update($data);

            return ResponseFormatter::success([
                'user' => $user
            ], 'User Data');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function verifyLogin()
    {
        return response()->json([], 204);
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return ResponseFormatter::success([], 'user successfully logged out');
    }
}
