<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de verificación de RNC/Cédula en la DGII con sistema de fallback.
 *
 * Estrategia de proveedores (en orden de prioridad):
 *   1. DGII Portal Scraper — Web scraping del formulario oficial (más confiable históricamente)
 *   2. DGII SOAP (WSMovilDGII) — Webservice SOAP del servicio móvil
 *   3. Padrón Electoral — Solo para cédulas (11 dígitos), como último recurso
 *
 * Cachea resultados exitosos por 24h. Los errores NO se cachean.
 */
class DgiiRncLookupService
{
    /**
     * URL del portal oficial de consultas DGII.
     */
    protected string $portalUrl = 'https://dgii.gov.do/app/WebApps/ConsultasWeb2/ConsultasWeb/consultas/rnc.aspx';

    /**
     * URL del webservice SOAP móvil de la DGII.
     */
    protected string $soapUrl = 'https://dgii.gov.do/wsMovilDGII/WSMovilDGII.asmx';

    /**
     * Busca un RNC (9 dígitos) o Cédula (11 dígitos) para obtener el nombre/razón social.
     * Intenta múltiples proveedores en cascada si el principal falla.
     *
     * @param string $rnc RNC o Cédula con o sin guiones
     * @return array{nombre: string, tipo: string, rnc: string, status: string, source: string}|null
     */
    public function lookup(string $rnc): ?array
    {
        $cleanRnc = preg_replace('/[^0-9]/', '', $rnc);

        if (empty($cleanRnc) || (strlen($cleanRnc) !== 9 && strlen($cleanRnc) !== 11)) {
            return null;
        }

        // Verificar caché primero
        $cacheKey = "dgii_lookup_{$cleanRnc}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Intentar proveedores en cascada
        $result = $this->tryPortalScraper($cleanRnc);

        if (!$result) {
            Log::info("[DGII Service] Portal scraper falló para {$cleanRnc}, intentando SOAP...");
            $result = $this->trySoapService($cleanRnc);
        }

        if (!$result && strlen($cleanRnc) === 11) {
            Log::info("[DGII Service] SOAP falló para {$cleanRnc}, intentando padrón electoral...");
            $result = $this->tryPadronFallback($cleanRnc);
        }

        // Solo cachear resultados exitosos (24 horas)
        if ($result) {
            Cache::put($cacheKey, $result, now()->addHours(24));
        } else {
            Log::info("[DGII Service] Todos los proveedores fallaron para: {$cleanRnc}");
        }

        return $result;
    }

