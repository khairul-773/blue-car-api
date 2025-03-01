<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TokenExpiryMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get token expiry time from cache
        $expiryTime = Cache::get('token_expiry_' . $user->id);
        
        // If no expiry is set or the expiry time has passed, deny access
        if (!$expiryTime || now()->greaterThan($expiryTime)) {
            return response()->json(['message' => 'Unauthorized - Token expired'], 401);
        }

        return $next($request);
    }
}
