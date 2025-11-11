<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
        $request->authenticate();

        $request->session()->regenerate();

        // --- ¡¡¡CORRECCIÓN!!! ---
        // Se usan los roles en español para la redirección.
        // "Admin" se queda igual porque coincide con el Seeder.

        if ($request->user()->hasRole('Admin')) {
            // Redirigir al dashboard de Admin
            return redirect()->route('admin.dashboard');
        }
        
        if ($request->user()->hasRole('Profesor')) { // Cambiado de 'Teacher'
            // Redirigir al dashboard de Profesor
            return redirect()->route('teacher.dashboard');
        }
        
        if ($request->user()->hasRole('Estudiante')) { // Cambiado de 'Student'
            // Redirigir al dashboard de Estudiante
            return redirect()->route('student.dashboard');
        }

        // Fallback por defecto (aunque la ruta 'dashboard' ya maneja esto)
        return redirect()->intended(route('dashboard'));
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