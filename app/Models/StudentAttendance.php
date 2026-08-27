<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'date',
        'status',
        'excuse_reason',
        'excuse_document',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Status Labels & Colors ──────────────────────────────

    const STATUS_LABELS = [
        'present'    => 'Presente',
        'absent'     => 'Ausente',
        'late'       => 'Tardanza',
        'excused'    => 'Excusa',
        'permission' => 'Permiso',
    ];

    const STATUS_COLORS = [
        'present'    => 'green',
        'absent'     => 'red',
        'late'       => 'yellow',
        'excused'    => 'blue',
        'permission' => 'purple',
    ];

    const STATUS_ICONS = [
        'present'    => '',
        'absent'     => '',
        'late'       => '',
        'excused'    => '',
        'permission' => '',
    ];

    // ── Accessors ────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        return self::STATUS_ICONS[$this->status] ?? '';
    }

    // ── Relationships ────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeForSection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeAbsences($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    // ── Static Helpers ───────────────────────────────────────

    /**
     * Calcular porcentaje de asistencia para un estudiante en un rango.
     */
    public static function attendancePercentage(int $studentId, string $from, string $to): float
    {
        $total = self::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->count();

        if ($total === 0) return 0;

        $present = self::where('student_id', $studentId)
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['present', 'late', 'excused'])
            ->count();

        return round(($present / $total) * 100, 1);
    }

    /**
     * Contar ausencias consecutivas (alerta temprana).
     */
    public static function consecutiveAbsences(int $studentId): int
    {
        $records = self::where('student_id', $studentId)
            ->orderByDesc('date')
            ->limit(30)
            ->pluck('status');

        $count = 0;
        foreach ($records as $status) {
            if ($status === 'absent') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }
}
