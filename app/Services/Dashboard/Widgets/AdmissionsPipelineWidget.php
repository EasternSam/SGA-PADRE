<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Admission;
use App\Models\Student;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * Embudo de admisiones: solicitudes por estado, prospectos y últimas pendientes.
 */
final class AdmissionsPipelineWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $byStatus = Admission::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'rejected' => (int) ($byStatus['rejected'] ?? 0),
            'prospects' => Student::excludingWalkIn()->where('status', 'Prospecto')->count(),
            'latest' => Admission::query()
                ->where('status', 'pending')
                ->latest()
                ->limit(4)
                ->get(['id', 'first_name', 'last_name', 'created_at'])
                ->map(fn (Admission $a) => [
                    'id' => $a->id,
                    'name' => trim("{$a->first_name} {$a->last_name}"),
                    'time' => $a->created_at?->locale('es')->diffForHumans(),
                ])->all(),
            'href' => DashboardLinks::url($user, 'admissions'),
        ];
    }
}
