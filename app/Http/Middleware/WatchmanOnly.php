<?php

namespace App\Http\Middleware;

use App\Models\Watchman;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WatchmanOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('sanctum')->check() === false) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }
}
