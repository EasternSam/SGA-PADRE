<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\Weekday;
use Illuminate\Support\Facades\DB;

/**
 * Bloques de clase por día lectivo, separados por turno (según la hora de inicio)
 * y por tipo de espacio (laboratorio si el nombre del aula contiene "Lab").
 */
final class WeeklyWorkloadWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $days = [];
        foreach (Weekday::SCHOOL_DAYS as $name) {
            $days[$name] = ['short' => Weekday::SHORT[$name], 'name' => $name, 'mat' => 0, 'vesp' => 0, 'noct' => 0, 'aulas' => 0, 'labs' => 0];
        }

        // Mapa id → nombre: las secciones sin aula asignada cuentan como aula teórica.
        $classrooms = DB::table('classrooms')->pluck('name', 'id');

        $schedules = DB::table('course_schedules')
            ->whereNull('deleted_at')
            ->get(['days_of_week', 'start_time', 'classroom_id']);

        foreach ($schedules as $schedule) {
            $shift = $this->shiftOf((string) $schedule->start_time);
            $space = stripos((string) ($classrooms[$schedule->classroom_id] ?? ''), 'lab') !== false ? 'labs' : 'aulas';

            foreach (Weekday::decode($schedule->days_of_week) as $day) {
                if (isset($days[$day])) {
                    $days[$day][$shift]++;
                    $days[$day][$space]++;
                }
            }
        }

        $days = array_values($days);
        $totals = [];
        foreach (['mat', 'vesp', 'noct', 'aulas', 'labs'] as $key) {
            $totals[$key] = array_sum(array_column($days, $key));
        }

        return [
            'days' => $days,
            'totals' => $totals,
            'totalBlocks' => $totals['mat'] + $totals['vesp'] + $totals['noct'],
            'totalSections' => $schedules->count(),
        ];
    }

    private function shiftOf(string $startTime): string
    {
        return match (true) {
            $startTime < '12:00:00' => 'mat',
            $startTime < '18:00:00' => 'vesp',
            default => 'noct',
        };
    }
}
