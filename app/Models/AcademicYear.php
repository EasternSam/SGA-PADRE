<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Relaciones ──────────────────────────────────────────

    public function periods(): HasMany
    {
        return $this->hasMany(EvaluationPeriod::class)->orderBy('number');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function currentPeriod(): ?EvaluationPeriod
    {
        return $this->periods()
            ->where('status', 'active')
            ->first();
    }
}
