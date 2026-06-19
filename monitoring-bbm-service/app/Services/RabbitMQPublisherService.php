<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQPublisherService
{
    public function publish($log): void
    {
        $token = $this->getToken();

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ])->post('https://iae-sso.virtualfri.id/api/v1/messages/publish', [
            'exchange'    => 'iae.central.exchange',
            'routing_key' => 'fuel.log.created',
            'payload'     => [
                'event'        => 'FuelLogCreated',
                'service'      => 'FuelLog-Service',
                'fuel_log_id'  => $log->id,
                'vehicle_id'   => $log->vehicle_id,
                'driver_name'  => $log->driver_name,
                'liters'       => $log->liters,
                'total_cost'   => $log->total_cost,
                'fuel_station' => $log->fuel_station,
                'filled_at'    => $log->filled_at,
                'timestamp'    => now()->toISOString(),
            ],
        ]);
    }

    private function getToken(): string
    {
        $response = Http::post(
            'https://iae-sso.virtualfri.id/api/v1/auth/token',
            [
                'api_key' => env('IAE_API_KEY_M2M', 'KEY-MHS-18'),
                'nim'     => env('IAE_MHS_NIM', '102022400033'),
            ]
        );

        return $response->json('token')
            ?? $response->json('access_token')
            ?? '';
    }
}