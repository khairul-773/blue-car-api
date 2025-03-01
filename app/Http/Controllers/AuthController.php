<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    use ResponseTrait;

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation errors', $validator->errors()->toArray(), 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_type' => 'Admin',
            ]);

            // Generate token
            $tokenResult = $user->createToken('API Token');
            $token = $tokenResult->plainTextToken;
            $expiryTime = now()->addDays(1); // Set token expiry time

            // Update expires_at column
            $user->tokens()->where('id', $tokenResult->accessToken->id)->update(['expires_at' => $expiryTime]);

            // Store expiry in cache
            Cache::put('token_expiry_' . $user->id, $expiryTime, $expiryTime);

            return $this->successResponse([
                'accessToken' => $token,
                'accessExpiresAt' => $expiryTime->toISOString(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->role_type,
                ],
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', $e->getMessage(), 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation errors', $validator->errors()->toArray(), 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid credentials', [], 401);
            }

            // Generate new token
            $tokenResult = $user->createToken('API Token');
            $token = $tokenResult->plainTextToken;

            // Set token expiry time (7 days from now)

            //$expiryTime = now()->addSecond(30); // Set token expiry time
            //Cache::put('token_expiry_' . $user->id, $expiryTime, 30);

            $expiryTime = now()->addDays(1);

            // Update expires_at column
            $user->tokens()->where('id', $tokenResult->accessToken->id)->update(['expires_at' => $expiryTime]);
            
            // Store expiry in cache
            Cache::put('token_expiry_' . $user->id, $expiryTime, $expiryTime);
            
            return $this->successResponse([
                'accessToken' => $token,
                'accessExpiresAt' => $expiryTime->toISOString(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->role_type,
                ],
            ], 'Sign-in successful', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', $e->getMessage(), 500);
        }
    }
    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logged out successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', $e->getMessage(), 500);
        }
    }
}
