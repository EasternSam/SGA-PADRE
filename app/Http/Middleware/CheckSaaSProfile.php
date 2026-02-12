<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckSaaSProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // =========================================================
        // INTERRUPTOR DE DESARROLLO (Bypass del SaaS)
        // =========================================================
        if (env('SAAS_MODE_ENABLED', true) === false) {
            return $next($request);
        }

        // 1. Permitir acceso libre al instalador Y A LA RUTA DE LIMPIEZA DE CACHÉ
        // Esto permite desbloquear el sistema manualmente si pagaron y no quieren esperar
        if ($request->is('install') || $request->is('install/*') || $request->is('system/refresh-license')) {
            return $next($request);
        }

        // 2. Verificar si el sistema ya fue instalado
        $isInstalled = env('APP_INSTALLED', false);

        if (!$isInstalled) {
            return redirect()->route('installer.step1');
        }

        // 3. Validar la licencia con el Servidor Maestro
        $licenseKey = env('APP_LICENSE_KEY');
        $domain = $request->getHost();
        $masterUrl = rtrim(env('SAAS_MASTER_URL', 'https://gestion.90s.agency'), '/');

        // =========================================================
        // CACHÉ OPTIMIZADO: 300 segundos (5 minutos)
        // =========================================================
        $isLicenseValid = Cache::remember('saas_license_valid', 300, function () use ($licenseKey, $domain, $masterUrl) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(5)
                    ->post("{$masterUrl}/api/v1/validate-license", [
                        'license_key' => $licenseKey,
                        'domain'      => $domain,
                    ]);

                return $response->successful() && $response->json('status') === 'success';
            } catch (\Exception $e) {
                // Si el maestro se cae temporalmente, permitimos el paso
                return true; 
            }
        });

        if (!$isLicenseValid) {
            // Si está bloqueado, permitimos ver una vista de error, pero forzamos borrar la caché 
            // para que si recargan y ya pagaron, entre de una vez.
            if ($request->isMethod('get') && !$request->ajax()) {
                // Opcional: Auto-limpiar caché al mostrar error para re-checkear en el siguiente F5
                Cache::forget('saas_license_valid');
            }
            
            abort(403, 'Academic+: La licencia de este sistema ha sido SUSPENDIDA o expiró. Contacte a soporte técnico para reactivación inmediata.');
        }

        return $next($request);
    }
}