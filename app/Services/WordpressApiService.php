<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException; // Importar QueryException
use App\Models\Setting;

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
        // ESTRATEGIA DE DEFENSA MEJORADA:
        // Manejamos excepciones de base de datos para permitir que artisan migrate funcione
        // en una instalación limpia donde la tabla 'settings' aún no existe.
        
        try {
            $this->baseUri = Setting::val('wp_api_url', config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI'));
            $this->secret = Setting::val('wp_api_secret', config('services.wordpress.secret') ?? env('WP_API_SECRET'));
        } catch (QueryException $e) {
            // Si la tabla no existe (ej: durante instalación), usamos valores de entorno o vacíos
            // para no romper el arranque de la aplicación.
            $this->baseUri = config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI');
            $this->secret = config('services.wordpress.secret') ?? env('WP_API_SECRET');
        } catch (\Exception $e) {
            // Captura genérica para cualquier otro error de arranque
            $this->baseUri = '';
            $this->secret = '';
        }
    }

    /**
     * Realiza una solicitud GET a la API de WordPress.
     *
     * @param string $endpoint El endpoint de la API (ej. 'sga/v1/get-courses/')
     * @return array|null Los datos de la respuesta o null en caso de error.
     */
    private function makeGetRequest($endpoint)
    {
        // Si no hay configuración válida (ej: instalación fallida o tabla vacía), abortar temprano
        if (empty($this->baseUri) || empty($this->secret)) {
            Log::warning('WP_API: Faltan credenciales (URI o Secret). Verifique tabla settings o .env.');
            return null;
        }

        // Corrección de URL para evitar dobles slashes problemáticos
        $fullUrl = rtrim($this->baseUri, '/') . '/' . ltrim($endpoint, '/');
        
        // Agregar cache buster para saltarse la caché de LiteSpeed/Cloudflare en WordPress
        $separator = str_contains($fullUrl, '?') ? '&' : '?';
        $fullUrl .= $separator . '_cb=' . time();

        // Log inicial para rastrear intentos
        Log::info("WP_API: Iniciando conexión...", [
            'destino' => $fullUrl,
            'tiene_secret' => !empty($this->secret) ? 'SI' : 'NO'
        ]);

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
     * Obtiene la lista de estudiantes de WordPress.
     * Endpoint: /sga/v1/students/
     * @return array
     */
    public function getSgaStudents(): array
    {
        $response = $this->makeGetRequest('sga/v1/students/');
        return is_array($response) ? $response : [];
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

    /**
     * Sincroniza la información de un perfil de estudiante con WordPress.
     *
     * @param array $studentData
     * @return bool
     */
    public function syncStudent(array $studentData): bool
    {
        $response = $this->makePostRequest('sga/v1/sync-student/', $studentData);
        return !empty($response['success']);
    }

    /**
     * Sincroniza el estado de una inscripción con WordPress.
     *
     * @param array $enrollmentData
     * @return bool
     */
    public function syncEnrollment(array $enrollmentData): bool
    {
        $response = $this->makePostRequest('sga/v1/sync-enrollment/', $enrollmentData);
        return !empty($response['success']);
    }

    /**
     * Realiza una solicitud POST a la API de WordPress.
     *
     * @param string $endpoint El endpoint de la API
     * @param array $data Los datos a enviar en el body
     * @return array|null
     */
    private function makePostRequest($endpoint, array $data)
    {
        if (empty($this->baseUri) || empty($this->secret)) {
            Log::warning('WP_API POST: Faltan credenciales (URI o Secret).');
            return null;
        }

        $fullUrl = rtrim($this->baseUri, '/') . '/' . ltrim($endpoint, '/');

        Log::info("WP_API POST: Enviando datos...", [
            'destino' => $fullUrl,
            'endpoint' => $endpoint
        ]);

        try {
            $response = Http::withoutVerifying()
                ->withOptions(["verify" => false])
                ->timeout(30)
                ->withHeaders([
                    'X-SGA-Signature' => $this->secret,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Client/1.0',
                ])
                ->post($fullUrl, $data);

            if ($response->successful()) {
                Log::info("WP_API POST: Éxito ({$response->status()})");
                return $response->json();
            }

            Log::error("WP_API POST: Error HTTP {$response->status()}", [
                'endpoint' => $endpoint,
                'body_preview' => substr($response->body(), 0, 500)
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error("WP_API POST: Excepción de Conexión", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}