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

        // 1. Permitir acceso libre a rutas especiales
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
        // VALIDACIÓN DE PAQUETES (SIN CACHÉ / TIEMPO REAL)
        // Hemos eliminado Cache::remember para que los cambios se reflejen al instante.
        // =========================================================
        $isValid = false;

        try {
            // Timeout corto para no ralentizar la carga si el maestro tarda
            $response = Http::withoutVerifying()
                ->timeout(3)
                ->post("{$masterUrl}/api/v1/validate-license", [
                    'license_key' => $licenseKey,
                    'domain'      => $domain,
                ]);

            // Si el servidor maestro responde que todo está bien
            if ($response->successful() && $response->json('status') === 'success') {
                
                // ===> AQUÍ ESTÁ LA CLAVE <===
                // El maestro nos devuelve las funciones permitidas. Las guardamos.
                $data = $response->json('data');
                
                // Guardamos features y plan en caché separada para que el Helper SaaS::has() los lea rápido
                // Cacheamos esto por 60 segundos (muy poco) para balancear velocidad y frescura
                Cache::put('saas_active_features', $data['features'] ?? [], 60);
                Cache::put('saas_plan_name', $data['plan_name'] ?? 'N/A', 60);

                $isValid = true;
            }
            
            // Si el servidor responde explícitamente que NO (403 Suspendido, 404 No existe)
            elseif ($response->status() === 403 || $response->status() === 404) {
                $isValid = false;
            } else {
                // Error 500 u otro, asumimos fallo
                $isValid = false;
            }

        } catch (\Exception $e) {
            // Fail Open: Si falla la conexión, permitimos el acceso
            // PERO mantenemos las últimas features conocidas si existen en caché vieja
            $isValid = true; 
        }

        if (!$isValid) {
            // Si está bloqueado, forzamos borrado de caché para reintento rápido
            Cache::forget('saas_active_features');
            Cache::forget('saas_plan_name');
            abort(403, 'Academic+: Su licencia ha sido SUSPENDIDA o expiró. Contacte a soporte para reactivación inmediata.');
        }

        return $next($request);
    }
}