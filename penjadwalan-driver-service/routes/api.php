<?php

use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Middleware\VerifySsoToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Penjadwalan Driver Service
|--------------------------------------------------------------------------
|
| Service B: Penjadwalan Driver (Group 7 — Pencatatan Operasional BBM)
| NIM: 102022400210 - Hafizh Rafi Maulana Suyufi
|
| Tugas 3: Autentikasi via IAE SSO (https://iae-sso.virtualfri.id)
|   - Primary:  Authorization: Bearer <JWT> (dari SSO warga35@ktp.iae.id / KEY-MHS-270)
|   - Fallback: X-IAE-KEY: 102022400210 (kompatibilitas Tugas 2)
|
| Middleware VerifySsoToken:
|   - Verifikasi JWT via JWKS RS256 (offline, cached 60 menit)
|   - Role GET:  admin_operasional, dispatcher, driver, auditor
|   - Role POST: admin_operasional, dispatcher (transaksi kritis)
|
*/

Route::prefix('v1')->middleware(VerifySsoToken::class)->group(function () {
    // Collection: Get all schedules
    Route::get('/schedules', [ScheduleController::class, 'index']);

    // Resource: Get specific schedule
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);

    // Action: Create new schedule (transaksi kritis — SOAP + AMQP)
    Route::post('/schedules', [ScheduleController::class, 'store']);

    // Action: Delete schedule
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
});
