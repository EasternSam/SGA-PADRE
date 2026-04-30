<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de un intento de login (exitoso o fallido).
 * Se usa para auditoría de seguridad, detección de ataques, y reportes admin.
 */
class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'login_identifier',
        'user_id',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'attempted_at',
    ];

    protected $casts = [
        'successful'   => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra un intento fallido.
     */
    public static function logFailure(string $identifier, ?int $userId, string $ip, ?string $userAgent, string $reason = 'invalid_credentials'): self
    {
        return self::create([
            'login_identifier' => $identifier,
            'user_id'          => $userId,
            'ip_address'       => $ip,
            'user_agent'       => $userAgent ? substr($userAgent, 0, 500) : null,
            'successful'       => false,
            'failure_reason'   => $reason,
            'attempted_at'     => now(),
        ]);
    }

    /**
     * Registra un login exitoso.
     */
    public static function logSuccess(string $identifier, int $userId, string $ip, ?string $userAgent): self
    {
        return self::create([
            'login_identifier' => $identifier,
            'user_id'          => $userId,
            'ip_address'       => $ip,
            'user_agent'       => $userAgent ? substr($userAgent, 0, 500) : null,
            'successful'       => true,
            'failure_reason'   => null,
            'attempted_at'     => now(),
        ]);
    }

    /**
     * Cuenta intentos fallidos recientes desde una IP.
     */
    public static function recentFailuresFromIp(string $ip, int $minutes = 30): int
    {
        return self::where('ip_address', $ip)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Limpieza: Eliminar registros más antiguos de N días.
     */
    public static function pruneOlderThan(int $days = 90): int
    {
        return self::where('attempted_at', '<', now()->subDays($days))->delete();
    }
}
