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
        // Si en tu archivo .env agregas: SAAS_MODE_ENABLED=false
        // El sistema saltará toda la validación y funcionará
        // exactamente como lo tenías antes, sin pedir licencia.
        if (env('SAAS_MODE_ENABLED', true) === false) {
            return $next($request);
        }

        // 1. Permitir acceso libre a las rutas del instalador para evitar bucles infinitos
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // 2. Verificar si el sistema ya fue instalado leyendo el .env
        $isInstalled = env('APP_INSTALLED', false);

        if (!$isInstalled) {
            // Si no está instalado, redirigir al asistente de instalación
            return redirect()->route('installer.step1');
        }

        // 3. Si está instalado, validamos la licencia con el Servidor Maestro
        $licenseKey = env('APP_LICENSE_KEY');
        $domain = $request->getHost();

        // Guardamos la validez en Caché por 12 horas (43200 segundos) para no saturar tu API Central
        $isLicenseValid = Cache::remember('saas_license_valid', 43200, function () use ($licenseKey, $domain) {
            try {
                // TODO: Cambia esta URL por la URL real de tu panel central en producción
                $masterUrl = env('SAAS_MASTER_URL', 'https://tu-panel-central.com');
                
                $response = Http::timeout(5)->post("{$masterUrl}/api/v1/validate-license", [
                    'license_key' => $licenseKey,
                    'domain'      => $domain,
                ]);

                // Consideramos válido solo si el servidor central responde "success"
                return $response->successful() && $response->json('status') === 'success';
            } catch (\Exception $e) {
                // En caso de caída temporal de tu servidor maestro o pérdida de internet en el colegio, 
                // permitimos el uso para no paralizar la operatividad del cliente.
                return true; 
            }
        });

        if (!$isLicenseValid) {
            // Borramos la caché para que el próximo F5 intente validar de nuevo
            Cache::forget('saas_license_valid');
            abort(403, 'SGA-PADRE: La licencia de este sistema es inválida, expiró o ha sido suspendida. Por favor, contacte a soporte.');
        }

        return $next($request);
    }
}