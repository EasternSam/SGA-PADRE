<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para manejar la comunicación (saliente) con la API de WordPress.
 */
class WordpressApiService
{
    protected $baseUri;
    protected $secret;

    public function __construct()
    {
        // Obtiene la configuración desde config/services.php
        $this->baseUri = config('services.wordpress.base_uri');
        $this->secret = config('services.wordpress.secret');
    }

    /**
     * Realiza una solicitud GET a la API de WordPress.
     *
     * @param string $endpoint El endpoint de la API (ej. 'sga/v1/get-courses/')
     * @return array|null Los datos de la respuesta o null en caso de error.
     */
    private function makeGetRequest($endpoint)
    {
        // ====================================================================
        // INICIO DE DEBUG Y CORRECCIÓN DE URL
        // ====================================================================

        // CORRECCIÓN: Nos aseguramos de que la URL se construya correctamente
        // 1. rtrim quita la barra '/' del final de $baseUri (si la hay)
        // 2. ltrim quita la barra '/' del inicio de $endpoint (si la hay)
        // 3. Se unen con una sola barra '/' en medio.
        $fullUrl = rtrim($this->baseUri, '/') . '/' . ltrim($endpoint, '/');

        Log::debug("WordpressApiService: Intentando conectar a...", [
            'base_uri' => $this->baseUri,
            'endpoint' => $endpoint,
            'url_completa' => $fullUrl, // Usamos la variable corregida
            'secret_cargado' => !empty($this->secret) ? 'Sí' : 'No (¡ERROR DE CONFIG!)',
        ]);
        // ====================================================================
        // FIN DE DEBUG Y CORRECCIÓN
        // ====================================================================

        if (empty($this->baseUri) || empty($this->secret)) {
            Log::error('WordpressApiService: La URI base o el secreto de la API no están configurados en config/services.php o .env');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-SGA-Signature' => $this->secret, // El header que tu API de WP espera
                'Accept' => 'application/json',
            ])->timeout(15) // Timeout de 15 segundos
              ->get($fullUrl); // Usamos la variable URL corregida

            if ($response->successful()) {
                // Devuelve el cuerpo de la respuesta JSON decodificado
                return $response->json();
            }

            // Log del error si la API devuelve un error
            Log::error("WordpressApiService: Error al llamar a {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return null;

        } catch (\Exception $e) {
            // Log del error si la conexión falla (timeout, DNS, etc.)
            Log::error("WordpressApiService: Excepción al llamar a {$endpoint}", [
                'tipo' => get_class($e), // Nos dice si es ConnectException, etc.
                'message' => $e->getMessage(),
                'linea' => $e->getLine(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene la lista de cursos desde el endpoint de WordPress.
     * (Llama a /sga/v1/get-courses/ que creamos en WP)
     *
     * @return array
     */
    public function getSgaCourses(): array
    {
        $response = $this->makeGetRequest('sga/v1/get-courses/');

        // Si la solicitud fue exitosa y 'data' existe (basado en tu API de WP)
        if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
            return $response['data'];
        }
        
        // Devuelve un array vacío en caso de fallo
        return [];
    }

    // Aquí se añadirán más métodos (como 'updateStudentStatusInWp') en el futuro
}