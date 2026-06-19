<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Didit Vehicle Service API",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="didit@example.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="ApiKeyAuth",
 *      type="apiKey",
 *      in="header",
 *      name="X-IAE-KEY",
 *      description="Enter your NIM (e.g. 102022400066)"
 * )
 */
abstract class Controller
{
}
