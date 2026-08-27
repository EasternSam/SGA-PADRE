<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'level',
        'cycle',
        'grade_number',
        'modality',
        'min_passing_score',
        'order',
        'is_active',
    ];

    protected $casts = [
        'cycle'             => 'integer',
        'grade_number'      => 'integer',
        'min_passing_score' => 'integer',
        'order'             => 'integer',
        'is_active'         => 'boolean',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'grade_level_subject')
                    ->withPivot('weekly_hours')
                    ->withTimestamps();
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimario($query)
    {
        return $query->where('level', 'primario');
    }

    public function scopeSecundario($query)
    {
        return $query->where('level', 'secundario');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ── Helpers ─────────────────────────────────────────────

    public function isPrimary(): bool
    {
        return $this->level === 'primario';
    }

    public function isSecondary(): bool
    {
        return $this->level === 'secundario';
    }

    public function isInitial(): bool
    {
        return $this->level === 'inicial';
    }

    /**
     * Obtener la nota mínima de aprobación según el nivel MINERD.
     * Primario: 65 | Secundario: 70
     */
    public function getPassingScore(): int
    {
        return $this->min_passing_score;
    }

    /**
     * Nombre completo del grado para display.
     */
    public function getDisplayNameAttribute(): string
    {
        $modalitySuffix = $this->modality ? " ({$this->modality})" : '';
        return "{$this->name}{$modalitySuffix}";
    }
}
