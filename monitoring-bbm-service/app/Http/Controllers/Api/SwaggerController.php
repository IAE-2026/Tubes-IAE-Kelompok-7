<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Fuel Monitoring Service API",
    version: "1.0.0",
    description: "API untuk Fuel Monitoring Service"
)]

#[OA\SecurityScheme(
    securityScheme: "apiKey",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY"
)]

class SwaggerController extends Controller
{
}