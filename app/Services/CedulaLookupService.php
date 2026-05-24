<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de verificación de cédula dominicana con sistema de fallback.
 * 
 * Estrategia de proveedores (en orden de prioridad):
 *   1. citaslanuevalicencia.lat.do — Datos completos (nombres, apellidos, sexo, fecha)
 *   2. DGII RNC Scraper — Nombre completo (como fallback de datos)
 *   3. OGTIC v3 — Solo validación booleana (último recurso)
 * 
 * Cachea resultados exitosos por 24h para evitar llamadas repetidas.
 * Los errores NO se cachean para permitir reintentos inmediatos.
 */
class CedulaLookupService
{
    /**
     * Proveedores de verificación ordenados por prioridad.
     * Cada uno retorna datos progresivamente menos completos.
     */
    protected array $providers = [
        'padron'  => 'https://citaslanuevalicencia.lat.do/api/public/validate-cedula',
        'ogtic'   => 'https://api.digital.gob.do/v3/cedulas/{cedula}/validate',
    ];

    /**
     * Consulta los datos de un ciudadano por su número de cédula.
     * Intenta múltiples proveedores en cascada si el principal falla.
     *
     * @param string $cedula Cédula con o sin guiones (ej: "402-1482708-7" o "40214827087")
     * @return array{found: bool, citizen?: array, source?: string, error?: string}
     */
    public function lookup(string $cedula): array
    {
        // Limpiar: solo números
        $clean = preg_replace('/[^0-9]/', '', $cedula);

        if (strlen($clean) !== 11) {
            return ['found' => false, 'error' => 'La cédula debe tener exactamente 11 dígitos.'];
        }

        // Verificar caché primero (solo resultados exitosos se cachean)
        $cacheKey = "cedula_lookup_{$clean}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Intentar proveedores en cascada
        $result = $this->tryPadronApi($clean);

        if (!$result['found']) {
            Log::info("CedulaLookup: Proveedor primario falló para {$clean}, intentando DGII scraper...");
            $result = $this->tryDgiiScraper($clean);
        }

        if (!$result['found']) {
            Log::info("CedulaLookup: DGII scraper falló para {$clean}, intentando OGTIC v3...");
            $result = $this->tryOgticApi($clean);
        }

        // Solo cachear resultados exitosos (24 horas)
        if ($result['found']) {
            Cache::put($cacheKey, $result, 60 * 60 * 24);
        }

        return $result;
    }

