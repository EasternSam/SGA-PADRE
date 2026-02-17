<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected $serverUrl;
    protected $licenseKey;
    public $errorMessage = ''; // Propiedad pública para acceder al mensaje desde el middleware

    public function __construct()
    {
        // 1. URL: Intentar config, luego env, luego fallback por defecto
        $configUrl = config('services.aplusmaster.url');
        $envUrl = env('SAAS_MASTER_URL');
        
        $baseUrl = $configUrl ?? $envUrl;
        
        // Fallback final si no hay nada configurado
        if (empty($baseUrl)) {
            $baseUrl = '[https://gestion.90s.agency/api/v1/validate-license](https://gestion.90s.agency/api/v1/validate-license)'; 
        }

        // Limpieza de URL
        $baseUrl = rtrim($baseUrl, '/');
        if (!str_contains($baseUrl, 'api/v1/validate-license')) {
            $this->serverUrl = $baseUrl . '/api/v1/validate-license';
        } else {
            $this->serverUrl = $baseUrl;
        }

        // 2. KEY: Intentar config, luego env directo
        $this->licenseKey = config('services.aplusmaster.key') ?? env('LICENSE_KEY');
        
        // Debug agresivo en constructor si está vacía
        if (empty($this->licenseKey)) {
            Log::warning("LICENSE DEBUG: La clave está vacía en el constructor.");
            Log::warning("LICENSE DEBUG: config('services.aplusmaster.key') devuelve: " . var_export(config('services.aplusmaster.key'), true));
            Log::warning("LICENSE DEBUG: env('LICENSE_KEY') devuelve: " . var_export(env('LICENSE_KEY'), true));
        }
    }

    public function check(): bool
    {
        if (empty($this->licenseKey)) {
            $this->errorMessage = 'Clave de licencia no configurada. Revise los logs (storage/logs/laravel.log) para ver detalles.';
            return false;
        }

        // Cache: Si es exitoso guardamos por 5 min, si falla 10 segundos
        $cacheKey = 'license_status_' . $this->licenseKey;
        
        $cachedResult = Cache::get($cacheKey);
        
        if ($cachedResult !== null) {
            if ($cachedResult === true) {
                return true;
            }
            $this->errorMessage = Cache::get($cacheKey . '_msg', 'Error de validación (Caché).');
            // Comentar el return para forzar validación si estás depurando
            // return false; 
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
            
            // Ignorar verificación SSL
            $response = Http::withoutVerifying()
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->post($this->serverUrl, [
                    'license_key' => $this->licenseKey,
                    'domain' => $domain,
                ]);

            $data = $response->json();

            // CASO 1: Respuesta Exitosa (200 OK)
            if ($response->successful()) {
                Log::info("CLIENTE: Respuesta del Maestro (Éxito):", $data ?? []);

                if ((isset($data['status']) && $data['status'] === 'success') || 
                    (isset($data['valid']) && $data['valid'] === true)) {
                    return true;
                }
                
                $this->errorMessage = $data['message'] ?? 'Respuesta exitosa pero inválida del servidor.';
                Log::warning("CLIENTE: " . $this->errorMessage);
                return false;
            }

            // CASO 2: Respuesta de Error Controlada
            if ($response->status() >= 400 && $response->status() < 500) {
                $this->errorMessage = $data['message'] ?? 'Licencia rechazada por el servidor maestro.';
                Log::warning("CLIENTE: Validación fallida: " . $this->errorMessage);
                return false;
            }

            // CASO 3: Error de Servidor
            $this->errorMessage = 'Error de conexión con el servidor de licencias (' . $response->status() . ')';
            Log::error("CLIENTE: Error HTTP {$response->status()}", ['body' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error de conexión: ' . $e->getMessage();
            Log::error('CLIENTE: Excepción conexión: ' . $e->getMessage());
            return false;
        }
    }
}