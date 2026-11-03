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
        // Excluir modo Kiosco / Tablet de redirección forzada de contraseña web
        if ($request->is('kiosk') || $request->is('kiosk/*') || $request->is('tablet')) {
            return $next($request);
        }

        // 1. Verificar si el usuario está logueado
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // 2. Verificar si tiene la bandera 'must_change_password' activada
        if ($user->must_change_password) {
            $allowedRoutes = [
                'password.force_change',
                'password.force_update',
                'logout',
            ];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('password.force_change');
            }
        }

        return $next($request);
    }
}
