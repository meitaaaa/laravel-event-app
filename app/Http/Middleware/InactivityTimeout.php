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

        \Log::debug('InactivityTimeout middleware', [
            'user_id' => $user->id,
            'token_id' => $token->id ?? null,
            'has_authorization' => $request->headers->has('Authorization'),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
        ]);

        $last = $token->last_used_at ?? $token->created_at;
        $maxInactiveMinutes = (int) env('INACTIVITY_TIMEOUT_MINUTES', 60);

        if($last && now()->diffInMinutes($last) >= $maxInactiveMinutes){
            $token->delete(); // revoke
            return response()->json(['message'=>'Session expired due to inactivity'], 440);
        }

        return $next($request);
    }
}
