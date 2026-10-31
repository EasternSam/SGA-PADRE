<?php

namespace App\Services\Dashboard\Support;

use App\Helpers\SaaS;

/**
 * Misma semántica que el middleware `feature:` (EnsureFeatureEnabled), para que el
 * dashboard nunca muestre algo que la ruta luego bloquearía.
 */
final class FeatureGate
{
    public static function enabled(string $feature): bool
    {
        return match ($feature) {
            'academic_courses' => SaaS::has('academic') && SaaS::showCourses(),
            'academic_careers' => SaaS::has('academic') && SaaS::showCareers(),
            default => SaaS::has($feature),
        };
    }
}
