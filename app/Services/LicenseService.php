<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected $serverUrl;
    protected $licenseKey;
    public $errorMessage = ''; 

    public function __construct()
    {
        $configUrl = config('services.aplusmaster.url');
        $envUrl = env('SAAS_MASTER_URL');
        
        if (empty($configUrl) && empty($envUrl)) {
            $envUrl = $this->readEnvFile('SAAS_MASTER_URL');
        }

        $baseUrl = $configUrl ?? $envUrl;
        
        if (empty($baseUrl)) {
            $baseUrl = 'https://gestion.90s.agency/api/v1/validate-license'; 
        }

        $baseUrl = rtrim($baseUrl, '/');
        if (!str_contains($baseUrl, 'api/v1/validate-license')) {
            $this->serverUrl = $baseUrl . '/api/v1/validate-license';
        } else {
            $this->serverUrl = $baseUrl;
        }

        $this->licenseKey = config('services.aplusmaster.key');

        if (empty($this->licenseKey)) {
            $this->licenseKey = env('APP_LICENSE_KEY') ?? env('LICENSE_KEY');
        }

        if (empty($this->licenseKey)) {
            $this->licenseKey = $this->readEnvFile('APP_LICENSE_KEY');
            if (empty($this->licenseKey)) {
                $this->licenseKey = $this->readEnvFile('LICENSE_KEY');
            }
        }
    }

    protected function readEnvFile($key)
    {
        try {
            $path = base_path('.env');
            if (!file_exists($path)) {
                return null;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                if (strpos($line, $key . '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    if (trim($name) === $key) {
                        return trim($value, " \t\n\r\0\x0B\"'");
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    public function check(): bool
    {
        if (empty($this->licenseKey)) {
            $this->errorMessage = 'Clave de licencia no configurada (APP_LICENSE_KEY no encontrada).';
            return false;
        }

        $cacheKey = 'license_status_' . $this->licenseKey;
        $cachedResult = Cache::get($cacheKey);
        
        if ($cachedResult !== null) {
            if ($cachedResult === true) {
                return true;
            }
            $this->errorMessage = Cache::get($cacheKey . '_msg', 'Error de validación (Caché).');
        }

        $isValid = $this->validateRemote();
        $ttl = $isValid ? 300 : 10; 
        Cache::put($cacheKey, $isValid, $ttl);
        
        if (!$isValid) {
            Cache::put($cacheKey . '_msg', $this->errorMessage, $ttl);
        }

        return $isValid;
    }

    public function validateRemote(): bool
    {
        try {
            $domain = request()->getHost();
            
            $response = Http::withoutVerifying()
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->post($this->serverUrl, [
                    'license_key' => $this->licenseKey,
                    'domain' => $domain,
                ]);

            $data = $response->json();

            if ($response->successful()) {
                if ((isset($data['status']) && $data['status'] === 'success') || 
                    (isset($data['valid']) && $data['valid'] === true)) {
                    
                    if (isset($data['data']['features']) && is_array($data['data']['features'])) {
                        Cache::put('saas_active_features', $data['data']['features'], 300);
                    }
                    
                    if (isset($data['data']['plan_name'])) {
                        Cache::put('saas_plan_name', $data['data']['plan_name'], 300);
                    }

                    // --- GUARDAMOS EL MODO ACADÉMICO ---
                    if (isset($data['data']['academic_mode'])) {
                        Cache::put('saas_academic_mode', $data['data']['academic_mode'], 300);
                    }
                    // -----------------------------------

                    return true;
                }
                
                $this->errorMessage = $data['message'] ?? 'Respuesta exitosa pero inválida del servidor.';
                return false;
            }

            if ($response->status() >= 400 && $response->status() < 500) {
                $this->errorMessage = $data['message'] ?? 'Licencia rechazada por el servidor maestro.';
                return false;
            }

            $this->errorMessage = 'Error de conexión con el servidor de licencias (' . $response->status() . ')';
            return false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error de conexión: ' . $e->getMessage();
            return false;
        }
    }
}