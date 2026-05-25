<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'evaluation_period_id',
        'section_id',
        'academic_year_id',
        'days_attended',
        'days_absent',
        'days_late',
        'total_school_days',
        'teacher_observations',
        'teacher_comments',
        'counselor_observations',
        'counselor_comments',
        'conduct',
        'conduct_grade',
        'generated_at',
        'delivered_at',
        'parent_signature',
        'pdf_path',
    ];

    protected $casts = [
        'days_attended'    => 'integer',
        'days_absent'      => 'integer',
        'days_late'        => 'integer',
        'total_school_days'=> 'integer',
        'parent_signature' => 'boolean',
        'generated_at'     => 'datetime',
        'delivered_at'     => 'datetime',
    ];

    const CONDUCT_OPTIONS = [
        'excelente'        => 'Excelente',
        'bueno'            => 'Bueno',
        'satisfactorio'    => 'Satisfactorio',
        'necesita_mejorar' => 'Necesita Mejorar',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    // ── Helpers ─────────────────────────────────────────────

    /**
     * Obtener todas las calificaciones del estudiante para este período.
     */
    public function getGrades()
    {
        return StudentGrade::where('student_id', $this->student_id)
            ->where('evaluation_period_id', $this->evaluation_period_id)
            ->with('sectionSubject.subject')
            ->get();
    }

    /**
     * Porcentaje de asistencia.
     */
    public function getAttendancePercentageAttribute(): float
    {
        if ($this->total_school_days === 0) return 0;
        return round(($this->days_attended / $this->total_school_days) * 100, 1);
    }

    /**
     * Promedio general del período.
     */
    public function getAverageScoreAttribute(): ?float
    {
        $grades = $this->getGrades();
        $scored = $grades->whereNotNull('score');

        if ($scored->isEmpty()) return null;

        return round($scored->avg('score'), 2);
    }

    /**
     * ¿El boletín ya fue generado?
     */
    public function isGenerated(): bool
    {
        return $this->generated_at !== null;
    }

    /**
     * ¿El boletín ya fue entregado?
     */
    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }

    /**
     * Label de conducta para display.
     */
    public function getConductLabelAttribute(): string
    {
        return self::CONDUCT_OPTIONS[$this->conduct] ?? 'No evaluado';
    }
}
