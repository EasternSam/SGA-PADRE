<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Student; // Asegúrate de importar el modelo Student

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            // Cambiamos 'email' a 'login' para que acepte email, username o cédula
            // Ya no validamos como 'email' aquí, lo haremos en el método authenticate
            'login' => ['required', 'string'],
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

        // 1. Intentar encontrar al usuario por email o matrícula
        $user = User::where('email', $login)->first();

        // 2. Si no se encuentra, intentar buscar por cédula en la tabla de estudiantes
        if (!$user) {
            $student = Student::where('cedula', $login)->first();
            if ($student && $student->user) {
                $user = $student->user;
            }
        }

        // 3. Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // 4. Verificar si la cuenta temporal ha expirado
        if ($user->access_expires_at && $user->access_expires_at->isPast()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Esta cuenta temporal ha expirado. Por favor, complete el pago de su inscripción.',
            ]);
        }

        // 5. Autenticar al usuario
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
            'login' => trans('auth.throttle', [
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
        // Usar 'login' en lugar de 'email' para el throttle key
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}