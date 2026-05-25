<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class SaaS
{
    /**
     * Verifica si la instalación actual tiene acceso a una funcionalidad específica.
     */
    public static function has(string $feature): bool
    {
        if (env('SAAS_MODE_ENABLED', true) === false) {
            return true;
        }

        $activeFeatures = Cache::get('saas_active_features', []);
        return in_array($feature, $activeFeatures);
    }

    /**
     * Obtiene el modo académico de la licencia.
     * Retorna: 'courses', 'careers', 'both', 'school'
     */
    public static function academicMode(): string
    {
        return 'school';
    }

    /**
     * Verifica si el modo actual incluye Cursos.
     */
    public static function showCourses(): bool
    {
        return false;
    }

    /**
     * Verifica si el modo actual incluye Carreras.
     */
    public static function showCareers(): bool
    {
        return false;
    }

    /**
     * Devuelve el nombre del plan actual.
     */
    public static function planName(): string
    {
        return Cache::get('saas_plan_name', 'Desconocido');
    }
}