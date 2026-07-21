<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TryAuthenticateCustomer
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = Auth::guard('sanctum')->user()) {
            Auth::shouldUse('sanctum');
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
