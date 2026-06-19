<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RabbitMQPublisher
{
    /**
     * Publish an event message to RabbitMQ via the central HTTP message bridge.
     *
     * @param string $routingKey
     * @param array $payload
     * @return bool True if successful, false otherwise
     */
    public function publishEvent(string $routingKey, array $payload): bool
    {
        $publishUrl = env('CENTRAL_PUBLISH_URL', 'https://iae-sso.virtualfri.id/api/v1/messages/publish');
        $exchange = env('CENTRAL_EXCHANGE', 'iae.central.exchange');
        $teamName = env('RABBITMQ_TEAM_NAME', 'TEAM-07');
        
        $fullRoutingKey = $routingKey;
        
        $token = \App\Services\M2MAuthService::getToken();

        if (!$token) {
            Log::error('RabbitMQ Publisher Error: Authorization Bearer token is missing.');
            return false;
        }

        $requestBody = [
            'exchange' => $exchange,
            'routing_key' => $fullRoutingKey,
            'routingKey' => $fullRoutingKey,
            'team_id' => $teamName,
            'message' => $payload,
            'payload' => $payload,
            'data' => $payload
        ];

        try {
            Log::info("Publishing RabbitMQ message via HTTP Bridge to {$publishUrl}", [
                'exchange' => $exchange,
                'routing_key' => $routingKey
            ]);

            $response = Http::withToken($token)
                ->contentType('application/json')
                ->post($publishUrl, $requestBody);

            if ($response->failed()) {
                Log::error('RabbitMQ HTTP Publish Failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            Log::info('RabbitMQ Message Published Successfully', [
                'response' => $response->json() ?? $response->body()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('RabbitMQ Publisher Exception occurred', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
