<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSchedule extends Model
{
    protected $fillable = [
        'academic_year_id', 'section_id', 'time_block_id',
        'subject_id', 'teacher_id', 'classroom_name', 'day_of_week', 'notes',
    ];

    const DAYS = [
        'lunes'     => 'Lunes',
        'martes'    => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves'    => 'Jueves',
        'viernes'   => 'Viernes',
    ];

    const DAY_ABBREV = [
        'lunes'     => 'L',
        'martes'    => 'M',
        'miercoles' => 'X',
        'jueves'    => 'J',
        'viernes'   => 'V',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function timeBlock(): BelongsTo
    {
        return $this->belongsTo(TimeBlock::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Detect teacher conflicts (teacher assigned in 2 places at the same time).
     */
    public static function teacherConflicts(int $teacherId, string $day, int $timeBlockId, ?int $excludeId = null): ?self
    {
        return self::where('teacher_id', $teacherId)
            ->where('day_of_week', $day)
            ->where('time_block_id', $timeBlockId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->with(['section.gradeLevel', 'subject'])
            ->first();
    }
}
