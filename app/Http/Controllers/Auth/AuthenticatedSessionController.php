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
            $request->authenticate();
            
            Log::info('Autenticación exitosa.');

            $request->session()->regenerate();

            $user = $request->user();
            
            $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
            Log::info('Usuario ID: ' . $user->id . ' | Roles: ' . implode(',', $roles));

            // --- LÓGICA DE REDIRECCIÓN ---

            if ($user->hasRole('Admin') || $user->hasAnyRole(['Registro', 'Contabilidad', 'Caja'])) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->hasRole('Profesor')) {
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->hasRole('Estudiante')) {
                return redirect()->route('student.dashboard');
            }

            // --- NUEVO: Redirección para el rol Solicitante ---
            if ($user->hasRole('Solicitante')) {
                Log::info('Rol Solicitante detectado -> Redirigiendo a applicant.portal');
                return redirect()->route('applicant.portal');
            }

            Log::info('Redirigiendo a dashboard general (Usuario sin rol específico).');
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('ERROR EN LOGIN: ' . $e->getMessage());
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