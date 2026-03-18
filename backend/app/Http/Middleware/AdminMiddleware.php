<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user() || ! in_array($request->user()->role, ['admin', 'staff'])) {
            return response()->json([
                'message' => 'Unauthorized. Admin or staff access required.'
            ], 403);
        }

        return $next($request);
    }
}
