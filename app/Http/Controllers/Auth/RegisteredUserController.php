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
use Illuminate\Support\Facades\DB; // Importante para la transacción
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
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
        
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users', 'unique:students'],
            'cedula' => ['required', 'string', 'max:20', 'unique:students'],
        ]);

        try {
            // Usamos DB::transaction para asegurar atomicidad: O se crea todo, o no se crea nada.
            $user = DB::transaction(function () use ($request) {
                
                // 1. Crear el Usuario
                $user = User::create([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->cedula), // Contraseña inicial es la cédula
                    'access_expires_at' => Carbon::now()->addMonths(3),
                    'must_change_password' => true,
                ]);

                // 2. Asignar rol
                $user->assignRole('Solicitante');

                // 3. Crear el perfil de Estudiante
                Student::create([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'cedula' => $request->cedula, 
                    'status' => 'Prospecto',
                ]);

                return $user;
            });

            Log::info('Registro completado exitosamente para Usuario ID: ' . $user->id);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('applicant.portal');

        } catch (\Exception $e) {
            // Si algo falla dentro de la transacción, Laravel hace rollback automático.
            // No quedan usuarios "basura" en la BD.
            
            Log::error('ERROR CRÍTICO EN TRANSACCIÓN DE REGISTRO: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->with('error', 'Ocurrió un error interno al procesar tu registro. Por favor intenta nuevamente o contacta soporte.');
        }
    }
}