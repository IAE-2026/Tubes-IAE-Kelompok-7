<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoapAuditService
{
    /**
     * Send audit log to Legacy SOAP Audit Service.
     *
     * @param string $activityName
     * @param array $data
     * @return string|null Receipt number if successful, null otherwise
     */
    public function sendAuditLog(string $activityName, array $data): ?string
    {
        $soapUrl = env('SOAP_AUDIT_URL', 'https://iae-sso.virtualfri.id/soap/v1/audit');
        $teamId = env('SOAP_TEAM_ID', 'TEAM-01');
        
        $token = \App\Services\M2MAuthService::getToken();

        if (!$token) {
            Log::error('SOAP Audit Error: Authorization Bearer token is missing.');
            return null;
        }

        $jsonPayload = json_encode($data);

        $soapEnvelope = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>{$teamId}</iae:TeamID>
            <iae:ActivityName>{$activityName}</iae:ActivityName>
            <iae:LogContent><![CDATA[{$jsonPayload}]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>
XML;

        try {
            Log::info("Sending SOAP Audit request to {$soapUrl}", ['team_id' => $teamId, 'activity' => $activityName]);
            
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://iae.central/audit/AuditRequest',
            ])
            ->withToken($token)
            ->withBody($soapEnvelope, 'text/xml')
            ->post($soapUrl);

            if ($response->failed()) {
                Log::error('SOAP Audit HTTP Request Failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $responseBody = $response->body();
            Log::info('SOAP Audit Response Received', ['body' => $responseBody]);

            if (preg_match('/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/i', $responseBody, $matches)) {
                return $matches[1];
            } elseif (preg_match('/<ReceiptNumber>(.*?)<\/ReceiptNumber>/i', $responseBody, $matches)) {
                return $matches[1];
            }

            if (preg_match('/<iae:Status>SUCCESS<\/iae:Status>/i', $responseBody) || preg_match('/<Status>SUCCESS<\/Status>/i', $responseBody)) {
                Log::warning('SOAP Audit reported SUCCESS but ReceiptNumber was not found in response.');
                return 'IAE-LOG-MOCKED-' . time();
            }

            Log::error('SOAP Audit Error: ReceiptNumber not found in response XML.');
            return null;

        } catch (\Exception $e) {
            Log::error('SOAP Audit Exception occurred', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
