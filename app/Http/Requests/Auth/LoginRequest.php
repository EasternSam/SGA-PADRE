<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Student; // AÑADIDO: Asegúrate de que el modelo Student está importado
use Carbon\Carbon; // AÑADIDO: Para la lógica de ->isPast()

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'], // MODIFICADO: de 'email' a 'login'
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');

        // --- INICIO DE MODIFICACIÓN (Versión Optimizada) ---
        // 1. Encontrar al usuario por email, cédula o matrícula en UNA sola consulta
        $user = User::where('email', $login)
            ->orWhereHas('student', function ($query) use ($login) {
                $query->where('cedula', $login)
                      ->orWhere('student_code', $login);
            })
            ->first();
        // --- FIN DE MODIFICACIÓN ---

        // 2. Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'), // Usamos 'login'
            ]);
        }

        // 3. Verificar si la cuenta temporal ha expirado (¡MEJORA!)
        // Esta comprobación se hace DESPUÉS de validar la contraseña.
        // No se penaliza (RateLimiter) a un usuario válido.
        if ($user->access_expires_at && $user->access_expires_at->isPast()) {
            
            // ¡No se llama a RateLimiter::hit() aquí!

            throw ValidationException::withMessages([
                // Mensaje personalizado más claro que 'auth.failed'
                'login' => 'Tu acceso temporal ha expirado. Por favor, realiza el pago de tu inscripción para reactivar el acceso.',
            ]);
        }

        // 4. Autenticar al usuario
        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [ // MODIFICADO: de 'email' a 'login'
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // MODIFICADO: Usar 'login' en lugar de 'email' para el throttle key
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}