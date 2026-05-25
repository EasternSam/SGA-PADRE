<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'description', 'old_values', 'new_values',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const ACTIONS = [
        'created'  => '➕ Creado',
        'updated'  => '✏️ Editado',
        'deleted'  => '🗑️ Eliminado',
        'exported' => '📥 Exportado',
        'login'    => '🔑 Login',
        'logout'   => '🚪 Logout',
        'approved' => '✅ Aprobado',
        'rejected' => '❌ Rechazado',
        'locked'   => '🔒 Bloqueado',
        'unlocked' => '🔓 Desbloqueado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action
     */
    public static function log(string $action, ?string $description = null, $model = null, ?array $oldValues = null, ?array $newValues = null): self
    {
        return static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id ?? null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
