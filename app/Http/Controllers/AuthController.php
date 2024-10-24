<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
/* 
    * Follow the tutorial https://youtu.be/LmMJB3STuU4?si=0k87IUuK6CQPGPzi
    * Laravel 11 API CRUD app with Authentication using Laravel Sanctum course 2024 | Part 1/2
*/
class AuthController extends Controller
{
    // Registration Method
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }
    
    // Login Method
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    // Logout method
    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        // Revoke the current user's all token
        //$request->user()->token()->delete();

        return response()->json([
            'success' => 'Logged out successfully.'
        ], 200);
    }
}