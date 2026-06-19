<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIaeKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-IAE-KEY');
        if ($key !== '102022400066') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or missing X-IAE-KEY',
                'errors' => null
            ], 401);
        }

        return $next($request);
    }
}
