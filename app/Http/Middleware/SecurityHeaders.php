<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad HTTP + Anti-Slowloris a nivel de respuesta.
 * 
 * Protege contra:
 * - Clickjacking (X-Frame-Options)
 * - XSS reflexivo (X-XSS-Protection)
 * - MIME sniffing (X-Content-Type-Options)
 * - Referrer leaking (Referrer-Policy)
 * - Information leaking (X-Powered-By removal)
 * - Slowloris (Keep-Alive agresivo + Connection control)
 * - Cache poisoning (Cache-Control en rutas sensibles)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ═══════════════════════════════════════════════════════
        // HEADERS DE SEGURIDAD ESTÁNDAR
        // ═══════════════════════════════════════════════════════

        // Prevenir clickjacking (pero permitir iframes de Cardnet)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // CSP: permitir scripts/frames de Cardnet
        $cardnetDomain = parse_url(config('services.cardnet.base_uri', ''), PHP_URL_HOST) ?: 'lab.cardnet.com.do';
        $response->headers->set(
            'Content-Security-Policy',
            "frame-src 'self' https://*.cardnet.com.do https://{$cardnetDomain}; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.cardnet.com.do https://{$cardnetDomain} https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: https: blob:; " .
            "font-src 'self' https://fonts.bunny.net https://cdnjs.cloudflare.com; " .
            "connect-src 'self' https://*.cardnet.com.do https://{$cardnetDomain};"
        );

        // Prevenir MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protección XSS del browser
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Controlar Referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Controlar permisos del browser
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Ocultar tecnología
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // ═══════════════════════════════════════════════════════
        // ANTI-SLOWLORIS: Control de conexión
        // ═══════════════════════════════════════════════════════

        // Keep-Alive con timeout corto para liberar conexiones rápido
        // Esto reduce la ventana de ataque Slowloris significativamente
        $response->headers->set('Keep-Alive', 'timeout=5, max=100');

        // Para rutas de autenticación: cerrar conexión inmediatamente
        // Los atacantes apuntan al /login para Slowloris
        if ($request->is('login', 'register', 'forgot-password', 'reset-password', 'kiosk/login', 'kiosk/signup')) {
            $response->headers->set('Connection', 'close');
        }

        // ═══════════════════════════════════════════════════════
        // CACHE CONTROL: Rutas sensibles
        // ═══════════════════════════════════════════════════════

        // No cachear NUNCA las páginas de auth
        if ($request->is('login', 'register', 'dashboard', 'admin/*', 'kiosk/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        // HSTS en producción
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
