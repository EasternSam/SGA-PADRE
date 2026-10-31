<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Student;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * Estudiantes agrupados por programa (nombre del curso sin prefijo "Curso de" ni
 * numeración de grupo). Devuelve los 4 programas principales y el resto agregado.
 */
final class StudentDistributionWidget implements WidgetDataProvider
{
    private const TOP = 4;

    public function data(User $user): array
    {
        $programs = Student::excludingWalkIn()
            ->leftJoin('courses', 'students.course_id', '=', 'courses.id')
            ->pluck('courses.name')
            ->countBy(fn (?string $course) => $this->programOf($course))
            ->sortDesc();

        $total = $programs->sum();
        $items = $programs->take(self::TOP)
            ->map(fn (int $count, string $name) => ['name' => $name, 'count' => $count])
            ->values();

        $others = $total - $items->sum('count');
        if ($others > 0) {
            $items->push(['name' => 'Otros programas', 'count' => $others, 'isOther' => true]);
        }

        return [
            'total' => $total,
            'items' => $items->map(fn (array $item) => $item + [
                'percentage' => $total > 0 ? round($item['count'] / $total * 100, 1) : 0,
            ])->all(),
            'href' => DashboardLinks::url($user, 'students'),
        ];
    }

    private function programOf(?string $course): string
    {
        $name = trim(preg_replace(['/^Curso de\s+/iu', '/\s+\d+.*$/u'], '', (string) $course));

        return $name !== '' ? $name : 'Sin programa asignado';
    }
}
