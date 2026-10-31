<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Payment;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\AgingBucket;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * Cuentas por cobrar: pagos en estado Pendiente, su antigüedad respecto a la fecha
 * de vencimiento y los estudiantes con mayor saldo vencido.
 */
final class ReceivablesWidget implements WidgetDataProvider
{
    private const TOP_DEBTORS = 5;

    public function data(User $user): array
    {
        $pending = Payment::where('status', 'Pendiente')
            ->with('student:id,first_name,last_name')
            ->get(['id', 'student_id', 'amount', 'due_date']);

        $buckets = array_fill_keys(array_keys(AgingBucket::LABELS), ['total' => 0.0, 'count' => 0]);
        $debtors = [];

        foreach ($pending as $payment) {
            $daysOverdue = $payment->due_date ? (int) $payment->due_date->startOfDay()->diffInDays(today(), false) : null;
            $bucket = AgingBucket::for($daysOverdue);
            $buckets[$bucket]['total'] += (float) $payment->amount;
            $buckets[$bucket]['count']++;

            if ($bucket !== 'current' && $payment->student) {
                $id = $payment->student_id;
                $debtors[$id] ??= ['name' => trim("{$payment->student->first_name} {$payment->student->last_name}"), 'total' => 0.0, 'count' => 0, 'maxDays' => 0];
                $debtors[$id]['total'] += (float) $payment->amount;
                $debtors[$id]['count']++;
                $debtors[$id]['maxDays'] = max($debtors[$id]['maxDays'], $daysOverdue);
            }
        }

        usort($debtors, fn ($a, $b) => $b['total'] <=> $a['total']);
        $overdue = array_diff_key($buckets, ['current' => true]);

        return [
            'total' => (float) $pending->sum('amount'),
            'count' => $pending->count(),
            'overdueTotal' => array_sum(array_column($overdue, 'total')),
            'overdueCount' => array_sum(array_column($overdue, 'count')),
            'aging' => array_map(
                fn (string $key) => ['key' => $key, 'label' => AgingBucket::LABELS[$key]] + $buckets[$key],
                array_keys($buckets),
            ),
            'topDebtors' => array_slice($debtors, 0, self::TOP_DEBTORS),
            'href' => DashboardLinks::url($user, 'ledger'),
        ];
    }
}
