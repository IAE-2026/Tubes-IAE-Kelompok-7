<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Services\SoapAuditService;
use App\Services\RabbitMQPublisherService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Fuel Logs")]
class FuelLogController extends Controller
{
    #[OA\Get(
        path: "/api/v1/fuel-logs",
        summary: "Ambil semua fuel log",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index()
    {
        $logs = FuelLog::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $logs,
            'meta' => ['service_name' => 'FuelLog-Service', 'api_version' => 'v1']
        ]);
    }

    #[OA\Get(
        path: "/api/v1/fuel-logs/{id}",
        summary: "Ambil fuel log berdasarkan ID",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show($id)
    {
        $log = FuelLog::find($id);
        if (!$log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fuel log not found',
                'errors' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $log
        ]);
    }

    #[OA\Post(
        path: "/api/v1/fuel-logs",
        summary: "Tambah fuel log baru",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id", "schedule_id", "driver_name", "liters", "total_cost", "fuel_station", "filled_at"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                    new OA\Property(property: "schedule_id", type: "integer", example: 1),
                    new OA\Property(property: "driver_name", type: "string", example: "Adwitiya Tikta Pramasti"),
                    new OA\Property(property: "liters", type: "number", example: 40.5),
                    new OA\Property(property: "total_cost", type: "number", example: 350000),
                    new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                    new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'   => 'required|integer',
            'schedule_id'  => 'required|integer',
            'driver_name'  => 'required|string',
            'liters'       => 'required|numeric|min:0',
            'total_cost'   => 'required|numeric|min:0',
            'fuel_station' => 'required|string',
            'filled_at'    => 'required|date',
        ]);

        $diditUrl = env('DIDIT_SERVICE_URL', 'http://didit-service:8000');
        $hafizhUrl = env('HAFIZH_SERVICE_URL', 'http://hafizh-service:80');
        
        $token = $this->getM2MToken();

        // 1. Validate Vehicle (Service Didit)
        $vehicleRes = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get("{$diditUrl}/api/v1/vehicles/{$request->vehicle_id}");

        if ($vehicleRes->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi lintas layanan gagal: Kendaraan tidak ditemukan di Service Didit. Response: ' . $vehicleRes->body(),
            ], 400);
        }

        // 2. Validate Schedule (Service Hafizh)
        $scheduleRes = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-IAE-KEY' => '102022400210',
            'Accept' => 'application/json'
        ])->get("{$hafizhUrl}/api/v1/schedules/{$request->schedule_id}");

        if ($scheduleRes->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi lintas layanan gagal: Jadwal driver tidak valid/tidak ditemukan di Service Hafizh.',
            ], 400);
        }

        $scheduleData = $scheduleRes->json('data') ?? [];

        if (isset($scheduleData['vehicle_id']) && $scheduleData['vehicle_id'] != $request->vehicle_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi lintas layanan gagal: vehicle_id tidak sesuai dengan data kendaraan pada jadwal (schedule_id) tersebut.',
            ], 422);
        }

        if (isset($scheduleData['driver_name']) && $scheduleData['driver_name'] !== $request->driver_name) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi lintas layanan gagal: driver_name tidak sesuai dengan nama driver pada jadwal (schedule_id) tersebut.',
            ], 422);
        }

        $logData = [
            'vehicle_id' => $request->vehicle_id,
            'driver_name' => $scheduleData['driver_name'] ?? $request->driver_name,
            'liters' => $request->liters,
            'total_cost' => $request->total_cost,
            'fuel_station' => $request->fuel_station,
            'filled_at' => $request->filled_at,
        ];

        $log = FuelLog::create($logData);
        try {
            $soapService = new SoapAuditService();
            $receiptNumber = $soapService->sendAudit($log);
            $log->update(['soap_receipt_number' => $receiptNumber]);
        } catch (\Exception $e) {
            // Tidak gagalkan request jika SOAP error
        }

        try {
            $rabbitService = new RabbitMQPublisherService();
            $rabbitService->publish($log);
        } catch (\Exception $e) {
            // Tidak gagalkan request jika RabbitMQ error
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fuel log berhasil ditambahkan.',
            'data' => $log
        ], 201);
    }

    private function getM2MToken(): string
    {
        $response = \Illuminate\Support\Facades\Http::post(
            'https://iae-sso.virtualfri.id/api/v1/auth/token',
            [
                'api_key' => env('IAE_API_KEY_M2M', 'KEY-MHS-270'),
                'nim'     => env('IAE_MHS_NIM', '102022400033')
            ]
        );

        return $response->json('token')
            ?? $response->json('access_token')
            ?? '';
    }

    #[OA\Delete(
        path: "/api/v1/fuel-logs/{id}",
        summary: "Hapus fuel log berdasarkan ID",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted successfully"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function destroy($id)
    {
        $log = FuelLog::find($id);
        if (!$log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fuel log not found',
                'errors' => null
            ], 404);
        }

        $log->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Fuel log berhasil dihapus',
            'data' => null
        ]);
    }
}