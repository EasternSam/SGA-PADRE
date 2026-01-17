<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para manejar la comunicación (saliente) con la API de WordPress.
 * Optimizado para cPanel: SSL ignorado, timeouts altos y logs detallados.
 */
class WordpressApiService
{
    protected $baseUri;
    protected $secret;

    public function __construct()
    {
        // ESTRATEGIA DE DEFENSA: 
        // Primero intenta leer de la configuración cacheada (lo correcto).
        // Si falla (null), lee DIRECTAMENTE del .env (salvavidas para cPanel).
        $this->baseUri = config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI');
        $this->secret = config('services.wordpress.secret') ?? env('WP_API_SECRET');
    }

    /**
     * Realiza una solicitud GET a la API de WordPress.
     *
     * @param string $endpoint El endpoint de la API (ej. 'sga/v1/get-courses/')
     * @return array|null Los datos de la respuesta o null en caso de error.
     */
    private function makeGetRequest($endpoint)
    {
        // Corrección de URL para evitar dobles slashes problemáticos
        $fullUrl = rtrim($this->baseUri, '/') . '/' . ltrim($endpoint, '/');

        // Log inicial para rastrear intentos
        Log::info("WP_API: Iniciando conexión...", [
            'destino' => $fullUrl,
            'tiene_secret' => !empty($this->secret) ? 'SI' : 'NO'
        ]);

        if (empty($this->baseUri) || empty($this->secret)) {
            Log::error('WP_API_FATAL: Faltan credenciales en .env (WP_API_BASE_URI o WP_API_SECRET).');
            return null;
        }

        try {
            // HTTP CLIENT BLINDADO PARA CPANEL
            $response = Http::withoutVerifying() // <--- CRÍTICO: Ignora errores de SSL (común en cPanel)
                ->withOptions(["verify" => false]) // Refuerzo para la librería Guzzle subyacente
                ->timeout(60) // Aumentamos a 60s por si el servidor WP es lento
                ->withHeaders([
                    'X-SGA-Signature' => $this->secret,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Client/1.0', // Evita bloqueo por ModSecurity/Firewalls
                ])
                ->get($fullUrl);

            if ($response->successful()) {
                Log::info("WP_API: Éxito ({$response->status()})");
                return $response->json();
            }

            // Manejo de errores específicos (401, 403, 404, 500)
            Log::error("WP_API: Error HTTP {$response->status()}", [
                'body_preview' => substr($response->body(), 0, 500) // Solo guardamos el inicio para no llenar el log
            ]);
            
            return null;

        } catch (\Exception $e) {
            // Captura errores de red (DNS, Timeout, Conexión rechazada)
            Log::error("WP_API: Excepción de Conexión", [
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Obtiene la lista de cursos desde el endpoint de WordPress.
     * Endpoint: /sga/v1/get-courses/
     * @return array
     */
    public function getSgaCourses(): array
    {
        $response = $this->makeGetRequest('sga/v1/get-courses/');

        if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
            return $response['data'];
        }
        
        return [];
    }

    /**
     * Alias para compatibilidad con el comando de importación.
     * @return array
     */
    public function getCourses(): array
    {
        return $this->getSgaCourses();
    }

    /**
     * Obtiene los horarios de un curso específico de WordPress.
     * Endpoint: /sga/v1/course/{id}/schedules
     * @param int $wpCourseId
     * @return array
     */
    public function getSchedulesForWpCourse(int $wpCourseId): array
    {
        $endpoint = "sga/v1/course/{$wpCourseId}/schedules";
        $response = $this->makeGetRequest($endpoint);

        if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
            return $response['data'];
        }

        Log::warning("WP_API: No se pudieron obtener horarios para WP Course ID: {$wpCourseId}");
        return [];
    }

    /**
     * Obtiene estadísticas de inscripciones desde WordPress (para el gráfico).
     * Endpoint: /sga/v1/reports/enrollment-stats
     * * @return array Estructura: ['labels' => [], 'data' => []]
     */
    public function getEnrollmentStats(): array
    {
        $response = $this->makeGetRequest('sga/v1/reports/enrollment-stats');

        if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
            return $response['data'];
        }

        Log::warning("WP_API: No se pudieron obtener estadísticas de inscripciones.");
        return ['labels' => [], 'data' => []];
    }
}