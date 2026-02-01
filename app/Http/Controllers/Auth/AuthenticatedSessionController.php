<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('--- INTENTO DE LOGIN (Standard) ---');
        Log::info('Email: ' . $request->email);

        try {
            // Usamos el método authenticate() de LoginRequest que maneja rate limiting y validación auth
            $request->authenticate();
            
            Log::info('Autenticación exitosa.');

            $request->session()->regenerate();

            $user = $request->user();
            
            // Log de roles para depuración
            $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
            Log::info('Usuario ID: ' . $user->id . ' | Roles: ' . implode(',', $roles));

            // --- LÓGICA DE REDIRECCIÓN ORIGINAL ---
            // Recuperamos la lógica exacta que funcionaba para Admin/Profesor

            if ($user->hasRole('Admin')) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->hasRole('Profesor')) {
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->hasRole('Estudiante')) {
                return redirect()->route('student.dashboard');
            }

            // Fallback para usuarios sin rol específico (ej. recién registrados)
            Log::info('Redirigiendo a dashboard general.');
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('ERROR EN LOGIN: ' . $e->getMessage());
            // Re-lanzamos la excepción para que Laravel devuelva los errores al formulario
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}