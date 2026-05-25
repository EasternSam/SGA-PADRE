<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';

    protected $fillable = [
        'academic_year_id', 'date', 'type', 'name', 'description', 'affects_attendance',
    ];

    protected $casts = [
        'date' => 'date',
        'affects_attendance' => 'boolean',
    ];

    const TYPES = [
        'school_day'  => 'Día Lectivo',
        'holiday'     => 'Feriado',
        'teacher_day' => '‍Día de Docentes',
        'exam_day'    => 'Período de Exámenes',
        'event'       => 'Evento Escolar',
        'vacation'    => 'Vacaciones',
        'makeup_day'  => 'Día de Recuperación',
    ];

    const TYPE_COLORS = [
        'school_day'  => '#3b82f6',
        'holiday'     => '#ef4444',
        'teacher_day' => '#8b5cf6',
        'exam_day'    => '#f59e0b',
        'event'       => '#10b981',
        'vacation'    => '#06b6d4',
        'makeup_day'  => '#f97316',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Count school days between two dates for a given academic year.
     */
    public static function schoolDaysBetween(int $yearId, string $from, string $to): int
    {
        return self::where('academic_year_id', $yearId)
            ->where('type', 'school_day')
            ->whereBetween('date', [$from, $to])
            ->count();
    }
}
