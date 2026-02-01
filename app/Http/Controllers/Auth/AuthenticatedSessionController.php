<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log; // Importante para debug

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
        Log::info('--- INTENTO DE LOGIN ---');
        Log::info('Email: ' . $request->email);

        try {
            $request->authenticate();
            Log::info('Autenticación (User/Pass) exitosa.');

            $request->session()->regenerate();
            Log::info('Sesión regenerada.');

            $user = $request->user();
            Log::info('Usuario ID: ' . $user->id . ' | Roles: ' . implode(',', $user->getRoleNames()->toArray()));

            // --- TUS REDIRECCIONES ORIGINALES ---

            if ($user->hasRole('Admin')) {
                Log::info('Rol Admin detectado -> Redirigiendo a admin.dashboard');
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->hasRole('Profesor')) {
                Log::info('Rol Profesor detectado -> Redirigiendo a teacher.dashboard');
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->hasRole('Estudiante')) {
                Log::info('Rol Estudiante detectado -> Redirigiendo a student.dashboard');
                return redirect()->route('student.dashboard');
            }

            // Fallback por defecto (ej: Aspirantes nuevos sin rol)
            Log::info('Sin rol específico -> Redirigiendo a dashboard (general)');
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('ERROR EN LOGIN: ' . $e->getMessage());
            // Esto permite que Laravel muestre el error "Credenciales incorrectas" en el formulario
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