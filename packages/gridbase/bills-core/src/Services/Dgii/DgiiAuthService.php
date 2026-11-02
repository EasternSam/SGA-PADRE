<?php

namespace Gridbase\BillsCore\Services\Dgii;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Gridbase Bills Core — DgiiAuthService
 *
 * Implements the official DGII e-CF Semilla -> Signed XML -> Bearer Token authentication flow.
 */
class DgiiAuthService
{
    public const URLS = [
        'testing' => [
            'semilla' => 'https://ecf.dgii.gov.do/testecf/autenticacion/api/Autenticacion/Semilla',
            'validar' => 'https://ecf.dgii.gov.do/testecf/autenticacion/api/Autenticacion/ValidarSemilla',
        ],
        'certification' => [
            'semilla' => 'https://ecf.dgii.gov.do/certecf/autenticacion/api/Autenticacion/Semilla',
            'validar' => 'https://ecf.dgii.gov.do/certecf/autenticacion/api/Autenticacion/ValidarSemilla',
        ],
        'production' => [
            'semilla' => 'https://ecf.dgii.gov.do/ecf/autenticacion/api/Autenticacion/Semilla',
            'validar' => 'https://ecf.dgii.gov.do/ecf/autenticacion/api/Autenticacion/ValidarSemilla',
        ],
    ];

    public function __construct(
        protected XmlSignatureService $signatureService
    ) {
    }

    /**
     * Retrieves a cached or fresh DGII Bearer token for the given issuer settings.
     */
    public function getValidToken(array $settings): string
    {
        $env = $settings['dgii_env'] ?? 'testing';
        $rnc = preg_replace('/[^0-9]/', '', (string) ($settings['company_tax_id'] ?? 'default'));
        $cacheKey = "bills_core_dgii_token_{$env}_{$rnc}";

        if (class_exists(Cache::class) && Cache::has($cacheKey)) {
            return (string) Cache::get($cacheKey);
        }

        $certRelPath = (string) ($settings['dgii_certificate_path'] ?? '');
        $certPass = (string) ($settings['dgii_certificate_password'] ?? '');

        if ($certRelPath === '') {
            throw new Exception('No se ha configurado un Certificado Digital (.p12) para autenticar ante la DGII.');
        }

        $certFullPath = file_exists($certRelPath)
            ? $certRelPath
            : (function_exists('storage_path') ? storage_path('app/secure/' . ltrim($certRelPath, '/')) : $certRelPath);

        // 1. Solicitar Semilla XML
        $semillaXml = $this->requestSemilla($env);

        // 2. Firmar Semilla con XMLDSig RSA-SHA256
        $signedSemilla = $this->signatureService->signXml($semillaXml, $certFullPath, $certPass);

        // 3. Intercambiar Semilla Firmada por Token JWT
        $tokenData = $this->validateSemilla($signedSemilla, $env);
        $token = (string) ($tokenData['token'] ?? '');

        if ($token === '') {
            throw new Exception('La DGII no devolvió un token válido en la respuesta de ValidarSemilla.');
        }

        if (class_exists(Cache::class)) {
            Cache::put($cacheKey, $token, now()->addMinutes(50));
        }

        return $token;
    }

    protected function requestSemilla(string $env): string
    {
        $url = self::URLS[$env]['semilla'] ?? self::URLS['testing']['semilla'];

        $response = Http::timeout(15)
            ->withHeaders(['Accept' => 'application/xml'])
            ->get($url);

        if (!$response->successful()) {
            throw new Exception("Error al solicitar Semilla DGII ({$response->status()}): " . $response->body());
        }

        return $response->body();
    }

    protected function validateSemilla(string $signedXml, string $env): array
    {
        $url = self::URLS[$env]['validar'] ?? self::URLS['testing']['validar'];

        $response = Http::timeout(20)
            ->attach('xml', $signedXml, 'semilla_firmada.xml', ['Content-Type' => 'text/xml'])
            ->post($url);

        if (!$response->successful()) {
            throw new Exception("Error al validar Semilla en DGII ({$response->status()}): " . $response->body());
        }

        return (array) $response->json();
    }
}
