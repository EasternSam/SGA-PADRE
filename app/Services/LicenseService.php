<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected $serverUrl;
    protected $licenseKey;

    public function __construct()
    {
        // Obtener URL base y asegurar formato correcto
        $baseUrl = config('services.aplusmaster.url') ?? env('SAAS_MASTER_URL');
        $baseUrl = rtrim($baseUrl, '/');

        if (!str_contains($baseUrl, 'api/v1/validate-license')) {
            $this->serverUrl = $baseUrl . '/api/v1/validate-license';
        } else {
            $this->serverUrl = $baseUrl;
        }

        $this->licenseKey = config('services.aplusmaster.key') ?? env('LICENSE_KEY');
    }

    public function check(): bool
    {
        if (empty($this->licenseKey)) {
            return false;
        }

        // Cache corto para pruebas (1 min)
        return Cache::remember('license_status_' . $this->licenseKey, 60, function () {
            return $this->validateRemote();
        });
    }

    public function validateRemote(): bool
    {
        try {
            $domain = request()->getHost();
            
            // Ignorar verificación SSL
            $response = Http::withoutVerifying()
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->post($this->serverUrl, [
                    'license_key' => $this->licenseKey,
                    'domain' => $domain,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // DIAGNOSTICO: Ver qué nos devuelve exactamente el maestro
                Log::info("CLIENTE: Respuesta del Maestro:", $data);

                // Aceptar si status es success O si valid es true (flexibilidad)
                if ((isset($data['status']) && $data['status'] === 'success') || 
                    (isset($data['valid']) && $data['valid'] === true)) {
                    return true;
                }
                
                Log::warning("CLIENTE: Respuesta exitosa (200) pero contenido inválido.");
                return false;
            }

            Log::error("CLIENTE: Error HTTP {$response->status()}", ['body' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            Log::error('CLIENTE: Excepción conexión: ' . $e->getMessage());
            return false;
        }
    }
}