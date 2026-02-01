<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student; // <-- Importado
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // <-- Importado

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Nota: Asegúrate de que esta vista tenga los campos first_name, last_name, cedula
        return view('auth.student-register'); 
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('--- INTENTO DE REGISTRO NUEVO INGRESO (LÓGICA ASPIRANTE) ---');
        Log::info('Datos recibidos:', $request->except(['password', 'password_confirmation']));

        try {
            // --- CAMBIO LÓGICA DE VALIDACIÓN ---
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                // Se valida unicidad en usuarios y en estudiantes
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users', 'unique:students'],
                'cedula' => ['required', 'string', 'max:20', 'unique:students'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('FALLO VALIDACIÓN REGISTRO:', $e->errors());
            throw $e;
        }

        Log::info('Validación exitosa. Procediendo a crear Usuario y Estudiante...');

        try {
            // 1. Crear el Usuario con acceso temporal
            // La contraseña será la cédula del usuario
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->cedula), 
                'access_expires_at' => Carbon::now()->addMonths(3),
                'must_change_password' => true, // Opcional: forzar cambio al primer login
            ]);

            Log::info('Usuario base creado ID: ' . $user->id);

            // 2. Asignar rol de Estudiante
            // Verificamos si existe el método (depende de Spatie Permission)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Estudiante');
            } else {
                Log::warning('No se pudo asignar rol: método assignRole no existe en User.');
            }

            // 3. Crear el perfil de Estudiante asociado
            Student::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'identification_id' => $request->cedula, // Mapeamos cedula a identification_id o el campo que uses en la BD
                'status' => 'Prospecto', // Estado inicial para alguien que se registra solo
                // 'phone' => $request->phone, // Si agregas teléfono al form, agrégalo aquí
            ]);

            Log::info('Perfil de estudiante creado exitosamente.');

            event(new Registered($user));

            Auth::login($user);

            Log::info('Usuario autenticado. Redirigiendo a dashboard/portal.');

            return redirect(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO EN PROCESO DE REGISTRO: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Ocurrió un error al procesar tu registro. Por favor intenta nuevamente.');
        }
    }
}