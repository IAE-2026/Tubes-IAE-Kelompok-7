<?php

use App\Http\Controllers\Api\V1\FuelLogController;
use App\Http\Controllers\Api\V1\SsoController;
use Illuminate\Support\Facades\Route;

Route::middleware('check.api.key')->prefix('v1')->group(function () {
    Route::get('/fuel-logs', [FuelLogController::class, 'index']);
    Route::get('/fuel-logs/{id}', [FuelLogController::class, 'show']);
    Route::post('/fuel-logs', [FuelLogController::class, 'store']);
    Route::delete('/fuel-logs/{id}', [FuelLogController::class, 'destroy']);
    Route::post('/auth/sso', [SsoController::class, 'authenticate']);
});