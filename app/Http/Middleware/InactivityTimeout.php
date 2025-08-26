<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InactivityTimeout
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if(!$user) return $next($request);

        $token = $user->currentAccessToken();
        if(!$token) return $next($request);

        $last = $token->last_used_at ?? $token->created_at;
        if($last && now()->diffInMinutes($last) >= 5){
            $token->delete(); // revoke
            return response()->json(['message'=>'Session expired due to inactivity'], 440);
        }

        return $next($request);
    }
}
