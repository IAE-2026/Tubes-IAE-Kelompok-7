<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IaeSsoService
 *
 * Service untuk interaksi dengan IAE SSO (https://iae-sso.virtualfri.id).
 *
 * Endpoints:
 *  - JWKS:  GET  /api/v1/auth/jwks  (RS256 public keys)
 *  - Token: POST /api/v1/auth/token
 *    - User: { email, password }
 *    - M2M:  { api_key: "KEY-MHS-270" }
 *
 * Response token ada di field "token" (bukan "access_token").
 */
class IaeSsoService
{
    private string $jwksUrl;
    private string $tokenUrl;
    private string $m2mApiKey;

    public function __construct()
    {
        $this->jwksUrl   = config('services.sso.jwks_url',   'https://iae-sso.virtualfri.id/api/v1/auth/jwks');
        $this->tokenUrl  = config('services.sso.token_url',  'https://iae-sso.virtualfri.id/api/v1/auth/token');
        $this->m2mApiKey = config('services.sso.m2m_api_key', 'KEY-MHS-270');
    }

    // =========================================================================
    // JWKS — Public Key untuk verifikasi JWT RS256
    // =========================================================================

    /**
     * Ambil JWKS dari SSO server (di-cache 60 menit).
     *
     * @return array JWKS response
     * @throws \RuntimeException
     */
    public function getJwks(): array
    {
        return Cache::remember('iae_sso_jwks', 3600, function () {
            $response = Http::timeout(10)->get($this->jwksUrl);

            if (!$response->successful()) {
                throw new \RuntimeException("Gagal mengambil JWKS: HTTP {$response->status()}");
            }

            return $response->json();
        });
    }

    public function flushJwksCache(): void
    {
        Cache::forget('iae_sso_jwks');
    }

    /**
     * Verifikasi JWT menggunakan JWKS (RS256) secara lokal.
     * Fallback ke trust-decode jika kid tidak cocok.
     *
     * @param string $token JWT Bearer token
     * @return array Decoded JWT payload
     * @throws \RuntimeException
     */
    public function verifyJwt(string $token): array
    {
        if (!class_exists(\Firebase\JWT\JWT::class)) {
            throw new \RuntimeException('firebase/php-jwt tidak terinstall.');
        }

        try {
            $jwks    = $this->getJwks();
            $jwkKeys = \Firebase\JWT\JWK::parseKeySet($jwks);
            \Firebase\JWT\JWT::$leeway = 60; // Allow 60 seconds clock skew
            $decoded = \Firebase\JWT\JWT::decode($token, $jwkKeys);
            return (array) $decoded;

        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new \RuntimeException('Token expired: ' . $e->getMessage());
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new \RuntimeException('Signature tidak valid: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Flush JWKS cache dan coba sekali lagi
            $this->flushJwksCache();
            try {
                $jwks    = $this->getJwks();
                $jwkKeys = \Firebase\JWT\JWK::parseKeySet($jwks);
                \Firebase\JWT\JWT::$leeway = 60; // Allow 60 seconds clock skew
                $decoded = \Firebase\JWT\JWT::decode($token, $jwkKeys);
                return (array) $decoded;
            } catch (\Exception $retryEx) {
                // Fallback: trust-decode tanpa verifikasi signature
                $parts = explode('.', $token);
                if (count($parts) === 3) {
                    $payloadJson = base64_decode(str_pad(
                        strtr($parts[1], '-_', '+/'),
                        strlen($parts[1]) % 4 === 0 ? strlen($parts[1]) : strlen($parts[1]) + (4 - strlen($parts[1]) % 4),
                        '='
                    ));
                    $payload = json_decode($payloadJson, true);
                    if ($payload && isset($payload['sub'])) {
                        if (isset($payload['exp']) && $payload['exp'] < time()) {
                            throw new \RuntimeException('Token expired');
                        }
                        Log::warning('verifyJwt: fallback trust-decode (kid mismatch)', [
                            'sub' => $payload['sub'] ?? null,
                        ]);
                        return $payload;
                    }
                }
                throw new \RuntimeException('JWT verification gagal: ' . $retryEx->getMessage());
            }
        }
    }

    // =========================================================================
    // M2M Token — Machine-to-Machine
    // =========================================================================

    /**
     * Ambil M2M access token menggunakan API Key (di-cache 55 menit).
     *
     * @return string Access token
     * @throws \RuntimeException
     */
    public function getM2MToken(): string
    {
        return Cache::remember('iae_m2m_token', 3300, function () {
            $response = Http::timeout(10)->post($this->tokenUrl, [
                'api_key' => $this->m2mApiKey,
                'nim'     => env('IAE_MHS_NIM', '102022400033'),
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "Gagal mendapat M2M token: HTTP {$response->status()} — {$response->body()}"
                );
            }

            $data  = $response->json();
            $token = $data['access_token'] ?? $data['token'] ?? null;

            if (!$token) {
                throw new \RuntimeException('M2M token tidak ditemukan di response SSO.');
            }

            Log::info('M2M token berhasil diambil dari IAE SSO');
            return $token;
        });
    }

    public function flushM2MTokenCache(): void
    {
        Cache::forget('iae_m2m_token');
    }

    // =========================================================================
    // User Token — Login sebagai warga
    // =========================================================================

    /**
     * Login sebagai user warga (warga35@ktp.iae.id).
     *
     * @return array { token, token_type, ... }
     * @throws \RuntimeException
     */
    public function loginAsWarga(): array
    {
        $response = Http::timeout(10)->post($this->tokenUrl, [
            'email'    => config('services.sso.user_email',    'warga35@ktp.iae.id'),
            'password' => config('services.sso.user_password', 'KtpDigital2026!'),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Login warga gagal: HTTP {$response->status()} — {$response->body()}"
            );
        }

        return $response->json();
    }
}
