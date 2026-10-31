<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;
use App\Services\Dashboard\Support\Weekday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Secciones que tienen clase hoy (según days_of_week y la vigencia de la sección).
 */
final class TodayScheduleWidget implements WidgetDataProvider
{
    private const LIMIT = 8;

    public function data(User $user): array
    {
        $today = Weekday::fromDayOfWeek(now()->dayOfWeek);
        $date = now()->toDateString();
        $classrooms = DB::table('classrooms')->pluck('name', 'id');

        $sections = DB::table('course_schedules')
            ->join('modules', 'course_schedules.module_id', '=', 'modules.id')
            ->join('courses', 'modules.course_id', '=', 'courses.id')
            ->whereNull('course_schedules.deleted_at')
            ->where(fn ($q) => $q->whereNull('course_schedules.start_date')->orWhere('course_schedules.start_date', '<=', $date))
            ->where(fn ($q) => $q->whereNull('course_schedules.end_date')->orWhere('course_schedules.end_date', '>=', $date))
            ->orderBy('course_schedules.start_time')
            ->get([
                'course_schedules.id', 'course_schedules.days_of_week', 'course_schedules.start_time',
                'course_schedules.end_time', 'course_schedules.section_name', 'course_schedules.classroom_id',
                'courses.name as course_name', 'courses.code as course_code', 'modules.name as module_name',
            ])
            ->filter(fn ($s) => in_array($today, Weekday::decode($s->days_of_week), true))
            ->values();

        return [
            'day' => $today,
            'total' => $sections->count(),
            'items' => $sections->take(self::LIMIT)->map(fn ($s) => [
                'id' => $s->id,
                'start' => Carbon::parse($s->start_time)->format('H:i'),
                'end' => Carbon::parse($s->end_time)->format('H:i'),
                'title' => preg_replace('/^Curso de\s+/iu', '', (string) $s->course_name),
                'code' => $s->course_code,
                'module' => $s->module_name,
                'section' => $s->section_name,
                'classroom' => $classrooms[$s->classroom_id] ?? null,
            ])->all(),
            'href' => DashboardLinks::url($user, 'calendar'),
        ];
    }
}
