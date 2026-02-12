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
        // VALIDACIÓN EN TIEMPO REAL (SIN CACHÉ)
        // Se ha eliminado Cache::remember para efecto inmediato.
        // =========================================================
        
        $isLicenseValid = true; // Por defecto dejamos pasar si hay error de conexión (Fail Open)

        try {
            // Timeout ajustado a 3 segundos para no afectar la experiencia de usuario
            $response = Http::withoutVerifying()
                ->timeout(3)
                ->post("{$masterUrl}/api/v1/validate-license", [
                    'license_key' => $licenseKey,
                    'domain'      => $domain,
                ]);

            // Si el servidor maestro responde que todo está bien
            if ($response->successful() && $response->json('status') === 'success') {
                $isLicenseValid = true;
            }
            // Si el servidor responde explícitamente que NO (403 Suspendido, 404 No existe)
            elseif ($response->status() === 403 || $response->status() === 404) {
                $isLicenseValid = false;
            }
            // Cualquier otro código de estado (ej. error 500 en el maestro) -> Bloqueamos por seguridad
            else {
                $isLicenseValid = false;
            }

        } catch (\Exception $e) {
            // Si falla la conexión (Internet caído, DNS, Timeout)
            // Registramos el error pero dejamos pasar al cliente para no detener su operación
            // Log::error("SaaS Connection Error: " . $e->getMessage()); // Descomentar para debug
            $isLicenseValid = true; 
        }

        if (!$isLicenseValid) {
            abort(403, 'Academic+: Su licencia ha sido SUSPENDIDA o expiró. Contacte a soporte para reactivación inmediata.');
        }

        return $next($request);
    }
}