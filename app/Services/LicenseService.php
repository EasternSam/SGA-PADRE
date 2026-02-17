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
        // 1. URL: Intentar config, luego env
        $configUrl = config('services.aplusmaster.url');
        $envUrl = env('SAAS_MASTER_URL');
        
        // Si fallan, intentar lectura directa del archivo .env
        if (empty($configUrl) && empty($envUrl)) {
            $envUrl = $this->readEnvFile('SAAS_MASTER_URL');
        }

        $baseUrl = $configUrl ?? $envUrl;
        
        // Fallback final
        if (empty($baseUrl)) {
            $baseUrl = 'https://gestion.90s.agency/api/v1/validate-license'; 
        }

        // Limpieza de URL
        $baseUrl = rtrim($baseUrl, '/');
        if (!str_contains($baseUrl, 'api/v1/validate-license')) {
            $this->serverUrl = $baseUrl . '/api/v1/validate-license';
        } else {
            $this->serverUrl = $baseUrl;
        }

        // 2. KEY: Intentar config, luego env directo (buscando ambas variantes)
        // Primero intentamos la configuración estándar
        $this->licenseKey = config('services.aplusmaster.key');

        // Si falla, intentamos variables de entorno
        if (empty($this->licenseKey)) {
            $this->licenseKey = env('APP_LICENSE_KEY') ?? env('LICENSE_KEY');
        }

        // FUERZA BRUTA: Si Laravel falla al leer, leemos el archivo nosotros mismos
        if (empty($this->licenseKey)) {
            // Intentamos leer con el nombre correcto primero
            $this->licenseKey = $this->readEnvFile('APP_LICENSE_KEY');
            
            // Fallback al nombre antiguo por si acaso
            if (empty($this->licenseKey)) {
                $this->licenseKey = $this->readEnvFile('LICENSE_KEY');
            }
        }
    }

    /**
     * Lee una variable directamente del archivo .env omitiendo el caché de Laravel
     */
    protected function readEnvFile($key)
    {
        try {
            $path = base_path('.env');
            if (!file_exists($path)) {
                return null;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignorar comentarios
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                // Buscar la clave
                if (strpos($line, $key . '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    if (trim($name) === $key) {
                        // Limpiar comillas y espacios
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
                // Log::info("CLIENTE: Respuesta del Maestro (Éxito):", $data ?? []); // Descomentar solo si es necesario depurar

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