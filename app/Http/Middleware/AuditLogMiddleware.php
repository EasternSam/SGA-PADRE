<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Capturar datos ANTES de procesar
        $startTime = microtime(true);
        $user = Auth::user();
        $userId = $user ? "ID:{$user->id} ({$user->name})" : 'Invitado/Anónimo';
        
        // Filtrar datos sensibles
        $payload = $request->except(['password', 'password_confirmation', 'credit_card', 'cvv']);

        // 2. Procesar la solicitud
        $response = $next($request);

        // 3. Calcular duración
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // 4. Escribir en el log dedicado 'audit'
        // Ignoramos peticiones de debugbar o assets para no saturar
        if (!$request->is('livewire/message*') && !$request->is('_debugbar*')) {
            Log::channel('audit')->info("🌍 WEB REQUEST", [
                'Usuario' => $userId,
                'IP' => $request->ip(),
                'Método' => $request->method(),
                'URL' => $request->fullUrl(),
                'Datos' => json_encode($payload),
                'Status' => $response->getStatusCode(),
                'Duración' => "{$duration}ms"
            ]);
        } 
        // Si es Livewire, lo logueamos de forma especial
        elseif ($request->is('livewire/message*')) {
             $component = $request->input('components.0.snapshot');
             $memo = $component ? json_decode($component, true)['memo']['name'] ?? 'Unknown' : 'Unknown';
             
             Log::channel('audit')->info("⚡ LIVEWIRE ACTION [{$memo}]", [
                'Usuario' => $userId,
                'URL' => $request->header('referer'), // De dónde vino
                'Updates' => json_encode($request->input('components.0.updates')),
                'Calls' => json_encode($request->input('components.0.calls')),
            ]);
        }

        return $response;
    }
}