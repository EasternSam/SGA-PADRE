<?php

namespace Gridbase\BillsCore\Services\Dgii;

use Exception;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Gridbase Bills Core — EcfManagerService
 * Orchestrates e-CF issuance:
 * 1. Atomic e-NCF reservation
 * 2. Official DGII XML construction
 * 3. XMLDSig RSA-SHA256 signing (or deterministic security code when in local contingency)
 * 4. DGII submission / RFCE handling
 * 5. Official DGII QR URL generation
 */
class EcfManagerService
{
    public function __construct(
        protected XmlBuilderService $builderService,
        protected XmlSignatureService $signatureService,
        protected DgiiAuthService $authService,
        protected DgiiApiService $apiService,
    ) {
    }

    /**
     * Issues a fiscal document using the embedded Gridbase Bills Core engine.
     *
     * @param array $document Normalized document payload
     * @param IssuerConfigInterface $issuer Host application's issuer configuration adapter
     * @param bool $submitToDgii Whether to transmit immediately to DGII when certificate is active
     * @return array{
     *   ncf: string,
     *   ncf_type: string,
     *   ncf_expiration: ?string,
     *   security_code: string,
     *   dgii_status: string,
     *   dgii_track_id: ?string,
     *   xml_path: ?string,
     *   qr_url: string
     * }
     */
    public function issueDocument(array $document, IssuerConfigInterface $issuer, bool $submitToDgii = false): array
    {
        $typeCode = (string) ($document['ecf_type'] ?? '32');
        $encf = trim((string) ($document['encf'] ?? ''));
        $expirationDate = $document['ncf_expiration'] ?? null;

        if ($encf === '') {
            $allocated = $issuer->allocateNextNcf($typeCode);
            if (!$allocated || empty($allocated['ncf'])) {
                throw new Exception("No hay secuencia activa disponible para el comprobante tipo {$typeCode}.");
            }
            $encf = $allocated['ncf'];
            $expirationDate = $allocated['expiration_date'] ?? $expirationDate;
        }

        $document['ecf_type'] = (int) $typeCode;
        $document['encf'] = $encf;
        $document['ncf_expiration'] = $expirationDate;

        $settings = $issuer->toSettingsArray();
        $rncEmisor = $issuer->getEmitterRnc();

        // Build unsigned e-CF XML using Bills Core XmlBuilderService
        $rawXml = $this->builderService->buildFromArray($document, $settings);
        $xmlPath = null;
        $dgiiStatus = 'issued_local';
        $trackId = null;

        if ($issuer->hasActiveCertificate()) {
            try {
                $certPath = $settings['dgii_certificate_path'];
                if (!file_exists($certPath) && function_exists('storage_path')) {
                    $certPath = storage_path('app/secure/' . ltrim($certPath, '/'));
                }
                $signedXml = $this->signatureService->signXml(
                    $rawXml,
                    $certPath,
                    (string) ($settings['dgii_certificate_password'] ?? '')
                );
                $securityCode = $this->signatureService->getSecurityCode($signedXml);

                if (class_exists(Storage::class)) {
                    $xmlPath = "signed_ecf/{$rncEmisor}{$encf}.xml";
                    Storage::put($xmlPath, $signedXml);
                }

                $dgiiStatus = 'signed';

                if ($submitToDgii) {
                    $token = $this->authService->getValidToken($settings);
                    $isFcUnder250k = ((int) $typeCode === 32) && ((float) ($document['total'] ?? 0) < 250000);

                    if ($isFcUnder250k) {
                        $document['security_code'] = $securityCode;
                        $rfceXml = $this->builderService->buildRfceFromArray($document, $settings);
                        $signedRfce = $this->signatureService->signXml(
                            $rfceXml,
                            $certPath,
                            (string) ($settings['dgii_certificate_password'] ?? '')
                        );
                        $submitResult = $this->apiService->submitInvoice($signedRfce, $token, $issuer->getEnvironment(), true);
                    } else {
                        $submitResult = $this->apiService->submitInvoice($signedXml, $token, $issuer->getEnvironment(), false);
                    }

                    $dgiiStatus = $submitResult['status'] ?? 'contingency';
                    $trackId = $submitResult['track_id'] ?? null;
                }
            } catch (\Throwable $e) {
                if (class_exists(Log::class)) {
                    Log::warning("[BillsCore EcfManagerService] Fallo firmando/enviando con certificado, usando firma determinística local: " . $e->getMessage());
                }
                $securityCode = $this->signatureService->computeDeterministicSecurityCode($rawXml, $rncEmisor, $encf);
                $dgiiStatus = 'contingency';
            }
        } else {
            // Deterministic cryptographic digest of the official e-CF XML (never random mt_rand)
            $securityCode = $this->signatureService->computeDeterministicSecurityCode($rawXml, $rncEmisor, $encf);
        }

        $qrUrl = $this->buildQrUrl(
            $rncEmisor,
            $encf,
            (float) ($document['total'] ?? 0),
            $securityCode,
            $issuer->getEnvironment(),
            (string) ($document['buyer_rnc'] ?? ''),
            (string) ($document['issue_date'] ?? date('Y-m-d'))
        );

        return [
            'ncf' => $encf,
            'ncf_type' => $typeCode,
            'ncf_expiration' => $expirationDate,
            'security_code' => $securityCode,
            'dgii_status' => $dgiiStatus,
            'dgii_track_id' => $trackId,
            'xml_path' => $xmlPath,
            'qr_url' => $qrUrl,
        ];
    }

    /**
     * Generates the official DGII e-CF consultation URL for the printed QR code.
     */
    public function buildQrUrl(
        string $rncEmisor,
        string $encf,
        float $montoTotal,
        string $securityCode,
        string $env = 'testing',
        string $rncComprador = '',
        string $fechaEmision = ''
    ): string {
        $cleanRnc = preg_replace('/[^0-9]/', '', $rncEmisor) ?: '101142456';
        $envPath = match ($env) {
            'production', 'eCF', 'ecf' => 'ecf',
            'certification', 'CerteCF', 'certecf' => 'certecf',
            default => 'testecf',
        };

        $montoFormatted = number_format($montoTotal, 2, '.', '');
        $isFcUnder250k = str_starts_with(strtoupper($encf), 'E32') && $montoTotal < 250000;

        if ($isFcUnder250k) {
            return "https://fc.dgii.gov.do/{$envPath}/ConsultasWeb/Consultas/Factura?RncEmisor={$cleanRnc}&ENCF=" . urlencode($encf) . "&MontoTotal={$montoFormatted}&CodigoSeguridad=" . urlencode($securityCode);
        }

        return "https://ecf.dgii.gov.do/{$envPath}/ConsultasWeb/Consultas/Factura?RncEmisor={$cleanRnc}&ENCF=" . urlencode($encf) . "&MontoTotal={$montoFormatted}&CodigoSeguridad=" . urlencode($securityCode);
    }
}
