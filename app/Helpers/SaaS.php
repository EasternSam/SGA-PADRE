<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class SaaS
{
    /**
     * Verifica si la instalación actual tiene acceso a una funcionalidad específica.
     * Uso: SaaS::has('finance')
     */
    public static function has(string $feature): bool
    {
        // Si el modo SaaS está desactivado, asumimos que tiene TODO permitido (Modo Desarrollo/Local)
        if (env('SAAS_MODE_ENABLED', true) === false) {
            return true;
        }

        // Recuperamos las funciones activas desde la caché (guardadas por el Middleware)
        $activeFeatures = Cache::get('saas_active_features', []);

        // Si es un administrador global o super-admin, quizás quieras bypass (opcional)
        // Por ahora, validamos estrictamente contra la lista del maestro.
        
        return in_array($feature, $activeFeatures);
    }

    /**
     * Devuelve el nombre del plan actual.
     */
    public static function planName(): string
    {
        return Cache::get('saas_plan_name', 'Desconocido');
    }
}