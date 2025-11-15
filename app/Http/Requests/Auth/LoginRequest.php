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

        // --- INICIO DE MODIFICACIÓN ---
        // Intentar encontrar al usuario por email, student_code o cédula
        
        // 1. Intentar por email (en la tabla 'users')
        $user = User::where('email', $login)->first();

        // 2. Si no se encuentra por email, intentar por student_code (en la tabla 'students')
        if (!$user) {
            $student = Student::where('student_code', $login)->first();
            if ($student) {
                $user = $student->user; // Obtener el usuario asociado
            }
        }

        // 3. Si sigue sin encontrarse, intentar por cédula (en la tabla 'students')
        if (!$user) {
            $student = Student::where('cedula', $login)->first();
            if ($student) {
                $user = $student->user; // Obtener el usuario asociado
            }
        }
        // --- FIN DE MODIFICACIÓN ---

        // 4. Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'), // MODIFICADO: de 'email' a 'login'
            ]);
        }

        // 5. Verificar si la cuenta temporal ha expirado
        if ($user->access_expires_at && $user->access_expires_at->isPast()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Esta cuenta temporal ha expirado. Por favor, complete el pago de su inscripción.', // MODIFICADO: de 'email' a 'login'
            ]);
        }

        // 6. Autenticar al usuario
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