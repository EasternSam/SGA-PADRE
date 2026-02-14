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
        // Usamos el Helper que ya creamos.
        // Si NO tiene el feature, mostramos la vista de bloqueo.
        if (! SaaS::has($feature)) {
            return response()->view('errors.feature-locked', ['feature' => $feature], 403);
        }

        return $next($request);
    }
}