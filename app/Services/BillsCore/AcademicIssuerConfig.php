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
            'dgii_razon_social' => Setting::get('ecf_razon_social') ?: $this->getEmitterName(),
            'dgii_nombre_comercial' => Setting::get('ecf_nombre_comercial') ?: (Setting::get('institution_short_name') ?: $this->getEmitterName()),
            'company_address' => Setting::get('contact_address') ?: (Setting::get('institution_address') ?: 'Santo Domingo, D.N.'),
            'dgii_municipio' => Setting::get('ecf_municipio') ?: (Setting::get('institution_municipio') ?: '010100'),
            'dgii_provincia' => Setting::get('ecf_provincia') ?: (Setting::get('institution_provincia') ?: '010000'),
            'company_phone' => Setting::get('contact_phone') ?: (Setting::get('institution_phone') ?: ''),
            'company_email' => Setting::get('support_email') ?: (Setting::get('institution_email') ?: ''),
            'company_website' => Setting::get('website_url') ?: (Setting::get('institution_website') ?: ''),
            'dgii_env' => $this->getEnvironment(),
            'dgii_certificate_path' => Setting::get('ecf_certificate_path') ?: '',
            'dgii_certificate_password' => Setting::get('ecf_certificate_password') ?: '',
            'dgii_ncf_expiry_date' => Setting::get('ecf_ncf_expiry_date') ?: '2028-12-31',
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
        return (string) (Setting::get('ecf_razon_social') ?: Setting::get('institution_name') ?: 'UNIVERSIDAD NACIONAL TECNOLÓGICA');
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
        if (str_starts_with($certPath, '/') && file_exists($certPath)) {
            return true;
        }
        $candidates = [
            storage_path('app/secure/' . ltrim($certPath, '/')),
            storage_path('app/' . ltrim($certPath, '/')),
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return true;
            }
        }
        return false;
    }

    public function allocateNextNcf(string $typeCode): ?array
    {
        NcfSequence::ensureDefaults();

        $typeCode = str_pad(preg_replace('/[^0-9]/', '', $typeCode), 2, '0', STR_PAD_LEFT);
        $altMap = [
            '31' => '01', '01' => '31',
            '32' => '02', '02' => '32',
            '33' => '03', '03' => '33',
            '34' => '04', '04' => '34',
            '41' => '11', '11' => '41',
            '43' => '13', '13' => '43',
            '44' => '14', '14' => '44',
            '45' => '15', '15' => '45',
        ];

        $sequenceModel = NcfSequence::where('type_code', $typeCode)
            ->where('is_active', true)
            ->first();

        if (!$sequenceModel && isset($altMap[$typeCode])) {
            $sequenceModel = NcfSequence::where('type_code', $altMap[$typeCode])
                ->where('is_active', true)
                ->first();
        }

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
                : '2028-12-31',
        ];
    }
}
