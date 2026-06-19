<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     * Validates the X-IAE-KEY header against the expected NIM.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');
        $validKey = '102022400210'; // NIM Hafizh Rafi Maulana Suyufi

        if (!$apiKey || $apiKey !== $validKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API Key (X-IAE-KEY).',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}
