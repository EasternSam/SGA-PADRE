<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolAlert;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Carbon\Carbon;

class AlertScannerService
{
    /**
     * Scan all active students for alerts.
     */
    public function scanAll(): array
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return ['scanned' => 0, 'alerts' => 0];

        $students = Student::where('status', 'Activo')->get();
        $alertCount = 0;

        foreach ($students as $student) {
            $alertCount += $this->scanAbsenceStreak($student, $activeYear);
            $alertCount += $this->scanLowPerformance($student, $activeYear);
            $alertCount += $this->scanDropoutRisk($student, $activeYear);
        }

        return ['scanned' => $students->count(), 'alerts' => $alertCount];
    }

    /**
     * 3+ consecutive absences.
     */
    public function scanAbsenceStreak(Student $student, AcademicYear $year): int
    {
        $recentAttendance = StudentAttendance::where('student_id', $student->id)
            ->whereDate('date', '>=', now()->subDays(10))
            ->orderBy('date', 'desc')
            ->take(10)
            ->pluck('status')
            ->toArray();

        $streak = 0;
        foreach ($recentAttendance as $status) {
            if ($status === 'absent') {
                $streak++;
            } else {
                break;
            }
        }

        if ($streak >= 3) {
            $existing = SchoolAlert::where('student_id', $student->id)
                ->where('type', 'absence_streak')
                ->where('is_resolved', false)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->first();

            if (!$existing) {
                SchoolAlert::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $year->id,
                    'type'             => 'absence_streak',
                    'severity'         => $streak >= 5 ? 'critical' : 'warning',
                    'title'            => "{$streak} ausencias consecutivas",
                    'description'      => "{$student->full_name} tiene {$streak} ausencias consecutivas recientes.",
                    'metadata'         => ['streak' => $streak, 'last_check' => now()->toDateString()],
                ]);
                return 1;
            }
        }

        return 0;
    }

    /**
     * Average below 70.
     */
    public function scanLowPerformance(Student $student, AcademicYear $year): int
    {
        $grades = StudentGrade::where('student_id', $student->id)
            ->whereHas('evaluationPeriod', fn($q) => $q->where('academic_year_id', $year->id))
            ->whereNotNull('score')
            ->get();

        if ($grades->count() < 3) return 0; // Need at least 3 grades

        $avg = round($grades->avg('score'), 1);

        if ($avg < 70) {
            $existing = SchoolAlert::where('student_id', $student->id)
                ->where('type', 'low_performance')
                ->where('is_resolved', false)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->first();

            if (!$existing) {
                SchoolAlert::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $year->id,
                    'type'             => 'low_performance',
                    'severity'         => $avg < 50 ? 'critical' : 'warning',
                    'title'            => "Promedio bajo: {$avg}",
                    'description'      => "{$student->full_name} tiene un promedio de {$avg}, por debajo del mínimo de 70.",
                    'metadata'         => ['average' => $avg, 'grade_count' => $grades->count()],
                ]);
                return 1;
            }
        }

        return 0;
    }

    /**
     * >20% absences = dropout risk.
     */
    public function scanDropoutRisk(Student $student, AcademicYear $year): int
    {
        $total = StudentAttendance::where('student_id', $student->id)
            ->whereDate('date', '>=', $year->start_date)
            ->count();

        if ($total < 15) return 0; // Need enough data

        $absent = StudentAttendance::where('student_id', $student->id)
            ->whereDate('date', '>=', $year->start_date)
            ->where('status', 'absent')
            ->count();

        $pct = round(($absent / $total) * 100, 1);

        if ($pct > 20) {
            $existing = SchoolAlert::where('student_id', $student->id)
                ->where('type', 'dropout_risk')
                ->where('is_resolved', false)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->first();

            if (!$existing) {
                SchoolAlert::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $year->id,
                    'type'             => 'dropout_risk',
                    'severity'         => $pct > 35 ? 'critical' : 'warning',
                    'title'            => "Riesgo de abandono: {$pct}% ausencias",
                    'description'      => "{$student->full_name} tiene {$pct}% de ausencias ({$absent} de {$total} días).",
                    'metadata'         => ['absence_pct' => $pct, 'absent' => $absent, 'total' => $total],
                ]);
                return 1;
            }
        }

        return 0;
    }
}
