<?php

namespace App\Services\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\StudentRequest;
use App\Services\Dashboard\Support\Weekday;
use Illuminate\Support\Collection;

/**
 * Servicio de datos y métricas para el Portal del Estudiante.
 * Centraliza cálculos de asistencia, horario semanal, progreso de pensum y finanzas.
 */
class StudentDashboardService
{
    /**
     * Formatea y ordena cronológicamente los días de clase (Lunes a Sábado).
     */
    public function formatDays(mixed $daysOfWeek): string
    {
        $decoded = Weekday::decode($daysOfWeek);
        if (empty($decoded)) {
            return 'Por coordinar';
        }

        $order = array_flip(Weekday::SCHOOL_DAYS);
        usort($decoded, function ($a, $b) use ($order) {
            $posA = $order[$a] ?? 99;
            $posB = $order[$b] ?? 99;
            return $posA <=> $posB;
        });

        return implode(', ', $decoded);
    }

    /**
     * Calcula estadísticas reales de asistencia (total, presentes, tardanzas, ausencias, % y badge).
     */
    public function calculateAttendanceStats(array|Collection $attendances): array
    {
        $total = count($attendances);
        if ($total === 0) {
            return [
                'total' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'excused' => 0,
                'percentage' => 100,
                'rate_badge' => 'Sin registros',
            ];
        }

        $present = 0;
        $late = 0;
        $absent = 0;
        $excused = 0;

        foreach ($attendances as $att) {
            $st = is_array($att) ? ($att['status'] ?? '') : ($att->status ?? '');
            $lower = mb_strtolower(trim((string)$st));

            if (str_contains($lower, 'present')) {
                $present++;
            } elseif (str_contains($lower, 'tard')) {
                $late++;
            } elseif (str_contains($lower, 'just')) {
                $excused++;
            } elseif (str_contains($lower, 'ausen')) {
                $absent++;
            } else {
                $present++;
            }
        }

        $effectivePresent = $present + ($late * 0.5) + ($excused * 1.0);
        $percentage = min(100, max(0, (int) round(($effectivePresent / $total) * 100)));

        $rateBadge = match (true) {
            $percentage >= 90 => 'Excelente',
            $percentage >= 80 => 'Bueno',
            $percentage >= 70 => 'Regular',
            default => 'Atención',
        };

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'percentage' => $percentage,
            'rate_badge' => $rateBadge,
        ];
    }

    /**
     * Agrupa las inscripciones activas en una matriz semanal por día (Lunes a Sábado).
     */
    public function buildWeeklySchedule(array|Collection $enrollments): array
    {
        $scheduleByDay = [
            'Lunes' => [],
            'Martes' => [],
            'Miércoles' => [],
            'Jueves' => [],
            'Viernes' => [],
            'Sábado' => [],
        ];

        foreach ($enrollments as $enr) {
            $days = $enr['days_of_week'] ?? [];
            if (!is_array($days)) {
                $days = Weekday::decode($days);
            }

            foreach ($days as $rawDay) {
                $canonical = Weekday::normalize($rawDay);
                if ($canonical && isset($scheduleByDay[$canonical])) {
                    $scheduleByDay[$canonical][] = [
                        'id' => $enr['id'] ?? null,
                        'subject_name' => $enr['subject_name'] ?? 'Asignatura',
                        'subject_code' => $enr['subject_code'] ?? 'ASG',
                        'credits' => $enr['credits'] ?? 3,
                        'teacher' => $enr['teacher'] ?? 'Docente',
                        'classroom' => $enr['classroom'] ?? 'Aula',
                        'start_time' => $enr['start_time'] ?? '08:00',
                        'end_time' => $enr['end_time'] ?? '10:00',
                    ];
                }
            }
        }

        foreach ($scheduleByDay as $day => &$slots) {
            usort($slots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
        }
        unset($slots);

        return $scheduleByDay;
    }

    /**
     * Calcula avance de créditos con porcentajes y créditos restantes.
     */
    public function computeProgress(int $totalCredits, int $approvedCredits, int $inProgressCredits): array
    {
        $total = max(1, $totalCredits);
        $percentage = min(100, max(0, (int) round(($approvedCredits / $total) * 100)));
        $pending = max(0, $totalCredits - $approvedCredits - $inProgressCredits);

        return [
            'total_credits' => $totalCredits,
            'approved_credits' => $approvedCredits,
            'in_progress_credits' => $inProgressCredits,
            'percentage' => $percentage,
            'pending_credits' => $pending,
        ];
    }
}
