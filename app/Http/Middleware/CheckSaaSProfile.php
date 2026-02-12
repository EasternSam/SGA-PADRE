<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

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

        // 1. Permitir acceso libre al instalador Y A LA RUTA DE LIMPIEZA DE CACHÉ Y DEBUG
        if ($request->is('install') || $request->is('install/*') || $request->is('system/*')) {
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

                // Si el servidor respondió (aunque sea error), analizamos el status
                // Si el status es 'success', devolvemos true. Si es 'error' (suspendido), devolvemos false.
                if ($response->successful() && $response->json('status') === 'success') {
                    return true;
                }
                
                // Si el servidor responde explícitamente que NO es válida (403, 404, etc), devolvemos false
                if ($response->status() === 403 || $response->status() === 404) {
                    return false;
                }

                // Si llegamos aquí, podría ser un error 500 del servidor maestro.
                // En ese caso, por seguridad del negocio, mejor dejamos pasar temporalmente (return true)
                // O bloqueamos (return false) según tu política. 
                // Asumiremos false para forzar revisión, o true para "fail-open".
                return false; 

            } catch (\Exception $e) {
                // AQUÍ es donde entra si se va el internet o el servidor no existe (timeout/DNS).
                // "Fail Open": Si no podemos contactar al guardián, dejamos pasar al cliente.
                Log::error("SaaS Connection Error: " . $e->getMessage());
                return true; 
            }
        });

        if (!$isLicenseValid) {
            // Si está bloqueado, permitimos ver una vista de error.
            if ($request->isMethod('get') && !$request->ajax()) {
                // Borramos caché al mostrar el error para que al refrescar se intente validar de nuevo
                Cache::forget('saas_license_valid');
            }
            
            abort(403, 'Academic+: Su licencia ha sido SUSPENDIDA o expiró. Contacte a soporte para reactivación inmediata.');
        }

        return $next($request);
    }
}