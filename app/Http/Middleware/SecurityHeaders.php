<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Agrega cabeceras de seguridad HTTP a todas las respuestas.
 * 
 * Protege contra:
 * - Clickjacking (X-Frame-Options)
 * - XSS reflexivo (X-XSS-Protection + Content-Security-Policy)
 * - MIME sniffing (X-Content-Type-Options)
 * - Referrer leaking (Referrer-Policy)
 * - Information leaking (X-Powered-By removal)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir clickjacking — no se puede embeber en iframe de otro dominio
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME-sniffing de browsers
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Activar protección XSS del browser (legacy pero útil)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Controlar qué información se envía en el Referrer header
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Controlar permisos del browser (cámara, micrófono, geolocalización, etc.)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Ocultar la tecnología del servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->set('X-Powered-By', 'CENTU-SGA');

        // Forzar HTTPS en producción (HSTS)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
