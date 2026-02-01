<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Se mantiene para compatibilidad de tipos si es necesario
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Usamos Request genérico para validación manual
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
    public function store(Request $request): RedirectResponse
    {
        // 1. LOG INICIAL
        Log::info('--- DEBUG LOGIN START (Bypass LoginRequest) ---');
        Log::info('Datos recibidos:', $request->only('email'));

        // 2. Validación Manual con Log de Errores
        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
            Log::info('Validación de formato: CORRECTA');
        } catch (ValidationException $e) {
            Log::error('Error de validación de formato (campos vacíos o email inválido):', $e->errors());
            throw $e; // Devuelve los errores a la vista
        }

        // 3. Diagnóstico de Credenciales antes de intentar Auth
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            Log::error("LOGIN FALLIDO: No existe usuario con el email: " . $request->email);
        } else {
            Log::info("Usuario encontrado ID: {$user->id}. Verificando contraseña...");
            // Chequeo manual del hash para ver si coincide
            if (Hash::check($request->password, $user->password)) {
                Log::info("DEBUG HASH: La contraseña coincide manualmente.");
            } else {
                Log::error("DEBUG HASH: La contraseña NO coincide.");
            }
        }

        // 4. Intento de Autenticación Real
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::error('Auth::attempt devolvió FALSE.');
            
            // Mensaje de error genérico para seguridad, pero log detallado arriba
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 5. Éxito
        Log::info('Autenticación exitosa. Regenerando sesión...');
        $request->session()->regenerate();

        $user = Auth::user();
        
        // Verificar roles de manera segura (si Spatie no está configurado, evita error)
        $roles = method_exists($user, 'getRoleNames') ? implode(',', $user->getRoleNames()->toArray()) : 'Sin Roles (Spatie)';
        Log::info("Usuario logueado. Roles: " . $roles);

        // --- LÓGICA DE REDIRECCIÓN ---

        // Verificar roles usando el trait de Spatie si está disponible
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('Admin')) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->hasRole('Profesor')) {
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->hasRole('Estudiante')) {
                return redirect()->route('student.dashboard');
            }
        }

        Log::info('Redirigiendo a dashboard general (Usuario sin rol específico o nuevo ingreso).');
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