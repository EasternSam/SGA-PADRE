<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Usamos la vista personalizada de registro de estudiantes/solicitantes
        return view('auth.student-register'); 
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('--- INTENTO DE REGISTRO NUEVO INGRESO (SOLICITANTE) ---');
        Log::info('Datos recibidos:', $request->except(['password', 'password_confirmation']));

        try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users', 'unique:students'],
                'cedula' => ['required', 'string', 'max:20', 'unique:students'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('FALLO VALIDACIÓN REGISTRO:', $e->errors());
            throw $e;
        }

        Log::info('Validación exitosa. Procediendo a crear Usuario y Solicitante...');

        try {
            // 1. Crear el Usuario con acceso temporal
            // La contraseña será la cédula del usuario
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->cedula), 
                'access_expires_at' => Carbon::now()->addMonths(3), // Acceso temporal para completar admisión
                'must_change_password' => true,
            ]);

            Log::info('Usuario base creado ID: ' . $user->id);

            // 2. Asignar rol de Solicitante (NUEVO ROL)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Solicitante');
            } else {
                Log::warning('No se pudo asignar rol: método assignRole no existe en User.');
            }

            // 3. Crear el perfil de Estudiante asociado (como Prospecto)
            Student::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'cedula' => $request->cedula, 
                'status' => 'Prospecto', // Estado inicial
            ]);

            Log::info('Perfil de prospecto creado exitosamente.');

            event(new Registered($user));

            Auth::login($user);

            Log::info('Usuario autenticado. Redirigiendo a portal de solicitante.');

            // Redirigir directamente al portal de aspirante
            return redirect()->route('applicant.portal');

        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO EN PROCESO DE REGISTRO: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if (isset($user) && $user->exists) {
                $user->delete();
                Log::info('Usuario huérfano eliminado por fallo en creación de estudiante.');
            }

            return back()->with('error', 'Ocurrió un error al procesar tu registro. Por favor intenta nuevamente.');
        }
    }
}