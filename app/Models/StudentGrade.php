<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'section_subject_id',
        'evaluation_period_id',
        'score',
        'performance_level',
        'is_recovery',
        'is_extraordinary',
        'observations',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'score'            => 'decimal:2',
        'is_recovery'      => 'boolean',
        'is_extraordinary' => 'boolean',
        'recorded_at'      => 'datetime',
    ];

    // ── Niveles de desempeño MINERD (Ordenanza 04-2023) ─────

    const LEVEL_DESTACADO          = 'destacado';           // 89-100
    const LEVEL_LOGRO_EVIDENCIADO  = 'logro_evidenciado';   // 77-88
    const LEVEL_EN_PROCESO         = 'en_proceso';          // 65-76
    const LEVEL_INSUFICIENTE       = 'insuficiente';        // <65

    const PERFORMANCE_LEVELS = [
        self::LEVEL_DESTACADO         => ['min' => 89, 'max' => 100, 'label' => 'Destacado',          'color' => '#22c55e'],
        self::LEVEL_LOGRO_EVIDENCIADO => ['min' => 77, 'max' => 88,  'label' => 'Logro Evidenciado',  'color' => '#3b82f6'],
        self::LEVEL_EN_PROCESO        => ['min' => 65, 'max' => 76,  'label' => 'En Proceso',         'color' => '#f59e0b'],
        self::LEVEL_INSUFICIENTE      => ['min' => 0,  'max' => 64,  'label' => 'Insuficiente',       'color' => '#ef4444'],
    ];

    // ── Boot ────────────────────────────────────────────────

    protected static function booted()
    {
        // Auto-calcular nivel de desempeño al guardar
        static::saving(function ($grade) {
            if ($grade->score !== null) {
                $grade->performance_level = self::calculatePerformanceLevel($grade->score);
            }
        });
    }

    // ── Relaciones ──────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function sectionSubject(): BelongsTo
    {
        return $this->belongsTo(SectionSubject::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeByPeriod($query, int $periodId)
    {
        return $query->where('evaluation_period_id', $periodId);
    }

    public function scopeBySubject($query, int $sectionSubjectId)
    {
        return $query->where('section_subject_id', $sectionSubjectId);
    }

    public function scopeRegular($query)
    {
        return $query->where('is_recovery', false)->where('is_extraordinary', false);
    }

    // ── Helpers estáticos ───────────────────────────────────

    /**
     * Calcula el nivel de desempeño MINERD basado en la puntuación.
     */
    public static function calculatePerformanceLevel(float $score): string
    {
        return match (true) {
            $score >= 89 => self::LEVEL_DESTACADO,
            $score >= 77 => self::LEVEL_LOGRO_EVIDENCIADO,
            $score >= 65 => self::LEVEL_EN_PROCESO,
            default      => self::LEVEL_INSUFICIENTE,
        };
    }

    /**
     * Obtener el label legible del nivel de desempeño.
     */
    public function getPerformanceLabelAttribute(): string
    {
        return self::PERFORMANCE_LEVELS[$this->performance_level]['label'] ?? 'Sin evaluar';
    }

    /**
     * Obtener el color del nivel de desempeño.
     */
    public function getPerformanceColorAttribute(): string
    {
        return self::PERFORMANCE_LEVELS[$this->performance_level]['color'] ?? '#9ca3af';
    }

    /**
     * Verificar si la nota aprueba según el grado del estudiante.
     */
    public function isPassing(): bool
    {
        if ($this->score === null) return false;

        $minScore = $this->student?->gradeLevel?->min_passing_score ?? 70;
        return $this->score >= $minScore;
    }
}
