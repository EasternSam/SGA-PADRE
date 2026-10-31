<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Asistencia de estudiantes de hoy o, si aún no hay registros, del último día
 * registrado (indicado explícitamente para no confundirlo con hoy).
 */
final class AttendanceTodayWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $date = DB::table('attendances')->whereDate('attendance_date', '<=', today())->max('attendance_date');

        if ($date === null) {
            return ['date' => null, 'dateLabel' => null, 'isToday' => false, 'present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => null];
        }

        $counts = DB::table('attendances')
            ->whereDate('attendance_date', $date)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $present = (int) ($counts['Presente'] ?? 0);
        $late = (int) ($counts['Tardanza'] ?? 0);
        $absent = (int) ($counts['Ausente'] ?? 0);
        $total = (int) $counts->sum();
        $day = Carbon::parse($date);

        return [
            'date' => $day->toDateString(),
            'dateLabel' => $day->locale('es')->isoFormat('dddd D [de] MMMM'),
            'isToday' => $day->isToday(),
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'total' => $total,
            // Tardanza cuenta como asistencia.
            'rate' => $total > 0 ? round(($present + $late) / $total * 100, 1) : null,
        ];
    }
}
