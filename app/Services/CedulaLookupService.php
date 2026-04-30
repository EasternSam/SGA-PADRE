<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de verificación de cédula dominicana.
 * 
 * Consulta la API del padrón electoral para obtener datos del ciudadano:
 * nombres, apellidos, sexo, fecha de nacimiento.
 * 
 * Cachea resultados por 24h para evitar llamadas repetidas.
 */
class CedulaLookupService
{
    /**
     * URL de la API externa de verificación.
     */
    protected string $apiUrl = 'https://citaslanuevalicencia.lat.do/api/public/validate-cedula';

    /**
     * Consulta los datos de un ciudadano por su número de cédula.
     *
     * @param string $cedula Cédula con o sin guiones (ej: "402-3669912-6" o "40236699126")
     * @return array{found: bool, citizen?: array, error?: string}
     */
    public function lookup(string $cedula): array
    {
        // Limpiar: solo números
        $clean = preg_replace('/[^0-9]/', '', $cedula);

        if (strlen($clean) !== 11) {
            return ['found' => false, 'error' => 'La cédula debe tener exactamente 11 dígitos.'];
        }

        // Cachear por 24 horas — la cédula no cambia
        $cacheKey = "cedula_lookup_{$clean}";

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($clean) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($this->apiUrl, ['cedula' => $clean]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data['found']) && !empty($data['citizen'])) {
                        $citizen = $data['citizen'];

                        return [
                            'found' => true,
                            'citizen' => [
                                'cedula'          => $citizen['cedula'] ?? $clean,
                                'nombres'         => $this->titleCase($citizen['nombres'] ?? ''),
                                'apellido1'       => $this->titleCase($citizen['apellido1'] ?? ''),
                                'apellido2'       => $this->titleCase($citizen['apellido2'] ?? ''),
                                'sexo'            => $citizen['sexo'] ?? '',
                                'fechaNacimiento' => $citizen['fechaNacimiento'] ?? null,
                            ],
                        ];
                    }

                    return ['found' => false, 'error' => 'No se encontró esta cédula en el padrón.'];
                }

                return ['found' => false, 'error' => 'Error de respuesta del servidor externo.'];

            } catch (\Exception $e) {
                Log::warning('CedulaLookup error: ' . $e->getMessage());
                return ['found' => false, 'error' => 'No se pudo conectar al servicio de verificación.'];
            }
        });
    }

    /**
     * Convierte texto MAYÚSCULAS a Title Case.
     */
    protected function titleCase(string $text): string
    {
        return mb_convert_case(mb_strtolower(trim($text)), MB_CASE_TITLE, 'UTF-8');
    }
}
