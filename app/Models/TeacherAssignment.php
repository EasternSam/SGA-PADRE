<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'academic_year_id', 'teacher_id', 'section_id', 'subject_id', 'is_homeroom',
    ];

    protected $casts = [
        'is_homeroom' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get homeroom teacher for a section.
     */
    public static function homeroomFor(int $sectionId, ?int $yearId = null): ?self
    {
        return self::where('section_id', $sectionId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->where('is_homeroom', true)
            ->with('teacher')
            ->first();
    }

    /**
     * Teacher's total class load for a year.
     */
    public static function teacherLoad(int $teacherId, int $yearId): int
    {
        return self::where('teacher_id', $teacherId)
            ->where('academic_year_id', $yearId)
            ->count();
    }
}
