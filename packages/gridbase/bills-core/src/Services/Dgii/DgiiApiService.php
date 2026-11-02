<?php

namespace Gridbase\BillsCore\Services\Dgii;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gridbase Bills Core — DgiiApiService
 * Submits signed e-CF / RFCE / ACECF XML documents and polls TrackId validation status on DGII endpoints.
 */
class DgiiApiService
{
    public function submitInvoice(string $signedXml, string $token, string $env, bool $isRfce = false): array
    {
        $envPath = match ($env) {
            'production', 'eCF', 'ecf' => 'ecf',
            'certification', 'CerteCF', 'certecf' => 'certecf',
            default => 'testecf',
        };
        $ecfBaseUrl = "https://ecf.dgii.gov.do/{$envPath}";
        $fcBaseUrl = "https://fc.dgii.gov.do/{$envPath}";

        $endpoint = $isRfce
            ? "{$fcBaseUrl}/recepcionfc/api/recepcion/ecf"
            : "{$ecfBaseUrl}/recepcion/api/facturaselectronicas";

        preg_match('/<RNCEmisor>(\d+)<\/RNCEmisor>/', $signedXml, $rncMatch);
        preg_match('/<eNCF>([^<]+)<\/eNCF>/', $signedXml, $encfMatch);
        $rncEmisor = $rncMatch[1] ?? '000000000';
        $xmlEncf = $encfMatch[1] ?? 'E000000000000';
        $sendFilename = "{$rncEmisor}{$xmlEncf}.xml";

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])
                ->attach('xml', $signedXml, $sendFilename, ['Content-Type' => 'text/xml'])
                ->post($endpoint);

            $responseBody = $response->json();
            $rawBody = $response->body();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'status' => 'contingency',
                    'track_id' => null,
                    'errors' => "HTTP {$response->status()}: " . $rawBody,
                ];
            }

            if (isset($responseBody['trackId'])) {
                return [
                    'success' => true,
                    'status' => 'accepted',
                    'track_id' => $responseBody['trackId'],
                    'errors' => null,
                ];
            }

            if (isset($responseBody['codigo']) && (int) $responseBody['codigo'] === 1) {
                return [
                    'success' => true,
                    'status' => 'accepted',
                    'track_id' => $responseBody['encf'] ?? 'RFCE_ACCEPTED',
                    'errors' => null,
                ];
            }

            if (isset($responseBody['estado']) && strtolower((string) $responseBody['estado']) === 'aceptado') {
                return [
                    'success' => true,
                    'status' => 'accepted',
                    'track_id' => 'SYNC_APPROVED',
                    'errors' => null,
                ];
            }

            if (isset($responseBody['estado']) && strtolower((string) $responseBody['estado']) === 'rechazado') {
                $errMessages = $responseBody['mensajes'] ?? 'Rechazado por la DGII.';
                return [
                    'success' => false,
                    'status' => 'rejected',
                    'track_id' => null,
                    'errors' => is_array($errMessages) ? json_encode($errMessages, JSON_UNESCAPED_UNICODE) : (string) $errMessages,
                ];
            }

            return [
                'success' => false,
                'status' => 'contingency',
                'track_id' => null,
                'errors' => 'Respuesta no reconocida de la DGII: ' . $rawBody,
            ];
        } catch (Exception $e) {
            Log::error("[BillsCore DgiiApiService] Excepción: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'contingency',
                'track_id' => null,
                'errors' => 'Error de conexión: ' . $e->getMessage(),
            ];
        }
    }

    public function queryInvoiceStatus(string $trackId, string $token, string $env): array
    {
        if (in_array($trackId, ['SYNC_APPROVED', 'RFCE_ACCEPTED'], true)) {
            return ['status' => 'accepted', 'errors' => null];
        }

        $envPath = match ($env) {
            'production', 'eCF', 'ecf' => 'ecf',
            'certification', 'CerteCF', 'certecf' => 'certecf',
            default => 'testecf',
        };
        $baseUrl = "https://ecf.dgii.gov.do/{$envPath}";

        try {
            $url = "{$baseUrl}/consultaresultado/api/consultas/estado?trackid={$trackId}";
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if (!$response->successful()) {
                return ['status' => 'pending', 'errors' => "No se pudo contactar DGII. HTTP {$response->status()}"];
            }

            $data = $response->json();
            $codigo = $data['codigo'] ?? null;
            $estado = strtolower((string) ($data['estado'] ?? $data['Estado'] ?? ''));

            if ($codigo === 1 || $estado === 'aceptado') {
                return ['status' => 'accepted', 'errors' => null];
            }
            if ($codigo === 4 || $estado === 'aceptado condicional') {
                return ['status' => 'accepted', 'errors' => 'Aceptado condicional'];
            }
            if ($codigo === 2 || $estado === 'rechazado') {
                return ['status' => 'rejected', 'errors' => json_encode($data['mensajes'] ?? 'Rechazado por la DGII.', JSON_UNESCAPED_UNICODE)];
            }
            if ($codigo === 0 || $estado === 'no encontrado') {
                return ['status' => 'not_found', 'errors' => 'e-CF no encontrado en registros DGII'];
            }

            return ['status' => 'pending', 'errors' => null];
        } catch (Exception $e) {
            return ['status' => 'pending', 'errors' => $e->getMessage()];
        }
    }
}
