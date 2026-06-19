<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-IAE-KEY') !== '102022400066') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid API Key.',
                'errors' => null
            ], 401);
        }
        return $next($request);
    }
}
