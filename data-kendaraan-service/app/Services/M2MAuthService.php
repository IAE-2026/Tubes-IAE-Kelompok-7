<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class M2MAuthService
{
    /**
     * Get M2M JWT Token using the Student API Key.
     * Caches the token to avoid hitting the auth endpoint on every request.
     *
     * @return string|null The JWT token, or null on failure.
     */
    public static function getToken(): ?string
    {
        $apiKey = env('SOAP_AUDIT_TOKEN');
        
        if (!$apiKey) {
            Log::error('M2MAuthService: SOAP_AUDIT_TOKEN is not defined in .env');
            return null;
        }

        return Cache::remember('m2m_token_' . $apiKey, 3300, function () use ($apiKey) {
            $url = env('SSO_TOKEN_URL', 'https://iae-sso.virtualfri.id/api/v1/auth/token');
            
            try {
                $response = Http::post($url, [
                    'api_key' => $apiKey,
                    'nim'     => env('IAE_MHS_NIM', '102022400066')
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['token'])) {
                        return $data['token'];
                    } elseif (isset($data['access_token'])) {
                        return $data['access_token'];
                    }
                }

                Log::error('M2MAuthService: Failed to retrieve M2M token', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                Log::error('M2MAuthService: Exception when retrieving M2M token', [
                    'message' => $e->getMessage()
                ]);
            }

            return null;
        });
    }
}
