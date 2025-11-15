<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student; // <-- AÑADIDO: Importar modelo Student
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon; // <-- AÑADIDO: Importar Carbon para la fecha de expiración

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // --- CAMBIO LÓGICA DE VALIDACIÓN ---
        // Ahora pedimos nombre, apellido, email y cédula.
        // La contraseña se genera automáticamente.
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, 'unique:'.Student::class],
            'cedula' => ['required', 'string', 'max:20', 'unique:'.Student::class],
            // 'password' => ['required', 'confirmed', Rules\Password::defaults()], // <-- ELIMINADO
        ]);

        // 1. Crear el Usuario con acceso temporal
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->cedula), // <-- ¡CAMBIO! Contraseña es la Cédula
            'access_expires_at' => Carbon::now()->addMonths(3), // <-- AÑADIDO: Acceso temporal
        ]);

        // 2. Asignar rol de Estudiante
        $user->assignRole('Estudiante'); // Asumiendo que el rol 'Estudiante' existe

        // 3. Crear el perfil de Estudiante asociado
        Student::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'cedula' => $request->cedula, // <-- AÑADIDO
            'status' => 'Activo', // O 'Prospecto', según la lógica de negocio
            'is_minor' => false, // Valor por defecto
        ]);


        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}