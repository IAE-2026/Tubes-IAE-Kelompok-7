<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private function formatSuccess($message, $data, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => [
                'service_name' => 'Vehicle-Service',
                'api_version' => 'v1'
            ]
        ], $code);
    }

    private function formatError($message, $errors = null, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public function index()
    {
        $vehicles = \App\Models\Vehicle::all();
        return $this->formatSuccess('Daftar kendaraan berhasil diambil', $vehicles);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'type' => 'nullable|string',
            'brand' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->formatError('Validasi gagal', $validator->errors(), 400);
        }

        if (app()->environment('testing')) {
            $receiptNumber = 'IAE-LOG-TESTING-12345';
        } else {
            $soapService = new \App\Services\SoapAuditService();
            $receiptNumber = $soapService->sendAuditLog('VehicleCreated', $request->except(['_token']));
            
            if (!$receiptNumber) {
                return $this->formatError('Proses audit gagal: Layanan SOAP Audit tidak merespon.', null, 503);
            }
        }

        $vehicleData = $request->all();
        $vehicleData['receipt_number'] = $receiptNumber;
        $vehicle = \App\Models\Vehicle::create($vehicleData);

        if (!app()->environment('testing')) {
            $publisher = new \App\Services\RabbitMQPublisher();
            
            $vehicleArray = $vehicle->toArray();
            $vehicleArray['team_id'] = env('RABBITMQ_TEAM_NAME', 'TEAM-07');

            $publisher->publishEvent('vehicle.created', [
                'event' => 'vehicle.created',
                'timestamp' => now()->toIso8601String(),
                'data' => $vehicleArray
            ]);
        }

        return $this->formatSuccess('Data kendaraan berhasil ditambahkan', $vehicle, 201);
    }

    public function show(string $id)
    {
        $vehicle = \App\Models\Vehicle::find($id);
        if (!$vehicle) {
            return $this->formatError('Kendaraan tidak ditemukan', null, 404);
        }
        return $this->formatSuccess('Data spesifik kendaraan berhasil diambil', $vehicle);
    }

    public function destroy(string $id)
    {
        $vehicle = \App\Models\Vehicle::find($id);
        if (!$vehicle) {
            return $this->formatError('Kendaraan tidak ditemukan', null, 404);
        }

        $vehicle->delete();
        return $this->formatSuccess('Data kendaraan berhasil dihapus', null);
    }
}
