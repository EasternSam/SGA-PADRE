<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeLockPeriod extends Model
{
    protected $fillable = [
        'evaluation_period_id', 'lock_date', 'is_locked',
        'lock_reason', 'locked_by',
    ];

    protected $casts = [
        'lock_date'  => 'date',
        'is_locked'  => 'boolean',
    ];

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'locked_by');
    }

    /**
     * Check if a period is locked for grade editing.
     */
    public static function isLocked($periodId): bool
    {
        $lock = self::where('evaluation_period_id', $periodId)->first();
        if (!$lock) return false;

        return $lock->is_locked || ($lock->lock_date && $lock->lock_date->isPast());
    }
}
