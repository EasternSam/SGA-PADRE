<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use Illuminate\Support\Facades\DB;

/**
 * Inscripciones por mes del año en curso (con comparativo del año anterior si
 * existe) y por período de evaluación registrado.
 */
final class EnrollmentTrendWidget implements WidgetDataProvider
{
    private const MONTHS = [
        1 => ['Ene', 'Enero'], 2 => ['Feb', 'Febrero'], 3 => ['Mar', 'Marzo'], 4 => ['Abr', 'Abril'],
        5 => ['May', 'Mayo'], 6 => ['Jun', 'Junio'], 7 => ['Jul', 'Julio'], 8 => ['Ago', 'Agosto'],
        9 => ['Sep', 'Septiembre'], 10 => ['Oct', 'Octubre'], 11 => ['Nov', 'Noviembre'], 12 => ['Dic', 'Diciembre'],
    ];

    public function data(User $user): array
    {
        $year = now()->year;
        $current = $this->countsByMonth($year);
        $previous = $this->countsByMonth($year - 1);

        $monthly = [];
        $cumulative = 0;
        $peak = null;

        for ($m = 1; $m <= now()->month; $m++) {
            $count = $current[$m] ?? 0;
            $cumulative += $count;

            if ($count > 0 && ($peak === null || $count > $peak['count'])) {
                $peak = ['label' => self::MONTHS[$m][1], 'count' => $count];
            }

            $monthly[] = [
                'label' => self::MONTHS[$m][0],
                'fullLabel' => self::MONTHS[$m][1] . " {$year}",
                'count' => $count,
                'previousCount' => $previous[$m] ?? 0,
                'cumulative' => $cumulative,
            ];
        }

        $totalYear = array_sum($current);
        $totalPrevious = array_sum($previous);

        return [
            'year' => $year,
            'monthly' => $monthly,
            'hasPreviousYear' => $totalPrevious > 0,
            'periods' => $this->periods(),
            'totalYear' => $totalYear,
            'peak' => $peak,
            'growthRate' => $totalPrevious > 0 ? round((($totalYear - $totalPrevious) / $totalPrevious) * 100, 1) : null,
        ];
    }

    /** @return array<int, int> */
    private function countsByMonth(int $year): array
    {
        return DB::table('enrollments')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /** @return list<array{label: string, fullLabel: string, count: int, active: bool}> */
    private function periods(): array
    {
        return DB::table('evaluation_periods')
            ->join('academic_years', 'evaluation_periods.academic_year_id', '=', 'academic_years.id')
            ->orderBy('evaluation_periods.start_date')
            ->get([
                'evaluation_periods.name', 'evaluation_periods.start_date', 'evaluation_periods.end_date',
                'evaluation_periods.status', 'academic_years.name as year_name',
            ])
            ->map(fn ($period) => [
                'label' => $period->name,
                'fullLabel' => "{$period->name} ({$period->year_name})",
                'count' => DB::table('enrollments')
                    ->whereBetween('created_at', ["{$period->start_date} 00:00:00", "{$period->end_date} 23:59:59"])
                    ->count(),
                'active' => $period->status === 'active',
            ])
            ->all();
    }
}
