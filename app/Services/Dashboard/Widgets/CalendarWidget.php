<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * El calendario se dibuja en el cliente; el servidor solo indica si el usuario
 * puede abrir el calendario académico (si no, los días no son navegables).
 */
final class CalendarWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        return ['calendarUrl' => DashboardLinks::url($user, 'calendar')];
    }
}
