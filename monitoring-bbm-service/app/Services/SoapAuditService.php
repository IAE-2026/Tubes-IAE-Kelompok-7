<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SoapAuditService
{
    private string $url = 'https://iae-sso.virtualfri.id/soap/v1/audit';

    public function sendAudit($log): string
    {
        $token = $this->getToken();

        if (empty($token)) {
            return 'NO-RECEIPT';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>' . env('IAE_TEAM_ID', 'TEAM-07') . '</iae:TeamID>
            <iae:ActivityName>FuelLogCreated</iae:ActivityName>
            <iae:LogContent><![CDATA[' . json_encode([
                'fuel_log_id'  => $log->id,
                'vehicle_id'   => $log->vehicle_id,
                'driver_name'  => $log->driver_name,
                'liters'       => $log->liters,
                'total_cost'   => $log->total_cost,
                'fuel_station' => $log->fuel_station,
                'filled_at'    => $log->filled_at,
            ]) . ']]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'text/xml; charset=utf-8',
            'SOAPAction'    => '',
        ])->withBody($xml, 'text/xml')->post($this->url);

        $body = $response->body();

        if (preg_match(
            '/<(?:iae:)?ReceiptNumber>(.*?)<\/(?:iae:)?ReceiptNumber>/',
            $body,
            $matches
        )) {
            return trim($matches[1]);
        }

        return 'NO-RECEIPT';
    }

    private function getToken(): string
    {
        $response = Http::post(
            'https://iae-sso.virtualfri.id/api/v1/auth/token',
            [
                'api_key' => env('IAE_API_KEY_M2M'),
                'nim'     => env('IAE_MHS_NIM', '102022400033')
            ]
        );

        return $response->json('token')
            ?? $response->json('access_token')
            ?? '';
    }
}