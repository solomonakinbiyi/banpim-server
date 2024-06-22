<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            if ($user) {
                return ResponseHelper::success(message: 'User has been registered successfully!', data: $user, statusCode: 201);
            }

            return ResponseHelper::error(message: 'Unable to register user. Please try again!', statusCode: 400);
        } catch (Exception $e) {
            Log::error('Unable to register user : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to register user. Please try again!', statusCode: 500);
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request)
    {
        try {
            if (!Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])) {
                return ResponseHelper::error(message: 'Invalid login credentials!', statusCode: 400);
            }

            /** @var \App\Models\User */
            $user = Auth::user();

            // create token
            $token = $user->createToken('My API Token')->plainTextToken;

            $authUser = [
                'user' => $user,
                'token' => $token
            ];

            return ResponseHelper::success(message: 'User logged in successfully!', data: $authUser, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to login user : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to login user. Please try again!', statusCode: 500);
        }
    }

    public function userProfile()
    {
        try {
            /** @var \App\Models\User */
            $user = Auth::user();

            if ($user) {
                return ResponseHelper::success(message: 'User profile fetched successfully!', data: $user, statusCode: 200);
            }

            return ResponseHelper::error(message: 'Invalid token credentials!', statusCode: 401);
        } catch (Exception $e) {
            Log::error('Unable to fetch user : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to fetch user. Please try again!', statusCode: 500);
        }
    }

    public function logout()
    {
        try {
            /** @var \App\Models\User */
            $user = Auth::user();

            if ($user) {
                $user->currentAccessToken()->delete();

                return ResponseHelper::success(message: 'User logged out successfully!', statusCode: 200);
            }

            return ResponseHelper::error(message: 'Invalid token credentials!', statusCode: 401);
        } catch (Exception $e) {
            Log::error('Unable to fetch user : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to logout user. Please try again!', statusCode: 500);
        }
    }
}
