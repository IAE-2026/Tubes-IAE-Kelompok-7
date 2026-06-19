<?php

namespace App\Http\Middleware;

use App\Services\IaeSsoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * VerifySsoToken
 *
 * Middleware autentikasi berbasis JWT dari IAE SSO (iae-sso.virtualfri.id).
 * Verifikasi JWT offline via JWKS RS256 (cached 60 menit).
 *
 * Fallback: X-IAE-KEY: 102022400210 untuk kompatibilitas lokal.
 */
class VerifySsoToken
{
    public function __construct(private readonly IaeSsoService $ssoService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        // Fallback: X-IAE-KEY (kompatibilitas testing lokal)
        if (!$bearerToken) {
            $apiKey = $request->header('X-IAE-KEY');
            if ($apiKey && $apiKey === '102022400210') {
                $request->merge([
                    'sso_user_id'   => 'local-dev',
                    'sso_user_name' => 'Local Dev (API Key Fallback)',
                    'sso_role'      => 'admin_operasional',
                    'auth_method'   => 'api_key_fallback',
                ]);
                return $next($request);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized. Sertakan Bearer JWT (SSO) atau X-IAE-KEY header.',
                'errors'  => null,
            ], 401);
        }

        // Verifikasi JWT via JWKS (RS256)
        try {
            $payload = $this->ssoService->verifyJwt($bearerToken);

            $role = $payload['role']
                 ?? $payload['token_type']
                 ?? $payload['grant_type']
                 ?? 'authenticated';

            $profile  = $payload['profile'] ?? [];
            $userName = $payload['name']
                     ?? (is_array($profile) ? ($profile['name'] ?? null) : null)
                     ?? $payload['sub']
                     ?? null;

            $request->merge([
                'sso_user_id'   => $payload['sub']  ?? null,
                'sso_user_name' => $userName,
                'sso_role'      => $role,
                'auth_method'   => 'jwt_sso',
            ]);

            Log::info('VerifySsoToken: akses diizinkan', [
                'role'   => $role,
                'sub'    => $payload['sub'] ?? null,
                'method' => $request->method(),
                'path'   => $request->path(),
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized. Token tidak valid atau expired.',
                'errors'  => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'SSO service unavailable.',
                'errors'  => $e->getMessage(),
            ], 503);
        }

        return $next($request);
    }
}
