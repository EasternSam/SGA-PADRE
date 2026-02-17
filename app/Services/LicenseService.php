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
        $this->serverUrl = config('services.aplusmaster.url');
        $this->licenseKey = config('services.aplusmaster.key');
    }

    public function check(): bool
    {
        if (empty($this->licenseKey)) {
            return false;
        }

        // Cachear el resultado exitoso por 24 horas para no saturar la red
        return Cache::remember('license_status_' . $this->licenseKey, 60 * 60 * 24, function () {
            return $this->validateRemote();
        });
    }

    public function validateRemote(): bool
    {
        try {
            $domain = request()->getHost();
            $ip = request()->ip();

            Log::info("CLIENTE LICENCIA: Validando con Maestro...", [
                'url' => $this->serverUrl,
                'key' => $this->licenseKey,
                'mi_dominio' => $domain
            ]);

            // Desactivar verificación SSL agresivamente
            $response = Http::withoutVerifying()
                ->withOptions([
                    'verify' => false,
                    'ssl_verify_peer' => false,
                    'ssl_verify_host' => false,
                ])
                ->timeout(15) // Aumentar timeout por si el handshake SSL es lento
                ->post($this->serverUrl, [
                    'license_key' => $this->licenseKey,
                    'domain' => $domain,
                    'ip' => $ip,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Loguear respuesta exitosa para ver qué dice el maestro
                Log::info("CLIENTE LICENCIA: Respuesta Maestro (HTTP 200)", $data);

                return isset($data['status']) && $data['status'] === 'success';
            }

            // Loguear error HTTP (403, 404, 500) y el cuerpo de la respuesta
            Log::error('CLIENTE LICENCIA: Fallo validación (HTTP ' . $response->status() . ')', [
                'body' => $response->body()
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error('CLIENTE LICENCIA: Error de conexión con servidor de licencias: ' . $e->getMessage());
            return false;
        }
    }
}