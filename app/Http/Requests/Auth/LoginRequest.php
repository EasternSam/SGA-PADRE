<?php

namespace App\Http\Requests;

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');

        // NORMALIZACIÓN: Si el login no es un email, quitamos guiones por si es una cédula
        $cleanLogin = !filter_var($login, FILTER_VALIDATE_EMAIL) 
            ? preg_replace('/[^A-Za-z0-9]/', '', $login) 
            : $login;

        // 1. Encontrar al usuario por email, cédula (normalizada o original) o matrícula
        $user = User::where('email', $login)
            ->orWhereHas('student', function ($query) use ($login, $cleanLogin) {
                $query->where('cedula', $login)
                      ->orWhere('cedula', $cleanLogin)
                      ->orWhere('student_code', $login)
                      ->orWhere('student_code', $cleanLogin);
            })
            ->first();

        // 2. Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // 3. Verificar si la cuenta temporal ha expirado
        if ($user->access_expires_at && $user->access_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'login' => 'Tu acceso temporal ha expirado. Por favor, realiza el pago de tu inscripción para reactivar el acceso.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

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

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}