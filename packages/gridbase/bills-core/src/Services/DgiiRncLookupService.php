<?php

namespace Gridbase\BillsCore\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DgiiRncLookupService — Gridbase Bills Core Engine
 *
 * 3-Tier Dominican RNC/Cédula Lookup:
 * Tier 1: DGII Citizens Portal Scraper
 * Tier 2: DGII Official SOAP WebService
 * Tier 3: Padrón Electoral / Public Registry Fallback
 */
class DgiiRncLookupService
{
    private const PORTAL_URL = 'https://dgii.gov.do/app/WebApps/ConsultasWeb2/ConsultasWeb/consultas/rnc.aspx';
    private const WSDL_URL = 'https://dgii.gov.do/wsMovilDGII/WSMovilDGII.asmx';

    /**
     * Look up RNC (9 digits) or Cédula (11 digits).
     */
    public function lookup(string $rncOrCedula): ?array
    {
        $clean = preg_replace('/[^0-9]/', '', $rncOrCedula);

        if (strlen($clean) !== 9 && strlen($clean) !== 11) {
            return null;
        }

        $result = $this->lookupViaPortalScraper($clean);
        if ($result !== null) {
            return $result;
        }

        $result = $this->lookupViaSoap($clean);
        if ($result !== null) {
            return $result;
        }

        if (strlen($clean) === 11) {
            return $this->lookupViaExternalApi($clean);
        }

        return null;
    }

    protected function lookupViaPortalScraper(string $clean): ?array
    {
        try {
            $cookies = [];
            $getRes = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            ])->withoutVerifying()->timeout(8)->get(self::PORTAL_URL);

            if (!$getRes->successful()) {
                return null;
            }

            foreach ($getRes->cookies() as $cookie) {
                $cookies[$cookie->getName()] = $cookie->getValue();
            }

            $html = $getRes->body();
            preg_match('/id="__VIEWSTATE"\s+value="([^"]*)"/i', $html, $vs);
            preg_match('/id="__VIEWSTATEGENERATOR"\s+value="([^"]*)"/i', $html, $vsg);
            preg_match('/id="__EVENTVALIDATION"\s+value="([^"]*)"/i', $html, $ev);

            if (empty($vs[1]) || empty($ev[1])) {
                return null;
            }

