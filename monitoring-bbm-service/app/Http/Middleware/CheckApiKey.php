<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('IAE_API_KEY_M2M');
        $bearerToken = $request->bearerToken();

        if ($apiKey === env('IAE_API_KEY_M2M')) {
            return $next($request);
        }

        if (!empty($bearerToken)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. API Key atau Bearer Token tidak valid.',
            'errors' => null
        ], 401);
    }
}