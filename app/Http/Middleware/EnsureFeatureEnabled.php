<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SaaS;

class EnsureFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $allowed = true;
        $featureLabel = $feature;

        // Lógica específica para segmentación académica
        if ($feature === 'academic_courses') {
            $allowed = SaaS::showCourses();
            $featureLabel = 'Módulo de Cursos (Instituto)';
        } elseif ($feature === 'academic_careers') {
            $allowed = SaaS::showCareers();
            $featureLabel = 'Módulo de Carreras (Universidad)';
        } else {
            // Lógica para features estándar (finance, inventory, etc)
            $allowed = SaaS::has($feature);
        }

        // Si NO tiene el permiso o modo activo, mostramos la vista de bloqueo.
        if (!$allowed) {
            return response()->view('errors.feature-locked', ['feature' => $featureLabel], 403);
        }

        return $next($request);
    }
}