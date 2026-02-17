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
            $this->errorMessage = 'Clave de licencia no configurada.';
            return false;
        }

        // Cache: Si es exitoso guardamos por 5 min, si falla no guardamos o guardamos muy poco
        // para permitir reintentos rápidos al corregir el problema.
        $cacheKey = 'license_status_' . $this->licenseKey;
        
        // Intentamos obtener del caché
        $cachedResult = Cache::get($cacheKey);
        
        if ($cachedResult !== null) {
            if ($cachedResult === true) {
                return true;
            }
            // Si el caché dice false, verificamos si tenemos un mensaje guardado en caché secundario
            $this->errorMessage = Cache::get($cacheKey . '_msg', 'Error de validación (Caché).');
            // Aún así, forzamos validación si falló recientemente para no bloquear al usuario si ya pagó
            // Comentar el return abajo para forzar re-check en caso de fallo previo
            // return false; 
        }

        $isValid = $this->validateRemote();

        // Guardar en caché solo si es válido para evitar bloquear intentos de arreglo
        // Si es inválido, guardamos por solo 10 segundos
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
            
            // Ignorar verificación SSL para evitar problemas en local/dev
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

            // CASO 2: Respuesta de Error Controlada (403, 404, 400 del servidor)
            // Aquí es donde atrapamos "Dominio no autorizado" o "Licencia suspendida"
            if ($response->status() >= 400 && $response->status() < 500) {
                $this->errorMessage = $data['message'] ?? 'Licencia rechazada por el servidor maestro.';
                Log::warning("CLIENTE: Validación fallida: " . $this->errorMessage);
                return false;
            }

            // CASO 3: Error de Servidor (500)
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