            $postRes = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => self::PORTAL_URL,
            ])->withCookies($cookies, 'dgii.gov.do')
                ->withoutVerifying()
                ->timeout(8)
                ->asForm()
                ->post(self::PORTAL_URL, [
                    'ctl00$smMain' => 'ctl00$cphMain$upBusqueda|ctl00$cphMain$btnBuscarPorRNC',
                    '__EVENTTARGET' => '',
                    '__EVENTARGUMENT' => '',
                    '__VIEWSTATE' => $vs[1],
                    '__VIEWSTATEGENERATOR' => $vsg[1] ?? '',
                    '__EVENTVALIDATION' => $ev[1],
                    'ctl00$cphMain$txtRNCCedula' => $clean,
                    'ctl00$cphMain$txtRazonSocial' => '',
                    '__ASYNCPOST' => 'true',
                    'ctl00$cphMain$btnBuscarPorRNC' => 'BUSCAR',
                ]);

            if (!$postRes->successful()) {
                return null;
            }

            $body = $postRes->body();
            if (str_contains($body, 'No existen registros') || !str_contains($body, 'dvDatosContribuyentes')) {
                return null;
            }

            preg_match_all('/<tr[^>]*>\s*<td[^>]*>(.*?)<\/td>\s*<td[^>]*>(.*?)<\/td>\s*<\/tr>/is', $body, $matches, PREG_SET_ORDER);

            $parsed = [];
            foreach ($matches as $match) {
                $key = trim(strip_tags($match[1]));
                $val = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $parsed[$key] = $val;
            }

            $nombre = '';
            $comercial = '';
            $estado = 'ACTIVO';
            $regimen = '';
            $actividad = '';

            foreach ($parsed as $k => $v) {
                if (stripos($k, 'Raz') !== false) {
                    $nombre = $v;
                } elseif (stripos($k, 'Comercial') !== false) {
                    $comercial = $v;
                } elseif (stripos($k, 'Estado') !== false) {
                    $estado = $v;
                } elseif (stripos($k, 'gimen') !== false) {
                    $regimen = $v;
                } elseif (stripos($k, 'Actividad') !== false) {
                    $actividad = $v;
                }
            }

            if ($nombre === '') {
                return null;
            }

            return [
                'rnc' => $clean,
                'name' => $nombre,
                'commercial_name' => $comercial !== '' ? $comercial : $nombre,
                'status' => $estado,
                'Activity' => $actividad,
                'regime' => $regimen,
                'is_active' => stripos($estado, 'ACTIVO') !== false || stripos($estado, 'NORMAL') !== false,
            ];
        } catch (\Throwable $e) {
            Log::debug('BillsCore DGII Portal Scraper fallback: ' . $e->getMessage());
            return null;
        }
    }

    protected function lookupViaSoap(string $clean): ?array
    {
        try {
            $soapBody = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetContribuyentes xmlns="http://dgii.gov.do/">
      <value>' . htmlspecialchars($clean, ENT_XML1) . '</value>
      <patronBusqueda>0</patronBusqueda>
      <inicioFilas>0</inicioFilas>
      <filaFilas>1</filaFilas>
      <IMEI></IMEI>
    </GetContribuyentes>
  </soap:Body>
</soap:Envelope>';

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://dgii.gov.do/GetContribuyentes',
            ])->timeout(8)->withoutVerifying()->send('POST', self::WSDL_URL, [
                'body' => $soapBody,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $xml = @simplexml_load_string($response->body());
            if ($xml === false) {
                return null;
            }

            $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('dgii', 'http://dgii.gov.do/');
            $resultNodes = $xml->xpath('//dgii:GetContribuyentesResult');

            if (empty($resultNodes)) {
                return null;
            }

            $rawResult = (string) $resultNodes[0];
            if ($rawResult === '' || $rawResult === '0') {
                return null;
            }

            $data = json_decode($rawResult, true);
            if (is_array($data)) {
                $entry = isset($data[0]) ? $data[0] : $data;
                $name = trim((string) ($entry['RGE_NOMBRE'] ?? $entry['NOMBRE'] ?? ''));
                if ($name === '') {
                    return null;
                }
                $status = strtoupper(trim((string) ($entry['ESTATUS'] ?? 'ACTIVO')));
                return [
                    'rnc' => $clean,
                    'name' => $name,
                    'commercial_name' => trim((string) ($entry['NOMBRE_COMERCIAL'] ?? $name)),
                    'status' => $status,
                    'Activity' => trim((string) ($entry['ACTIVIDAD_ECONOMICA'] ?? '')),
                    'regime' => trim((string) ($entry['REGIMEN_PAGOS'] ?? 'NORMAL')),
                    'is_active' => in_array($status, ['ACTIVO', 'NORMAL', '2'], true),
                ];
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function lookupViaExternalApi(string $cedula): ?array
    {
        try {
            $res = Http::withoutVerifying()->timeout(6)->get("https://api.digital.gob.do/v3/cedulas/{$cedula}/validate");
            $compRes = Http::withoutVerifying()->timeout(6)->get("https://compulaboratorioclinico.com/api/cedula/{$cedula}");
            if ($compRes->successful()) {
                $data = $compRes->json();
                if (!empty($data['nombres'])) {
                    $fullName = trim(($data['nombres'] ?? '') . ' ' . ($data['apellido1'] ?? '') . ' ' . ($data['apellido2'] ?? ''));
                    return [
                        'rnc' => $cedula,
                        'name' => $fullName,
                        'commercial_name' => $fullName,
                        'status' => 'NO REGISTRADO EN DGII (CÉDULA VÁLIDA)',
                        'Activity' => 'PERSONA FÍSICA',
                        'regime' => 'N/A',
                        'is_active' => true,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silent fallback
        }

        return null;
    }
}
