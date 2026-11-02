<?php

namespace Gridbase\BillsCore\Services\Dgii;

use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Gridbase Bills Core — XmlSignatureService
 * Signs XML documents using XMLDSig Enveloped Signature for DGII e-CF.
 *
 * Implementation follows the official DGII guide "Firmado de e-CF.pdf" exactly:
 * - SHA256 for SignatureMethod and DigestMethod
 * - C14N(false, false) canonicalization (no exclusive, no comments)
 * - Enveloped-signature transform
 * - Only X509Certificate in KeyInfo (no KeyValue/RSAKeyValue/Exponent)
 * - preserveWhiteSpace = false on DOMDocument
 * - Reference URI="" (empty — signs the whole document)
 */
class XmlSignatureService
{
    /**
     * Signs an XML document using XMLDSig Enveloped signature and a PKCS#12 (.p12/.pfx) certificate.
     */
    public function signXml(string $xmlContent, string $p12Path, string $password): string
    {
        if (!file_exists($p12Path)) {
            throw new Exception("El archivo del certificado digital no existe en la ruta especificada: {$p12Path}");
        }

        $p12Content = file_get_contents($p12Path);
        $certs = [];

        if (!openssl_pkcs12_read($p12Content, $certs, $password)) {
            throw new Exception("Error al leer el certificado digital (.p12). Verifique que la contraseña sea correcta.");
        }

        $privateKey = $certs['pkey'] ?? null;
        $publicCert = $certs['cert'] ?? null;

        if (!$privateKey || !$publicCert) {
            throw new Exception("No se pudo extraer la clave privada o el certificado del archivo .p12.");
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;

        if (!$doc->loadXML($xmlContent)) {
            throw new Exception("El contenido provisto no es un XML válido.");
        }

        if (!$doc->documentElement) {
            throw new Exception("El documento XML no tiene un elemento raíz.");
        }

        $canonicalData = $doc->documentElement->C14N(false, false);
        $digestValue = base64_encode(hash('sha256', $canonicalData, true));

        $dsigNs = 'http://www.w3.org/2000/09/xmldsig#';

        $signatureElement = $doc->createElement('Signature');
        $signatureElement->setAttribute('xmlns', $dsigNs);

        $signedInfoElement = $doc->createElement('SignedInfo');
        $signatureElement->appendChild($signedInfoElement);

        $canonMethodElement = $doc->createElement('CanonicalizationMethod');
        $canonMethodElement->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfoElement->appendChild($canonMethodElement);

        $sigMethodElement = $doc->createElement('SignatureMethod');
        $sigMethodElement->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfoElement->appendChild($sigMethodElement);

        $referenceElement = $doc->createElement('Reference');
        $referenceElement->setAttribute('URI', '');
        $signedInfoElement->appendChild($referenceElement);

        $transformsElement = $doc->createElement('Transforms');
        $referenceElement->appendChild($transformsElement);

        $transformElement = $doc->createElement('Transform');
        $transformElement->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transformsElement->appendChild($transformElement);

        $digestMethodElement = $doc->createElement('DigestMethod');
        $digestMethodElement->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $referenceElement->appendChild($digestMethodElement);

        $digestValueElement = $doc->createElement('DigestValue', $digestValue);
        $referenceElement->appendChild($digestValueElement);

        $signatureValueElement = $doc->createElement('SignatureValue', '');
        $signatureElement->appendChild($signatureValueElement);

        $keyInfoElement = $doc->createElement('KeyInfo');
        $signatureElement->appendChild($keyInfoElement);

        $x509DataElement = $doc->createElement('X509Data');
        $keyInfoElement->appendChild($x509DataElement);

        $rawCert = str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r", "\n", ' '],
            '',
            $publicCert
        );
        $x509CertElement = $doc->createElement('X509Certificate', $rawCert);
        $x509DataElement->appendChild($x509CertElement);

        $doc->documentElement->appendChild($signatureElement);

        $c14nSignedInfo = $signedInfoElement->C14N(false, false);

        $privateKeyResource = openssl_pkey_get_private($privateKey);
        if (!$privateKeyResource) {
            throw new Exception("No se pudo cargar la clave privada del certificado.");
        }

        $signatureRaw = '';
        if (!openssl_sign($c14nSignedInfo, $signatureRaw, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new Exception("Error al generar la firma digital RSA-SHA256.");
        }

        $signatureValueElement->nodeValue = base64_encode($signatureRaw);

        return $doc->saveXML();
    }

    /**
     * Extracts the security code (first 6 characters of the SignatureValue base64)
     * required for the printed representation QR Code.
     */
    public function getSecurityCode(string $signedXmlContent): string
    {
        try {
            $doc = new DOMDocument();
            if ($doc->loadXML($signedXmlContent)) {
                $signatureValueNode = $doc->getElementsByTagName('SignatureValue')->item(0);
                if ($signatureValueNode) {
                    $signatureValue = trim($signatureValueNode->textContent);
                    if ($signatureValue !== '') {
                        return substr($signatureValue, 0, 6);
                    }
                }
            }
        } catch (\Throwable $e) {
            if (class_exists(Log::class)) {
                Log::error("[XmlSignatureService] Error al extraer el código de seguridad: " . $e->getMessage());
            }
        }

        return '000000';
    }

    /**
     * Computes a deterministic 6-character alphanumeric security digest from an unsigned e-CF XML
     * when operating in local/contingency mode before the .p12 certificate is uploaded.
     */
    public function computeDeterministicSecurityCode(string $xmlContent, string $rncEmisor, string $encf): string
    {
        $secret = function_exists('config') ? (string) config('app.key', 'gridbase-bills-core') : 'gridbase-bills-core';
        $rawDigest = base64_encode(hash_hmac('sha256', "{$rncEmisor}|{$encf}|{$xmlContent}", $secret, true));
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $rawDigest);

        return substr(str_pad($clean, 6, '0'), 0, 6);
    }
}
