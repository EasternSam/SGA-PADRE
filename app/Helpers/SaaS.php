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
     * Retorna: 'courses', 'careers', 'both'
     */
    public static function academicMode(): string
    {
        if (env('SAAS_MODE_ENABLED', true) === false) {
            return 'both';
        }

        return Cache::get('saas_academic_mode', 'both');
    }

    /**
     * Verifica si el modo actual incluye Cursos.
     */
    public static function showCourses(): bool
    {
        $mode = self::academicMode();
        return $mode === 'courses' || $mode === 'both';
    }

    /**
     * Verifica si el modo actual incluye Carreras.
     */
    public static function showCareers(): bool
    {
        $mode = self::academicMode();
        return $mode === 'careers' || $mode === 'both';
    }

    /**
     * Devuelve el nombre del plan actual.
     */
    public static function planName(): string
    {
        return Cache::get('saas_plan_name', 'Desconocido');
    }
}