<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyRemoteToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // INTENTO 1: Buscar en config (Mejor práctica para producción con caché)
        // INTENTO 2: Buscar directo en env (Fallback si no está en config/services.php)
        $systemSecret = config('services.remote.secret') ?? env('REMOTE_SECRET_KEY');
        
        $inputSecret = $request->header('X-Remote-Secret') ?? $request->input('secret');

        // Validación estricta
        if (empty($systemSecret)) {
            Log::critical('SECURITY WARNING: REMOTE_SECRET_KEY is not set on server.');
            return response()->json(['success' => false, 'message' => 'Server configuration error.'], 500);
        }

        if ($inputSecret !== $systemSecret) {
            Log::warning('Unauthorized access attempt to Remote API from IP: ' . $request->ip());
            
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized: Invalid or missing remote secret key.'
            ], 403);
        }

        return $next($request);
    }
}