    /**
     * Proveedor #1: API del Padrón Electoral (citaslanuevalicencia).
     * Retorna datos completos: nombres, apellidos, sexo, fecha de nacimiento.
     */
    protected function tryPadronApi(string $cedula): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->providers['padron'], ['cedula' => $cedula]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['found']) && !empty($data['citizen'])) {
                    $citizen = $data['citizen'];

                    return [
                        'found' => true,
                        'source' => 'padron',
                        'citizen' => [
                            'cedula'          => $citizen['cedula'] ?? $cedula,
                            'nombres'         => $this->titleCase($citizen['nombres'] ?? ''),
                            'apellido1'       => $this->titleCase($citizen['apellido1'] ?? ''),
                            'apellido2'       => $this->titleCase($citizen['apellido2'] ?? ''),
                            'nombre_completo' => $this->titleCase(
                                trim(($citizen['nombres'] ?? '') . ' ' . ($citizen['apellido1'] ?? '') . ' ' . ($citizen['apellido2'] ?? ''))
                            ),
                            'sexo'            => $citizen['sexo'] ?? '',
                            'fechaNacimiento' => $citizen['fechaNacimiento'] ?? null,
                        ],
                    ];
                }

                return ['found' => false, 'error' => 'Cédula no encontrada en el padrón electoral.'];
            }

            Log::warning("CedulaLookup [padron]: HTTP {$response->status()}");
            return ['found' => false, 'error' => 'Error HTTP del servidor de padrón.'];

        } catch (\Exception $e) {
            Log::warning('CedulaLookup [padron] excepción: ' . $e->getMessage());
            return ['found' => false, 'error' => 'No se pudo conectar al servicio de padrón.'];
        }
    }

    /**
     * Proveedor #2: DGII RNC Scraper (web scraping del portal oficial).
     * Retorna nombre completo como fallback. Menos datos que el padrón.
     */
    protected function tryDgiiScraper(string $cedula): array
    {
        try {
            $dgiiService = app(DgiiRncLookupService::class);
            $result = $dgiiService->lookup($cedula);

            if ($result && !empty($result['nombre'])) {
                // Intentar dividir el nombre en partes (asumiendo formato "NOMBRES APELLIDO1 APELLIDO2")
                $parts = explode(' ', trim($result['nombre']));
                $totalParts = count($parts);

                // Heurística: últimas 2 palabras suelen ser apellidos
                if ($totalParts >= 3) {
                    $apellido2 = array_pop($parts);
                    $apellido1 = array_pop($parts);
                    $nombres = implode(' ', $parts);
                } elseif ($totalParts === 2) {
                    $nombres = $parts[0];
                    $apellido1 = $parts[1];
                    $apellido2 = '';
                } else {
                    $nombres = $result['nombre'];
                    $apellido1 = '';
                    $apellido2 = '';
                }

                return [
                    'found' => true,
                    'source' => 'dgii_scraper',
                    'citizen' => [
                        'cedula'          => $cedula,
                        'nombres'         => $this->titleCase($nombres),
                        'apellido1'       => $this->titleCase($apellido1),
                        'apellido2'       => $this->titleCase($apellido2),
                        'nombre_completo' => $this->titleCase($result['nombre']),
                        'sexo'            => '',
                        'fechaNacimiento' => null,
                    ],
                ];
            }

            return ['found' => false, 'error' => 'No se encontró en DGII.'];

        } catch (\Exception $e) {
            Log::warning('CedulaLookup [dgii_scraper] excepción: ' . $e->getMessage());
            return ['found' => false, 'error' => 'Error al consultar DGII.'];
        }
    }

    /**
     * Proveedor #3: OGTIC v3 API (api.digital.gob.do).
     * Solo valida si la cédula existe — NO retorna datos personales.
     * Útil como último recurso para al menos confirmar que es válida.
     */
    protected function tryOgticApi(string $cedula): array
    {
        try {
            $url = str_replace('{cedula}', $cedula, $this->providers['ogtic']);

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['valid']) && $data['valid'] === true) {
                    Log::info("CedulaLookup [ogtic]: Cédula {$cedula} validada (sin datos personales).");
                    return [
                        'found' => true,
                        'source' => 'ogtic_validation_only',
                        'citizen' => [
                            'cedula'          => $cedula,
                            'nombres'         => '',
                            'apellido1'       => '',
                            'apellido2'       => '',
                            'nombre_completo' => '',
                            'sexo'            => '',
                            'fechaNacimiento' => null,
                        ],
                    ];
                }

                return ['found' => false, 'error' => 'Cédula no válida según OGTIC.'];
            }

            return ['found' => false, 'error' => 'Error de respuesta OGTIC.'];

        } catch (\Exception $e) {
            Log::warning('CedulaLookup [ogtic] excepción: ' . $e->getMessage());
            return ['found' => false, 'error' => 'No se pudo conectar a OGTIC.'];
        }
    }

    /**
     * Verifica rápidamente si una cédula es válida sin obtener datos completos.
     * Usa la API más rápida disponible (OGTIC v3 o caché).
     * 
     * @param string $cedula
     * @return bool
     */
    public function isValid(string $cedula): bool
    {
        $clean = preg_replace('/[^0-9]/', '', $cedula);
        
        if (strlen($clean) !== 11) {
            return false;
        }

        // Verificar caché primero
        $cached = Cache::get("cedula_lookup_{$clean}");
        if ($cached !== null) {
            return $cached['found'] ?? false;
        }

        // Intentar validación rápida con OGTIC
        $result = $this->tryOgticApi($clean);
        return $result['found'] ?? false;
    }

    /**
     * Convierte texto MAYÚSCULAS a Title Case.
     */
    protected function titleCase(string $text): string
    {
        return mb_convert_case(mb_strtolower(trim($text)), MB_CASE_TITLE, 'UTF-8');
    }
}
