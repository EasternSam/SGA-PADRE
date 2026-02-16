<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LicenseService;

class VerifyLicense
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Rutas que funcionan sin licencia (instalación, health checks, assets, etc.)
        $exemptPatterns = [
            'up',
            'health', 
            'install/*', 
            '_debugbar/*',
            'api/webhook/*' 
        ];

        if ($request->is($exemptPatterns)) {
            return $next($request);
        }

        if (!$this->licenseService->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Licencia inválida o expirada.'], 403);
            }
            
            abort(403, 'SISTEMA BLOQUEADO: Su licencia no es válida, ha expirado o el dominio no está autorizado. Contacte a Aplusmaster.');
        }

        return $next($request);
    }
}