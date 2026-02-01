<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Se mantiene pero no se usa en el tipo
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // <-- USAMOS ESTE para poder entrar al método sin validar antes
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
        // 1. LOG INICIAL (Si esto no sale, el problema es la ruta web.php)
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
                Log::error("DEBUG HASH: La contraseña NO coincide. Revisar si se guardó bien la cédula en el registro.");
            }
        }

        // 4. Intento de Autenticación Real
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::error('Auth::attempt devolvió FALSE.');
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 5. Éxito
        Log::info('Autenticación exitosa. Regenerando sesión...');
        $request->session()->regenerate();

        $user = Auth::user();
        Log::info("Usuario logueado. Roles: " . implode(',', $user->getRoleNames()->toArray()));

        // --- LÓGICA DE REDIRECCIÓN ---

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->hasRole('Profesor')) {
            return redirect()->route('teacher.dashboard');
        }
        
        if ($user->hasRole('Estudiante')) {
            return redirect()->route('student.dashboard');
        }

        Log::info('Redirigiendo a dashboard general.');
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