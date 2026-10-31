<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\Setting;

/**
 * Servicio para manejar la comunicación (saliente) con la API de WordPress.
 * Optimizado para cPanel / Cloudflare / LiteSpeed con auto-resolución de /wp-json/.
 */
class WordpressApiService
{
    protected $baseUri;
    protected $secret;
    protected $lastError = null;

    public function __construct()
    {
        try {
            $this->baseUri = Setting::val('wp_api_url', config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI'));
            $this->secret = Setting::val('wp_api_secret', config('services.wordpress.secret') ?? env('WP_API_SECRET'));
        } catch (QueryException $e) {
            $this->baseUri = config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI');
            $this->secret = config('services.wordpress.secret') ?? env('WP_API_SECRET');
        } catch (\Exception $e) {
            $this->baseUri = '';
            $this->secret = '';
        }
    }

    /**
     * Obtener el último error registrado durante una llamada API.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Probar la conexión con la API de WordPress.
     *
     * @return array
     */
    public function testConnection(): array
    {
        $courses = $this->getSgaCourses();
        if (!empty($courses)) {
            $count = count($courses);
            return [
                'success' => true,
                'message' => "Conexión exitosa con WordPress (Academic+ Catalog). Se detectaron {$count} cursos disponibles.",
                'count' => $count,
            ];
        }

        $error = $this->getLastError() ?: 'No se pudieron recuperar cursos desde el endpoint de WordPress. Verifique la URL y el Token en los ajustes.';
        return [
            'success' => false,
            'message' => "Error de conexión con WordPress: {$error}",
            'count' => 0,
        ];
    }

    /**
     * Construye y normaliza la URL completa asegurando el prefijo REST /wp-json/.
     */
    private function buildUrl(string $endpoint): string
    {
        $base = rtrim($this->baseUri ?: '', '/');
        if (!str_contains($base, '/wp-json')) {
            $base .= '/wp-json';
        }
        return $base . '/' . ltrim($endpoint, '/');
    }

    /**
     * Realiza una solicitud GET a la API de WordPress.
     *
     * @param string $endpoint El endpoint de la API (ej. 'sga/v1/get-courses/')
     * @return array|null Los datos de la respuesta o null en caso de error.
     */
    private function makeGetRequest($endpoint)
    {
        $this->lastError = null;

        if (empty($this->baseUri) || empty($this->secret)) {
            $this->lastError = 'Faltan credenciales de WordPress (URL Base o Token Secreto). Configure la integración en Ajustes > Integraciones & APIs.';
            Log::warning('WP_API: ' . $this->lastError);
            return null;
        }

        $fullUrl = $this->buildUrl($endpoint);
        
        // Agregar cache buster para saltarse la caché de LiteSpeed/Cloudflare en WordPress
        $separator = str_contains($fullUrl, '?') ? '&' : '?';
        $fullUrl .= $separator . '_cb=' . time();

        Log::info("WP_API: Iniciando conexión...", [
            'destino' => $fullUrl,
            'tiene_secret' => !empty($this->secret) ? 'SI' : 'NO'
        ]);

        try {
            $response = Http::withoutVerifying()
                ->withOptions(["verify" => false])
                ->timeout(45)
                ->withHeaders([
                    'X-SGA-Signature' => $this->secret,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-SGA-Client/1.0',
                ])
                ->get($fullUrl);

            if ($response->successful()) {
                Log::info("WP_API: Éxito ({$response->status()})");
                return $response->json();
            }

            $body = $response->json();
            $msg = $body['message'] ?? "Error HTTP {$response->status()} en WordPress ({$fullUrl})";
            $this->lastError = $msg;

            Log::error("WP_API: Error HTTP {$response->status()}", [
                'body_preview' => substr($response->body(), 0, 500)
            ]);
            
            return null;

        } catch (\Exception $e) {
            $this->lastError = 'Excepción de conexión con WordPress: ' . $e->getMessage();
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

        if (is_array($response)) {
            return $response;
        }

        return [];
    }

    /**
     * Obtiene las estadísticas de inscripciones de WordPress para los gráficos de dashboard.
     * Endpoint: /sga/v1/reports/enrollment-stats
     * @return array
     */
    public function getEnrollmentStats(): array
    {
        $response = $this->makeGetRequest('sga/v1/reports/enrollment-stats');

        if (is_array($response) && isset($response['labels'], $response['data'])) {
            return $response;
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
        $this->lastError = null;

        if (empty($this->baseUri) || empty($this->secret)) {
            $this->lastError = 'Faltan credenciales de WordPress (URL Base o Token Secreto).';
            Log::warning('WP_API POST: ' . $this->lastError);
            return null;
        }

        $fullUrl = $this->buildUrl($endpoint);

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

            $body = $response->json();
            $this->lastError = $body['message'] ?? "Error HTTP {$response->status()} en WordPress POST";

            Log::error("WP_API POST: Error HTTP {$response->status()}", [
                'endpoint' => $endpoint,
                'body_preview' => substr($response->body(), 0, 500)
            ]);
            return null;

        } catch (\Exception $e) {
            $this->lastError = 'Excepción de conexión POST con WordPress: ' . $e->getMessage();
            Log::error("WP_API POST: Excepción de Conexión", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
