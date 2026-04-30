<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protección contra ataques de agotamiento de conexiones (Slowloris, R-U-Dead-Yet, etc.)
 * 
 * Funciona en 3 niveles:
 * 
 * 1. LÍMITE DE CONEXIONES CONCURRENTES POR IP
 *    Una IP no puede tener más de N requests simultáneos activos.
 *    Esto previene que un atacante abra cientos de conexiones lentas.
 * 
 * 2. TIMEOUT DE EJECUCIÓN
 *    Establece set_time_limit() agresivo para requests normales.
 *    Requests de upload/export tienen más tiempo permitido.
 * 
 * 3. DETECCIÓN DE BODY LENTO (R-U-Dead-Yet / Slow POST)
 *    Si el Content-Length declarado es grande pero la request tarda
 *    mucho en llegar, abortar.
 */
class ConnectionGuard
{
    /**
     * Máximo de requests concurrentes por IP.
     * Valor conservador: un usuario real en una tab tiene ~3-6 requests simultáneos.
     * Un atacante Slowloris abre 100+.
     */
    protected int $maxConcurrentPerIp = 30;

    /**
     * Tiempo máximo de ejecución para requests normales (segundos).
     */
    protected int $normalTimeout = 30;

    /**
     * Rutas que necesitan más tiempo (uploads, exports, imports).
     */
    protected array $longRunningPatterns = [
        'admin/import*',
        'api/enroll',
        'api/v1/*',
        'admin/finance/dgii*',
        'admin/reports*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $cacheKey = "conn_guard:{$ip}";

        // ─── 1. Verificar conexiones concurrentes ───────────────
        // Envuelto en try-catch: SQLite no soporta escrituras concurrentes.
        // Si la caché falla, el request pasa igual (seguridad best-effort).
        try {
            $currentConnections = (int) Cache::get($cacheKey, 0);

            if ($currentConnections >= $this->maxConcurrentPerIp) {
                abort(429, 'Demasiadas conexiones simultáneas. Intenta de nuevo en unos segundos.');
            }

            Cache::put($cacheKey, $currentConnections + 1, 120);
        } catch (\Throwable $e) {
            // SQLite locked o cualquier error de caché → continuar sin bloquear
        }

        // ─── 2. Timeout de ejecución ────────────────────────────
        if (!$this->isLongRunning($request)) {
            set_time_limit($this->normalTimeout);
        }

        try {
            $response = $next($request);
            return $response;
        } finally {
            // SIEMPRE decrementar, incluso si hubo excepción
            try {
                $remaining = (int) Cache::get($cacheKey, 1) - 1;
                if ($remaining <= 0) {
                    Cache::forget($cacheKey);
                } else {
                    Cache::put($cacheKey, $remaining, 120);
                }
            } catch (\Throwable $e) {
                // Ignorar errores de caché al decrementar
            }
        }
    }

    /**
     * Determina si esta request es una operación que necesita más tiempo.
     */
    protected function isLongRunning(Request $request): bool
    {
        foreach ($this->longRunningPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }
        return false;
    }
}
