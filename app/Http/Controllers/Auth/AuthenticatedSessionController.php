<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
     * 
     * Seguridad:
     * - El LoginRequest maneja rate limiting, honeypot, auditoría, etc.
     * - Este controlador se encarga de la sesión y redirección segura.
     * - Se incrementa el throttle de IP global en cada intento.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $ip = $request->ip();

        // Registrar intento en el throttle GLOBAL de IP (previo a autenticación)
        $ipKey = 'login_ip_global:' . $ip;
        RateLimiter::hit($ipKey, 3600); // 1 hora de ventana

        try {
            $request->authenticate();

            // Éxito — limpiar throttle de IP
            RateLimiter::clear($ipKey);

            $request->session()->regenerate();

            $user = $request->user();

            Log::info("[LOGIN OK] User #{$user->id} ({$user->email}) desde IP: {$ip}");

            // --- REDIRECCIÓN POR ROL ---
            if ($user->hasRole('Admin') || $user->hasAnyRole(['Registro', 'Contabilidad', 'Caja'])) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->hasRole('Profesor')) {
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->hasRole('Estudiante')) {
                return redirect()->route('student.dashboard');
            }

            if ($user->hasRole('Solicitante')) {
                return redirect()->route('applicant.portal');
            }

            return redirect()->intended(route('dashboard'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error de validación (auth fallida o rate limited)
            // El throttle de IP ya se contabilizó arriba
            throw $e;
        } catch (\Exception $e) {
            Log::error("[LOGIN ERROR] " . $e->getMessage() . " | IP: {$ip}");
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        Log::info("[LOGOUT] User #{$userId} cerró sesión");

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}