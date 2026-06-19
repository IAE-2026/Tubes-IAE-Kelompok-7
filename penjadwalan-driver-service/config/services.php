<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IAE SSO — Server Nyata (https://iae-sso.virtualfri.id)
    | Sumber: "URL dan Akun Tugas IAE.pdf"
    |--------------------------------------------------------------------------
    |
    | SSO menggunakan JWKS RS256. JWT diverifikasi secara LOKAL menggunakan
    | public key yang diambil dari endpoint /api/v1/auth/jwks.
    | Tidak perlu forward token ke server untuk validasi.
    |
    */
    'sso' => [
        'base_url'       => env('SSO_BASE_URL',       'https://iae-sso.virtualfri.id'),
        'jwks_url'       => env('SSO_JWKS_URL',       'https://iae-sso.virtualfri.id/api/v1/auth/jwks'),
        'token_url'      => env('SSO_TOKEN_URL',       'https://iae-sso.virtualfri.id/api/v1/auth/token'),

        // Akun Warga (absen 35 → warga35)
        'user_email'     => env('SSO_USER_EMAIL',     'warga35@ktp.iae.id'),
        'user_password'  => env('SSO_USER_PASSWORD',  'KtpDigital2026!'),

        // M2M (Machine-to-Machine) untuk akses antar-service
        'm2m_api_key'    => env('SSO_M2M_API_KEY',    'KEY-MHS-270'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IAE Legacy Audit (SOAP) — Server Nyata
    | Endpoint: POST /soap/v1/audit (Bearer auth)
    |--------------------------------------------------------------------------
    |
    | Schema standar IAE (berbeda dari implementasi placeholder sebelumnya):
    |   <TeamID>    → Identitas tim lab (TEAM-07)
    |   <ActivityName> → Nama aktivitas bisnis
    |   <LogContent>   → Data transaksi dalam CDATA
    |
    */
    'legacy_audit' => [
        'endpoint'  => env('SOAP_AUDIT_ENDPOINT', 'https://iae-sso.virtualfri.id/soap/v1/audit'),
        'namespace' => env('SOAP_AUDIT_NAMESPACE', 'http://iae.central/audit'),
        'team_id'   => env('IAE_TEAM_ID', 'TEAM-07'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IAE Message Broker (HTTP REST, bukan AMQP langsung)
    | Endpoint: POST /api/v1/messages/publish (Bearer auth)
    |--------------------------------------------------------------------------
    |
    | Berbeda dari implementasi sebelumnya yang menggunakan php-amqplib.
    | Server dosen menyediakan HTTP REST API untuk publish ke exchange.
    | Exchange yang digunakan: iae.central.exchange
    |
    */
    'message_broker' => [
        'publish_url' => env('MESSAGE_PUBLISH_URL', 'https://iae-sso.virtualfri.id/api/v1/messages/publish'),
        'exchange'    => env('MESSAGE_EXCHANGE',    'iae.central.exchange'),
    ],

];