    /**
     * Proveedor #1: Web scraping del portal oficial DGII.
     * Simula un formulario ASP.NET para consultar el RNC/Cédula.
     */
    protected function tryPortalScraper(string $rnc): ?array
    {
        try {
            $cookieFile = storage_path('app/dgii_cookies_' . md5($rnc) . '.txt');

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->portalUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR      => $cookieFile,
                CURLOPT_COOKIEFILE     => $cookieFile,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
            $htmlGet = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (!$htmlGet || $httpCode !== 200) {
                $error = curl_error($ch);
                curl_close($ch);
                @unlink($cookieFile);
                Log::warning("[DGII Scraper] GET inicial falló: HTTP {$httpCode}, Error: {$error}");
                return null;
            }

            // Extraer tokens ASP.NET
            $viewstate = $this->extractField($htmlGet, '__VIEWSTATE');
            $viewstateGen = $this->extractField($htmlGet, '__VIEWSTATEGENERATOR');
            $eventValidation = $this->extractField($htmlGet, '__EVENTVALIDATION');

            if (empty($viewstate) || empty($eventValidation)) {
                curl_close($ch);
                @unlink($cookieFile);
                Log::warning("[DGII Scraper] No se encontraron tokens ASPX — posible cambio en el portal.");
                return null;
            }

            // Enviar consulta POST
            $postData = http_build_query([
                '__EVENTTARGET'       => '',
                '__EVENTARGUMENT'     => '',
                '__VIEWSTATE'         => $viewstate,
                '__VIEWSTATEGENERATOR'=> $viewstateGen,
                '__EVENTVALIDATION'   => $eventValidation,
                'ctl00$cphMain$txtRNCCedula'      => $rnc,
                'ctl00$cphMain$btnBuscarPorRNC'    => 'BUSCAR',
            ]);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $htmlPost = curl_exec($ch);
            $httpCodePost = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            @unlink($cookieFile); // Limpiar cookies temporales

            if (!$htmlPost || $httpCodePost !== 200) {
                Log::warning("[DGII Scraper] POST de búsqueda falló: HTTP {$httpCodePost}");
                return null;
            }

            // Extraer Razón Social
            if (preg_match('/Nombre\/Raz\&#243;n Social.*?<\/td>\s*<td>([^<]+)<\/td>/is', $htmlPost, $matches)) {
                $nombre = trim($matches[1]);

                // Intentar extraer el Estado también
                $status = 'ACTIVO';
                if (preg_match('/Estado.*?<\/td>\s*<td>([^<]+)<\/td>/is', $htmlPost, $statusMatches)) {
                    $status = strtoupper(trim($statusMatches[1]));
                }

                return [
                    'nombre' => mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8'),
                    'tipo'   => strlen($rnc) === 9 ? 'Juridica' : 'Fisica',
                    'rnc'    => $rnc,
                    'status' => $status,
                    'source' => 'dgii_portal',
                ];
            }

            Log::info("[DGII Scraper] RNC/Cédula no encontrado en el portal: {$rnc}");
            return null;

        } catch (\Exception $e) {
            Log::error("[DGII Scraper] Excepción para {$rnc}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Proveedor #2: Webservice SOAP de la DGII (servicio móvil).
     * Más estable que el scraping, pero a veces redirige o no responde.
     */
    protected function trySoapService(string $rnc): ?array
    {
        try {
            $soapBody = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://dgii.gov.do/">
  <soap:Body>
    <tns:GetContribuyentes>
      <tns:value>' . htmlspecialchars($rnc) . '</tns:value>
      <tns:patronBusqueda>0</tns:patronBusqueda>
      <tns:inicioFilas>1</tns:inicioFilas>
      <tns:filaFilas>1</tns:filaFilas>
      <tns:RONE>1</tns:RONE>
    </tns:GetContribuyentes>
  </soap:Body>
</soap:Envelope>';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->soapUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $soapBody,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: "http://dgii.gov.do/GetContribuyentes"',
                ],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if (!$response || $httpCode !== 200) {
                Log::warning("[DGII SOAP] HTTP {$httpCode}, Error: {$error}");
                return null;
            }

            // Extraer datos del XML de respuesta
            $nombre = null;
            $status = 'ACTIVO';

            if (preg_match('/RAZON_SOCIAL>([^<]+)</i', $response, $m)) {
                $nombre = trim($m[1]);
            } elseif (preg_match('/NOMBRE_COMERCIAL>([^<]+)</i', $response, $m)) {
                $nombre = trim($m[1]);
            }

            if (preg_match('/ESTATUS>([^<]+)</i', $response, $m)) {
                $status = strtoupper(trim($m[1]));
            }

            if ($nombre) {
                return [
                    'nombre' => mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8'),
                    'tipo'   => strlen($rnc) === 9 ? 'Juridica' : 'Fisica',
                    'rnc'    => $rnc,
                    'status' => $status,
                    'source' => 'dgii_soap',
                ];
            }

            Log::info("[DGII SOAP] No se encontraron datos para: {$rnc}");
            return null;

        } catch (\Exception $e) {
            Log::error("[DGII SOAP] Excepción para {$rnc}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Proveedor #3: API del Padrón Electoral (solo para cédulas de 11 dígitos).
     * Último recurso — obtiene nombre de la persona física.
     */
    protected function tryPadronFallback(string $cedula): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://citaslanuevalicencia.lat.do/api/public/validate-cedula', [
                    'cedula' => $cedula,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['found']) && !empty($data['citizen'])) {
                    $citizen = $data['citizen'];
                    $nombre = trim(
                        ($citizen['nombres'] ?? '') . ' ' .
                        ($citizen['apellido1'] ?? '') . ' ' .
                        ($citizen['apellido2'] ?? '')
                    );

                    return [
                        'nombre' => mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8'),
                        'tipo'   => 'Fisica',
                        'rnc'    => $cedula,
                        'status' => 'ACTIVO',
                        'source' => 'padron_fallback',
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::warning("[DGII Padrón Fallback] Excepción: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extrae un campo hidden de un formulario ASP.NET.
     */
    private function extractField(string $html, string $id): string
    {
        if (preg_match('/<input type="hidden" name="' . preg_quote($id, '/') . '" id="' . preg_quote($id, '/') . '" value="([^"]*)"/', $html, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
