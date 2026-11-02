<?php

namespace Gridbase\BillsCore\Contracts;

interface IssuerConfigInterface
{
    /**
     * Returns the normalized settings array expected by Bills Core DGII services:
     * - company_tax_id (RNC/Cédula of emitter)
     * - dgii_razon_social
     * - dgii_nombre_comercial
     * - company_address
     * - dgii_municipio
     * - dgii_provincia
     * - company_phone
     * - company_email
     * - company_website
     * - dgii_env ('testing' | 'certification' | 'production')
     * - dgii_certificate_path (absolute or relative to storage/app/secure)
     * - dgii_certificate_password
     * - dgii_ncf_expiry_date
     */
    public function toSettingsArray(): array;

    public function getEmitterRnc(): string;

    public function getEmitterName(): string;

    public function getEnvironment(): string;

    public function isElectronicBillingEnabled(): bool;

    public function hasActiveCertificate(): bool;

    /**
     * Atomically reserves and returns the next NCF/e-NCF for the given type code (e.g. '31', '32', '34').
     * Returns ['ncf' => 'E320000000001', 'type_code' => '32', 'expiration_date' => '2027-12-31'] or null.
     */
    public function allocateNextNcf(string $typeCode): ?array;
}
