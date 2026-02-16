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
        // Rutas que funcionan sin licencia (instalación, health checks, assets, login, etc.)
        $exemptPatterns = [
            'up',
            'health', 
            'install',      // Permitir ruta exacta /install
            'install/*',    // Permitir subrutas de instalación
            'installer',    // Variaciones comunes
            'installer/*',
            '_debugbar/*',
            'api/webhook/*',
            'login',        // Permitir login para que el admin pueda entrar a cambiar la licencia
            'logout',
            'register',     // Opcional, si el registro es público
            'admin/settings*', // Permitir entrar a ajustes para cambiar la licencia
            'livewire/*',   // Necesario para que Livewire funcione en páginas de login/install
            'livewire/message/*',
        ];

        if ($request->is($exemptPatterns)) {
            return $next($request);
        }

        if (!$this->licenseService->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Licencia inválida o expirada.'], 403);
            }
            
            // Si la licencia falla, podemos redirigir a una página de "Activación" en lugar de abortar
            // O mostrar el mensaje de error. Por ahora mantenemos el abort para seguridad estricta.
            abort(403, 'SISTEMA BLOQUEADO: Su licencia no es válida, ha expirado o el dominio (' . request()->getHost() . ') no está autorizado. Contacte a Aplusmaster.');
        }

        return $next($request);
    }
}