<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Payment;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;
use App\Services\Dashboard\Support\Weekday;
use Carbon\CarbonPeriod;

/**
 * Cobros registrados hoy (pagos en estado Pagado creados hoy), desglose por método
 * y serie de los últimos 7 días. Si hoy no hay cobros se informa el último.
 */
final class FinanceTodayWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $paidToday = Payment::where('status', 'Pagado')->whereDate('created_at', today());

        $byMethod = (clone $paidToday)
            ->selectRaw('gateway, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['method' => $row->gateway ?: 'Sin método', 'count' => (int) $row->count, 'total' => (float) $row->total])
            ->all();

        $start = today()->subDays(6);
        $daily = Payment::where('status', 'Pagado')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $trend = [];
        foreach (CarbonPeriod::create($start, today()) as $day) {
            $trend[] = [
                'date' => $day->toDateString(),
                'label' => Weekday::SHORT[Weekday::fromDayOfWeek($day->dayOfWeek)] . ' ' . $day->day,
                'total' => (float) ($daily[$day->toDateString()] ?? 0),
            ];
        }

        $lastPayment = Payment::where('status', 'Pagado')->latest()->value('created_at');

        return [
            'total' => (float) (clone $paidToday)->sum('amount'),
            'count' => (clone $paidToday)->count(),
            'byMethod' => $byMethod,
            'trend' => $trend,
            'lastPaymentAt' => $lastPayment?->locale('es')->isoFormat('D [de] MMMM, h:mm a'),
            'href' => DashboardLinks::url($user, 'pos'),
        ];
    }
}
