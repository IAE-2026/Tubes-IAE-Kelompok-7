<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ScheduleEventPublisher
 *
 * Mempublikasikan event schedule.created ke IAE Message Broker
 * via HTTP REST API. Auth: Bearer M2M token (KEY-MHS-270).
 *
 * Exchange: iae.central.exchange | Routing key: schedule.created
 */
class ScheduleEventPublisher
{
    private string $publishUrl;
    private string $exchange;

    public function __construct(private readonly IaeSsoService $ssoService)
    {
        $this->publishUrl = config('services.message_broker.publish_url', 'https://iae-sso.virtualfri.id/api/v1/messages/publish');
        $this->exchange   = config('services.message_broker.exchange',    'iae.central.exchange');
    }

    /**
     * Publish event schedule.created ke IAE Message Broker.
     *
     * @param array $scheduleData Data jadwal yang baru dibuat
     * @return bool true jika berhasil, false jika gagal (non-blocking)
     */
    public function publishScheduleCreated(array $scheduleData): bool
    {
        $eventPayload = [
            'exchange'    => $this->exchange,
            'routing_key' => 'schedule.created',
            'message'     => [
                'event'     => 'schedule.created',
                'timestamp' => now()->toIso8601String(),
                'source'    => 'Penjadwalan-Driver-Service',
                'data'      => [
                    'schedule_id'   => $scheduleData['id']            ?? null,
                    'driver_name'   => $scheduleData['driver_name']   ?? null,
                    'vehicle_id'    => $scheduleData['vehicle_id']    ?? null,
                    'plate_number'  => $scheduleData['plate_number']  ?? null,
                    'schedule_date' => $scheduleData['schedule_date'] ?? null,
                    'shift'         => $scheduleData['shift']         ?? null,
                    'status'        => $scheduleData['status']        ?? 'active',
                ],
            ],
        ];

        try {
            $m2mToken = $this->ssoService->getM2MToken();

            $response = Http::withToken($m2mToken)
                ->timeout(10)
                ->post($this->publishUrl, $eventPayload);

            if ($response->successful()) {
                Log::info('Event schedule.created published ke IAE Message Broker', [
                    'schedule_id' => $scheduleData['id'] ?? null,
                    'exchange'    => $this->exchange,
                ]);
                return true;
            }

            Log::warning('Publish event gagal (non-200 response)', [
                'http_status' => $response->status(),
                'body'        => substr($response->body(), 0, 300),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Publish event ke IAE Message Broker gagal (non-blocking)', [
                'error'       => $e->getMessage(),
                'schedule_id' => $scheduleData['id'] ?? null,
            ]);
            return false;
        }
    }
}
