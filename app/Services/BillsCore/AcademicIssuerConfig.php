<?php

namespace App\Services\BillsCore;

use App\Models\NcfSequence;
use App\Models\Setting;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;

/**
 * AcademicIssuerConfig — Adapter connecting Academic+ settings & NcfSequence
 * to the embedded Gridbase\BillsCore engine.
 */
class AcademicIssuerConfig implements IssuerConfigInterface
{
    public function toSettingsArray(): array
    {
        return [
            'company_tax_id' => $this->getEmitterRnc(),
            'dgii_razon_social' => $this->getEmitterName(),
            'dgii_nombre_comercial' => Setting::get('institution_short_name') ?: $this->getEmitterName(),
            'company_address' => Setting::get('institution_address') ?: 'Santo Domingo, D.N.',
            'dgii_municipio' => Setting::get('institution_municipio') ?: '010100',
            'dgii_provincia' => Setting::get('institution_provincia') ?: '010000',
            'company_phone' => Setting::get('institution_phone') ?: '',
            'company_email' => Setting::get('institution_email') ?: '',
            'company_website' => Setting::get('institution_website') ?: '',
            'dgii_env' => $this->getEnvironment(),
            'dgii_certificate_path' => Setting::get('ecf_certificate_path') ?: '',
            'dgii_certificate_password' => Setting::get('ecf_certificate_password') ?: '',
            'dgii_ncf_expiry_date' => '2027-12-31',
        ];
    }

    public function getEmitterRnc(): string
    {
        $rnc = Setting::get('ecf_rnc_emisor')
            ?: Setting::get('institution_rnc')
            ?: '101142456';

        return preg_replace('/[^0-9]/', '', (string) $rnc) ?: '101142456';
    }

    public function getEmitterName(): string
    {
        return (string) (Setting::get('institution_name') ?: 'UNIVERSIDAD NACIONAL TECNOLÓGICA');
    }

    public function getEnvironment(): string
    {
        $env = strtolower((string) (Setting::get('ecf_environment') ?: 'testing'));
        if (in_array($env, ['production', 'prod', 'ecf'], true)) {
            return 'production';
        }
        if (in_array($env, ['certification', 'certecf', 'cert'], true)) {
            return 'certification';
        }
        return 'testing';
    }

    public function isElectronicBillingEnabled(): bool
    {
        $val = Setting::get('enable_electronic_billing', 'true');
        return $val === 'true' || $val === true || $val === '1' || $val === 1;
    }

    public function hasActiveCertificate(): bool
    {
        $certPath = Setting::get('ecf_certificate_path');
        if (!$certPath) {
            return false;
        }
        $fullPath = str_starts_with($certPath, '/')
            ? $certPath
            : storage_path('app/' . ltrim($certPath, '/'));

        return file_exists($fullPath);
    }

    public function allocateNextNcf(string $typeCode): ?array
    {
        $typeCode = str_pad(preg_replace('/[^0-9]/', '', $typeCode), 2, '0', STR_PAD_LEFT);

        $sequenceModel = NcfSequence::where('type_code', $typeCode)
            ->where('is_active', true)
            ->first();

        if (!$sequenceModel) {
            return null;
        }

        $ncf = $sequenceModel->getNextNcf();
        if (!$ncf) {
            return null;
        }

        return [
            'ncf' => $ncf,
            'type_code' => $sequenceModel->type_code,
            'expiration_date' => $sequenceModel->expiration_date
                ? $sequenceModel->expiration_date->format('Y-m-d')
                : '2027-12-31',
        ];
    }
}
