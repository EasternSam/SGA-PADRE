<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response|View
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $ip = $request->ip();

        $ipKey = 'login_ip_global:' . $ip;
        RateLimiter::hit($ipKey, 3600);

        try {
            $request->authenticate();

            RateLimiter::clear($ipKey);

            $request->session()->regenerate();

            $user = $request->user();

            Log::info("[LOGIN OK] User #{$user->id} ({$user->email}) desde IP: {$ip}");

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
            throw $e;
        } catch (\Exception $e) {
            Log::error("[LOGIN ERROR] " . $e->getMessage() . " | IP: {$ip}");
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        $userId = Auth::id();
        Log::info("[LOGOUT] User #{$userId} cerró sesión");

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location('/login');
    }
}
