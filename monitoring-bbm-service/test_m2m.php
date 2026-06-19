<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = Illuminate\Support\Facades\Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', ['api_key' => 'KEY-MHS-206', 'nim' => '102022400179']);
echo $res->body() . "\n";
