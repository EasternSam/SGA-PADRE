<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRemoteToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // La clave debe estar en el .env de la escuela: REMOTE_SECRET_KEY=miclavesecreta
        $systemSecret = env('REMOTE_SECRET_KEY');
        $inputSecret = $request->header('X-Remote-Secret') ?? $request->input('secret');

        if (!$systemSecret || $inputSecret !== $systemSecret) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized: Invalid or missing remote secret key.'
            ], 403);
        }

        return $next($request);
    }
}