<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\ActivityHumanizer;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * Últimas entradas del registro de auditoría, en lenguaje legible. Solo Admin.
 */
final class RecentActivityWidget implements WidgetDataProvider
{
    private const LIMIT = 6;

    public function data(User $user): array
    {
        return [
            'items' => ActivityLog::with('user:id,name')
                ->latest('id')
                ->limit(self::LIMIT)
                ->get(['id', 'user_id', 'action', 'description', 'created_at'])
                ->map(fn (ActivityLog $log) => ActivityHumanizer::describe($log->description, $log->action) + [
                    'id' => $log->id,
                    'user' => $log->user?->name ?? 'Sistema',
                    'time' => $log->created_at?->locale('es')->diffForHumans(),
                ])->all(),
            'href' => DashboardLinks::url($user, 'activity_logs'),
        ];
    }
}
