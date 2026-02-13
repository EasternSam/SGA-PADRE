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
        // VALIDACIÓN DE PAQUETES (CON CACHÉ CORTA DE 5 MINUTOS)
        // Guardamos no solo si es válido, sino QUÉ puede hacer.
        // =========================================================
        $isLicenseValid = Cache::remember('saas_license_v3_data', 300, function () use ($licenseKey, $domain, $masterUrl) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
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
                    // Cacheamos esto por 5 minutos igual que la validación principal
                    Cache::put('saas_active_features', $data['features'] ?? [], 300);
                    Cache::put('saas_plan_name', $data['plan_name'] ?? 'N/A', 300);

                    return true;
                }
                
                // Si el servidor responde explícitamente que NO (403 Suspendido, 404 No existe)
                if ($response->status() === 403 || $response->status() === 404) {
                    return false;
                }

                return false;

            } catch (\Exception $e) {
                // Fail Open: Si falla la conexión, permitimos el acceso
                // PERO mantenemos las últimas features conocidas si existen en caché vieja
                return true; 
            }
        });

        if (!$isLicenseValid) {
            // Si está bloqueado, forzamos borrado de caché para reintento rápido
            if ($request->isMethod('get') && !$request->ajax()) {
                Cache::forget('saas_license_v3_data');
            }
            abort(403, 'Academic+: Su licencia ha sido SUSPENDIDA o expiró. Contacte a soporte para reactivación inmediata.');
        }

        return $next($request);
    }
}