<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SoapAuditClient
 *
 * Mengirim data audit ke IAE Legacy Audit System via SOAP/XML.
 * Endpoint: POST https://iae-sso.virtualfri.id/soap/v1/audit
 * Auth: Bearer M2M token (KEY-MHS-270)
 *
 * Tag wajib: <iae:TeamID>, <iae:ActivityName>, <iae:LogContent> (CDATA JSON)
 * Namespace: http://iae.central/audit
 */
class SoapAuditClient
{
    private string $endpoint;
    private string $namespace;
    private string $teamId;

    public function __construct(private readonly IaeSsoService $ssoService)
    {
        $this->endpoint  = config('services.legacy_audit.endpoint',  'https://iae-sso.virtualfri.id/soap/v1/audit');
        $this->namespace = config('services.legacy_audit.namespace', 'http://iae.central/audit');
        $this->teamId    = config('services.legacy_audit.team_id',   'TEAM-07');
    }

    /**
     * Kirim audit pembuatan jadwal ke IAE Legacy SOAP Service.
     *
     * @param array       $scheduleData Data jadwal yang baru dibuat
     * @param string|null $performedBy  User ID dari JWT (sso_user_id)
     * @param string|null $role         Role dari JWT (sso_role)
     * @return array|null { receipt_number, status } atau null jika gagal
     */
    public function auditScheduleCreation(array $scheduleData, ?string $performedBy = null, ?string $role = null): ?array
    {
        $role        = $role        ?? 'authenticated';
        $performedBy = $performedBy ?? 'system';

        $logContentJson = json_encode([
            'schedule_id'   => $scheduleData['id']            ?? null,
            'driver_name'   => $scheduleData['driver_name']   ?? null,
            'vehicle_id'    => $scheduleData['vehicle_id']    ?? null,
            'plate_number'  => $scheduleData['plate_number']  ?? null,
            'schedule_date' => $scheduleData['schedule_date'] ?? null,
            'shift'         => $scheduleData['shift']         ?? null,
            'status'        => $scheduleData['status']        ?? 'active',
            'performed_by'  => $performedBy,
            'role'          => $role,
            'timestamp'     => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE);

        $xmlEnvelope = $this->buildXmlEnvelope('ScheduleCreated', $logContentJson);

        try {
            $m2mToken = $this->ssoService->getM2MToken();

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=UTF-8',
                'SOAPAction'   => 'AuditRequest',
                'Authorization'=> "Bearer {$m2mToken}",
            ])
            ->timeout(10)
            ->withBody($xmlEnvelope, 'text/xml')
            ->post($this->endpoint);

            if ($response->successful()) {
                $parsed = $this->parseXmlResponse($response->body());
                Log::info('SOAP Audit berhasil', [
                    'receipt_number' => $parsed['receipt_number'] ?? 'N/A',
                    'schedule_id'    => $scheduleData['id']       ?? null,
                ]);
                return $parsed;
            }

            Log::warning('SOAP Audit response error', [
                'http_status' => $response->status(),
                'body'        => substr($response->body(), 0, 500),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('SOAP Audit gagal (non-blocking)', [
                'error'       => $e->getMessage(),
                'schedule_id' => $scheduleData['id'] ?? null,
            ]);
            return null;
        }
    }

    /**
     * Build SOAP XML Envelope sesuai schema IAE.
     *
     * <iae:AuditRequest>
     *   <iae:TeamID>TEAM-07</iae:TeamID>
     *   <iae:ActivityName>ScheduleCreated</iae:ActivityName>
     *   <iae:LogContent><![CDATA[{...json...}]]></iae:LogContent>
     * </iae:AuditRequest>
     */
    private function buildXmlEnvelope(string $activityName, string $logContentJson): string
    {
        $ns       = $this->namespace;
        $teamId   = htmlspecialchars($this->teamId, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $activity = htmlspecialchars($activityName, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="{$ns}">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$teamId}</iae:TeamID>
      <iae:ActivityName>{$activity}</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContentJson}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;
    }

    /**
     * Parse response XML dari IAE SOAP server.
     * Mengambil <iae:Status> dan <iae:ReceiptNumber>.
     */
    private function parseXmlResponse(string $xmlBody): array
    {
        try {
            $xml = simplexml_load_string($xmlBody);
            $xml->registerXPathNamespace('iae', $this->namespace);

            $receiptNumber = (string) ($xml->xpath('//iae:ReceiptNumber')[0] ?? '');
            $status        = (string) ($xml->xpath('//iae:Status')[0]        ?? '');

            return [
                'receipt_number' => $receiptNumber ?: null,
                'status'         => $status        ?: 'UNKNOWN',
            ];
        } catch (\Exception $e) {
            Log::warning('Gagal parse SOAP response', ['error' => $e->getMessage()]);
            return ['receipt_number' => null, 'status' => 'PARSE_ERROR'];
        }
    }
}
