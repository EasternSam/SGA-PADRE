<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'number',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'number'     => 'integer',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function isCurrent(): bool
    {
        return $this->status === 'active';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['active', 'grading']);
    }

    /**
     * Nombre corto para display: "P1", "P2", etc.
     */
    public function getShortNameAttribute(): string
    {
        return "P{$this->number}";
    }
}
