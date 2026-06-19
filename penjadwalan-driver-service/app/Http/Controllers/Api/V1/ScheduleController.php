<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\IaeSsoService;
use App\Services\SoapAuditClient;
use App\Services\ScheduleEventPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Penjadwalan Driver Service API",
    description: "Service B: API untuk mengelola penjadwalan driver operasional. Bagian dari ekosistem Pencatatan Operasional (Pengisian BBM) - Group 7.",
    contact: new OA\Contact(
        name: "Hafizh Rafi Maulana Suyufi",
        email: "102022400210@student.telkomuniversity.ac.id"
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "BearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "JWT dari IAE SSO (https://iae-sso.virtualfri.id). Login sebagai warga21@ktp.iae.id"
)]
#[OA\SecurityScheme(
    securityScheme: "X-IAE-KEY",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Fallback API Key (Tugas 2): NIM Mahasiswa (102022400210)"
)]
class ScheduleController extends Controller
{
    public function __construct(
        private readonly IaeSsoService          $ssoService,
        private readonly SoapAuditClient        $soapAuditClient,
        private readonly ScheduleEventPublisher $eventPublisher
    ) {
    }

    /**
     * GET /api/v1/schedules
     * Mengambil daftar seluruh jadwal operasional driver.
     */
    #[OA\Get(
        path: "/api/v1/schedules",
        summary: "Get all driver schedules",
        description: "Mengambil daftar seluruh jadwal operasional driver.",
        operationId: "getSchedules",
        tags: ["Schedules"],
        security: [["BearerAuth" => []], ["X-IAE-KEY" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Data retrieved successfully"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - Invalid or missing auth token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized. Sertakan Bearer JWT (SSO) atau X-IAE-KEY header."),
                        new OA\Property(property: "errors", type: "string", nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $schedules = Schedule::all();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data retrieved successfully',
            'data'    => $schedules,
            'meta'    => [
                'service_name' => 'Penjadwalan-Driver-Service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }

    /**
     * GET /api/v1/schedules/{id}
     * Mengambil data spesifik jadwal shift berdasarkan ID.
     */
    #[OA\Get(
        path: "/api/v1/schedules/{id}",
        summary: "Get a specific schedule",
        description: "Mengambil data spesifik jadwal shift berdasarkan ID.",
        operationId: "getScheduleById",
        tags: ["Schedules"],
        security: [["BearerAuth" => []], ["X-IAE-KEY" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Schedule ID",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Data retrieved successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Schedule not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Schedule not found"),
                        new OA\Property(property: "errors", type: "string", nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized."),
                        new OA\Property(property: "errors", type: "string", nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Schedule not found',
                'errors'  => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data retrieved successfully',
            'data'    => $schedule,
            'meta'    => [
                'service_name' => 'Penjadwalan-Driver-Service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }

    /**
     * POST /api/v1/schedules
     * Menambah data penugasan atau jadwal baru untuk driver.
     * Transaksi kritis: memicu SOAP Audit + Event Publishing ke IAE server.
     */
    #[OA\Post(
        path: "/api/v1/schedules",
        summary: "Create a new schedule",
        description: "Menambah jadwal baru. Hanya role admin_operasional dan dispatcher yang diizinkan. Memicu SOAP audit ke IAE Legacy System dan publish event ke iae.central.exchange.",
        operationId: "createSchedule",
        tags: ["Schedules"],
        security: [["BearerAuth" => []], ["X-IAE-KEY" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["driver_name", "vehicle_id", "plate_number", "schedule_date", "shift"],
                properties: [
                    new OA\Property(property: "driver_name",   type: "string",  example: "Budi Santoso"),
                    new OA\Property(property: "vehicle_id",    type: "integer", example: 1),
                    new OA\Property(property: "plate_number",  type: "string",  example: "B 1234 ABC"),
                    new OA\Property(property: "schedule_date", type: "string",  format: "date", example: "2026-05-15"),
                    new OA\Property(property: "shift",         type: "string",  enum: ["pagi", "siang", "malam"], example: "pagi"),
                    new OA\Property(property: "status",        type: "string",  enum: ["active", "completed", "cancelled"], example: "active"),
                    new OA\Property(property: "notes",         type: "string",  nullable: true, example: "Rute Jakarta - Bandung"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Schedule created successfully"),
            new OA\Response(response: 403, description: "Forbidden - Role tidak diizinkan POST"),
            new OA\Response(response: 422, description: "Validation Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status",  type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Validation failed"),
                        new OA\Property(property: "errors",  type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver_name'   => 'required|string|max:255',
            'vehicle_id'    => 'required|integer',
            'plate_number'  => 'required|string|max:20',
            'schedule_date' => 'required|date',
            'shift'         => 'required|string|in:pagi,siang,malam',
            'status'        => 'sometimes|string|in:active,completed,cancelled',
            'notes'         => 'nullable|string',
        ]);

        // Validasi lintas layanan ke Data Kendaraan (Service Didit)
        $diditUrl = env('DIDIT_SERVICE_URL', 'http://didit-service:8000');
        $token = $request->bearerToken();

        $vehicleRes = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get("{$diditUrl}/api/v1/vehicles/{$request->vehicle_id}");

        if ($vehicleRes->failed()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal: Kendaraan (vehicle_id) tidak ditemukan di layanan Data Kendaraan.',
            ], 422);
        }

        $vehicleData = $vehicleRes->json('data');
        if ($vehicleData['license_plate'] !== $request->plate_number) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal: plate_number tidak cocok dengan license_plate dari data kendaraan tersebut.',
            ], 422);
        }

        $schedule = Schedule::create($validated);

        // SOAP Audit — non-blocking
        $auditResult = $this->soapAuditClient->auditScheduleCreation(
            $schedule->toArray(),
            $request->input('sso_user_id') ?? 'system',
            $request->input('sso_role')    ?? 'authenticated'
        );

        // Publish event schedule.created — non-blocking
        $this->eventPublisher->publishScheduleCreated($schedule->toArray());

        return response()->json([
            'status'  => 'success',
            'message' => 'Schedule created successfully',
            'data'    => $schedule,
            'meta'    => [
                'service_name'  => 'Penjadwalan-Driver-Service',
                'api_version'   => 'v1',
                'auth_method'   => $request->input('auth_method', 'unknown'),
                'audit_receipt' => $auditResult['receipt_number'] ?? null,
                'audit_status'  => $auditResult['status']         ?? 'SKIPPED',
            ],
        ], 201);
    }

    /**
     * DELETE /api/v1/schedules/{id}
     * Menghapus data jadwal.
     */
    #[OA\Delete(
        path: "/api/v1/schedules/{id}",
        summary: "Delete a schedule",
        description: "Menghapus jadwal shift.",
        operationId: "deleteSchedule",
        tags: ["Schedules"],
        security: [["BearerAuth" => []], ["X-IAE-KEY" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Schedule ID",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: "Schedule deleted successfully"),
            new OA\Response(response: 404, description: "Schedule not found"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Schedule not found',
                'errors'  => null,
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Schedule deleted successfully',
            'data'    => null,
        ], 200);
    }
}
