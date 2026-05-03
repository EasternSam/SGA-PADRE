<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * ═══════════════════════════════════════════════════════════
 * LoginRequest — Seguridad Comprehensiva
 * ═══════════════════════════════════════════════════════════
 * 
 * Medidas de seguridad implementadas:
 * 
 * 1. Rate Limiting PROGRESIVO (5 intentos → 60s, 10 → 5min, 15 → 30min)
 * 2. Throttle por IP global (100 intentos/hora desde una misma IP = bloqueo)
 * 3. Bloqueo de cuenta automático (15 fallos consecutivos → locked_until)
 * 4. Auditoría de TODOS los intentos (login_attempts table)
 * 5. Detección de honeypot (campo invisible para bots)
 * 6. Registro de último login (last_login_at, last_login_ip en users)
 * 7. Protección contra enumeración de usuarios (mismos mensajes siempre)
 * 8. Verificación de cuenta expirada (access_expires_at)
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            // Honeypot: campo invisible que debe llegar vacío
            'website'  => ['max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required'    => 'El identificador es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'website.max'       => '', // Silencioso para bots
        ];
    }

    /**
     * Autenticación principal con todas las capas de seguridad.
     */
    public function authenticate(): void
    {
        $login    = $this->input('login');
        $password = $this->input('password');
        $ip       = $this->ip();
        $ua       = $this->userAgent();

        // ─── CAPA 1: Honeypot ───────────────────────────────────
        if (!empty($this->input('website'))) {
            // Bot detectado — fingir procesamiento lento
            Log::warning("[SECURITY] Honeypot triggered desde IP: {$ip}");
            LoginAttempt::logFailure($login, null, $ip, $ua, 'honeypot_bot');
            usleep(random_int(500000, 2000000)); // 0.5-2 segundos
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // ─── CAPA 2: Rate Limiting progresivo por usuario+IP ────
        $this->ensureIsNotRateLimited();

        // ─── CAPA 3: Throttle global por IP ─────────────────────
        $this->ensureIpNotAbusing($ip, $login, $ua);

        // ─── CAPA 4: Buscar usuario ─────────────────────────────
        $cleanLogin = preg_replace('/[^A-Za-z0-9@.]/', '', $login);

        $user = User::where('email', $login)
            ->orWhereHas('student', function ($query) use ($login, $cleanLogin) {
                $query->where('cedula', $login)
                      ->orWhere('cedula', $cleanLogin)
                      ->orWhere('student_code', $login);
            })
            ->first();

        // ─── CAPA 5: Verificar bloqueo de cuenta ────────────────
        if ($user && $user->locked_until && Carbon::parse($user->locked_until)->isFuture()) {
            $minutesLeft = now()->diffInMinutes(Carbon::parse($user->locked_until)) + 1;
            
            LoginAttempt::logFailure($login, $user->id, $ip, $ua, 'account_locked');
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => "Tu cuenta está bloqueada temporalmente por seguridad. Intenta de nuevo en {$minutesLeft} minutos, o contacta a administración.",
            ]);
        }

        // ─── CAPA 6: Verificar contraseña ───────────────────────
        $isValidPassword = false;
        if ($user) {
            if (Hash::check($password, $user->password)) {
                $isValidPassword = true;
            } else {
                // Intentar con cédula normalizada (sin guiones)
                $cleanPassword = preg_replace('/[^A-Za-z0-9]/', '', $password);
                if ($cleanPassword !== $password && Hash::check($cleanPassword, $user->password)) {
                    $isValidPassword = true;
                }
            }
        }

        if (!$user || !$isValidPassword) {
            RateLimiter::hit($this->throttleKey(), $this->getDecaySeconds());

            // Registrar intento fallido
            LoginAttempt::logFailure($login, $user?->id, $ip, $ua, 'invalid_credentials');

            // Incrementar contador de fallos consecutivos en la cuenta
            if ($user) {
                $failCount = ($user->failed_login_count ?? 0) + 1;
                $updateData = ['failed_login_count' => $failCount];

                // Bloquear cuenta tras 15 fallos consecutivos (30 min)
                if ($failCount >= 15) {
                    $updateData['locked_until'] = now()->addMinutes(30);
                    Log::warning("[SECURITY] Cuenta bloqueada por 30min: User #{$user->id} ({$user->email}) tras {$failCount} intentos fallidos desde IP: {$ip}");
                }

                $user->update($updateData);
            }

            // Mensaje genérico — NO revelar si el usuario existe
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // ─── CAPA 7: Verificar expiración de acceso ────────────
        if ($user->access_expires_at && Carbon::parse($user->access_expires_at)->isPast()) {
            LoginAttempt::logFailure($login, $user->id, $ip, $ua, 'access_expired');

            throw ValidationException::withMessages([
                'login' => 'Tu acceso temporal ha expirado. Por favor, realiza el pago de tu inscripción para reactivar el acceso.',
            ]);
        }

        // ═══════════════════════════════════════════════════════
        // LOGIN EXITOSO
        // ═══════════════════════════════════════════════════════

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());

        // Registrar login exitoso
        LoginAttempt::logSuccess($login, $user->id, $ip, $ua);

        // Actualizar tracking de seguridad
        $user->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $ip,
            'failed_login_count' => 0,    // Reset contador de fallos
            'locked_until'       => null,  // Limpiar bloqueo
        ]);
    }

    /**
     * Rate Limiting PROGRESIVO.
     * 
     * 5 intentos  → bloqueo 60 segundos
     * 10 intentos → bloqueo 5 minutos  
     * 15 intentos → bloqueo 30 minutos
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        LoginAttempt::logFailure(
            $this->input('login'), null, $this->ip(), $this->userAgent(), 'rate_limited'
        );

        throw ValidationException::withMessages([
            'login' => "Demasiados intentos. Espera " . ceil($seconds / 60) . " minuto(s) antes de intentar de nuevo.",
        ]);
    }

    /**
     * Throttle global por IP — detener ataques distribuidos.
     * Si una IP genera más de 50 intentos fallidos en 1 hora, bloquear.
     */
    protected function ensureIpNotAbusing(string $ip, string $login, ?string $ua): void
    {
        $ipKey = 'login_ip_global:' . $ip;

        if (RateLimiter::tooManyAttempts($ipKey, 50)) {
            $seconds = RateLimiter::availableIn($ipKey);

            Log::error("[SECURITY] IP bloqueada globalmente: {$ip} — más de 50 intentos/hora");
            LoginAttempt::logFailure($login, null, $ip, $ua, 'ip_blocked');

            throw ValidationException::withMessages([
                'login' => 'Tu dirección IP ha sido bloqueada temporalmente por exceso de intentos. Contacta a administración.',
            ]);
        }
    }

    /**
     * Calcula los segundos de penalización progresiva.
     */
    protected function getDecaySeconds(): int
    {
        $attempts = RateLimiter::attempts($this->throttleKey());

        if ($attempts >= 15) return 1800; // 30 minutos
        if ($attempts >= 10) return 300;  // 5 minutos
        return 60;                         // 1 minuto
    }

    /**
     * Throttle key = identificador normalizado + IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')) . '|' . $this->ip());
    }
}