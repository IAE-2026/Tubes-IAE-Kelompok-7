<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class VerifySsoJwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing or invalid Authorization Bearer token',
                'errors' => null
            ], 401);
        }

        $token = substr($authHeader, 7);

        if (app()->environment('testing') && str_starts_with($token, 'mock_token_')) {
            $roleStr = substr($token, 11);
            $decoded = (object)[
                'email' => $roleStr . '@ktp.iae.id',
                'name' => 'Mock ' . ucfirst($roleStr),
                'role' => $roleStr,
                'sub' => 'mock_sub_' . $roleStr
            ];
        } else {
            try {
                $jwksUrl = env('SSO_JWKS_URL', 'https://iae-sso.virtualfri.id/api/v1/auth/jwks');
                
                $jwks = Cache::remember('sso_jwks', 86400, function () use ($jwksUrl) {
                    $response = Http::withToken(\App\Services\M2MAuthService::getToken())->get($jwksUrl);
                    if ($response->failed()) {
                        throw new \Exception('Failed to retrieve JWKS from central SSO server: ' . $jwksUrl);
                    }
                    return $response->json();
                });

                $keys = JWK::parseKeySet($jwks);
                JWT::$leeway = 60; // Allow 60 seconds clock skew
                $decoded = JWT::decode($token, $keys);
                
            } catch (\Exception $e) {
                try {
                    Cache::forget('sso_jwks');
                    $jwks = Cache::remember('sso_jwks', 86400, function () use ($jwksUrl) {
                        $response = Http::withToken(\App\Services\M2MAuthService::getToken())->get($jwksUrl);
                        if ($response->failed()) {
                            throw new \Exception('Failed to retrieve JWKS from central SSO server: ' . $jwksUrl);
                        }
                        return $response->json();
                    });
                    $keys = JWK::parseKeySet($jwks);
                    JWT::$leeway = 60; // Allow 60 seconds clock skew
                    $decoded = JWT::decode($token, $keys);
                } catch (\Exception $retryException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized: Invalid token (' . $retryException->getMessage() . ')',
                        'errors' => null
                    ], 401);
                }
            }
        }

        $email = $decoded->email ?? null;
        $sub = $decoded->sub ?? null;
        
        if (!$email && !$sub) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token payload is missing user identifier (email/sub)',
                'errors' => null
            ], 401);
        }

        $roleName = 'warga';
        if (isset($decoded->role)) {
            $roleName = is_array($decoded->role) ? ($decoded->role[0] ?? 'warga') : $decoded->role;
        } elseif (isset($decoded->roles)) {
            $roleName = is_array($decoded->roles) ? ($decoded->roles[0] ?? 'warga') : $decoded->roles;
        } elseif ($email) {
            if (str_contains($email, 'admin')) {
                $roleName = 'admin';
            } elseif (str_contains($email, 'staf')) {
                $roleName = 'staf';
            } elseif (str_contains($email, 'driver')) {
                $roleName = 'driver';
            }
        }

        $role = \App\Models\Role::firstOrCreate(
            ['name' => strtolower($roleName)],
            ['description' => ucfirst($roleName) . ' Role']
        );

        $user = \App\Models\User::updateOrCreate(
            ['email' => $email ?? $sub],
            [
                'name' => $decoded->name ?? $decoded->username ?? explode('@', $email)[0] ?? 'User',
                'password' => bcrypt('SSO_USER_NO_PASSWORD'),
                'role_id' => $role->id,
            ]
        );

        Auth::login($user);

        if ($request->isMethod('post') && !in_array(strtolower($role->name), ['admin', 'staf', 'warga'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You do not have permission to perform this action (Required role: admin/staf/warga, yours: ' . $role->name . ')',
                'errors' => null
            ], 403);
        }

        return $next($request);
    }
}
