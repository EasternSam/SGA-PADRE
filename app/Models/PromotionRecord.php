<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRecord extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'grade_level_id', 'section_id',
        'result', 'final_average', 'observations', 'decision_date',
    ];

    protected $casts = [
        'final_average' => 'decimal:2',
        'decision_date' => 'date',
    ];

    const RESULTS = [
        'promoted'    => '✅ Promovido',
        'retained'    => '🔄 Repitente',
        'transferred' => '🔀 Trasladado',
        'withdrawn'   => '❌ Retirado',
        'graduated'   => '🎓 Graduado',
    ];

    const RESULT_COLORS = [
        'promoted'    => '#10b981',
        'retained'    => '#ef4444',
        'transferred' => '#8b5cf6',
        'withdrawn'   => '#6b7280',
        'graduated'   => '#f59e0b',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
