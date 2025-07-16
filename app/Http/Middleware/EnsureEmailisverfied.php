<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureEmailisverfied
{
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();

       if ($user->user_type === 'patient' && !$user->is_email_verified) {
    return response()->json(['error' => 'Email not verified.'], 403);
}

        return $next($request);
    }
}
