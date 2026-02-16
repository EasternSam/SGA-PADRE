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
            $response = Http::timeout(10)->post($this->serverUrl, [
                'license_key' => $this->licenseKey,
                'domain' => request()->getHost(),
                'ip' => request()->ip(), // Opcional, dependiendo de tu controlador
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['status']) && $data['status'] === 'success';
            }

            Log::error('Fallo validación de licencia: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Error de conexión con servidor de licencias: ' . $e->getMessage());
            return false;
        }
    }
}