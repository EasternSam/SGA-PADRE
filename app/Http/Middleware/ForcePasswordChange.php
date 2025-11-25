<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar si el usuario está logueado
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // 2. Verificar si tiene la bandera 'must_change_password' activada
        if ($user->must_change_password) {
            
            // 3. Permitir acceso solo a las rutas de cambio de contraseña y logout
            // (Ajusta los nombres de ruta según definiremos abajo)
            $allowedRoutes = [
                'password.force_change', // La pantalla del formulario
                'password.force_update', // La acción POST para guardar
                'logout',                // Permitir salir
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('password.force_change');
            }
        }

        return $next($request);
    }
}