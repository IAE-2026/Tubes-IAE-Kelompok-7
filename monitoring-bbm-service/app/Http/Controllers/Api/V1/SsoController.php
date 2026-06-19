<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SsoController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $token = $request->input('token');

        $jwksResponse = Http::get('https://iae-sso.virtualfri.id/api/v1/auth/jwks');
        if (!$jwksResponse->successful()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil JWKS dari SSO.',
                'errors'  => null
            ], 500);
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token JWT tidak valid.',
                'errors'  => null
            ], 401);
        }

        $payload = json_decode(base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        )), true);

        if (!$payload) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payload JWT tidak bisa dibaca.',
                'errors'  => null
            ], 401);
        }

        $ssoRole = $payload['role'] ?? $payload['roles'][0] ?? 'viewer';
        $localRole = match($ssoRole) {
            'admin'   => 'admin',
            'manager' => 'manager',
            default   => 'viewer',
        };

        $user = User::updateOrCreate(
            ['email' => $payload['email'] ?? $payload['sub'] ?? 'unknown@sso.id'],
            [
                'name'   => $payload['name'] ?? 'SSO User',
                'sso_id' => $payload['sub'] ?? null,
                'role'   => $localRole,
                'password' => bcrypt(\Str::random(32)),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'SSO authentication berhasil.',
            'data'    => [
                'user'       => $user,
                'local_role' => $localRole,
                'sso_payload' => $payload,
            ]
        ]);
    }
}