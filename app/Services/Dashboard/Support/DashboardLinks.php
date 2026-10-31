<?php

namespace App\Services\Dashboard\Support;

use App\Models\User;

/**
 * Enlaces del dashboard con los mismos roles y features que sus rutas en
 * routes/admin.php. Un enlace solo se entrega si el usuario puede abrirlo.
 */
final class DashboardLinks
{
    /** @var array<string, array{route: string, roles: list<string>, features: list<string>}> */
    private const LINKS = [
        'students' => ['route' => 'admin.students.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic']],
        'admissions' => ['route' => 'admin.admissions.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic']],
        'teachers' => ['route' => 'admin.teachers.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic']],
        'calendar' => ['route' => 'admin.calendar.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic']],
        'courses' => ['route' => 'admin.courses.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic_courses']],
        'careers' => ['route' => 'admin.careers.index', 'roles' => ['Admin', 'Registro'], 'features' => ['academic_careers']],
        'classrooms' => ['route' => 'admin.classrooms.index', 'roles' => ['Admin'], 'features' => ['academic']],
        'pos' => ['route' => 'admin.pos', 'roles' => ['Admin', 'Contabilidad', 'Caja'], 'features' => ['finance']],
        'finance' => ['route' => 'admin.finance.dashboard', 'roles' => ['Admin', 'Contabilidad', 'Caja'], 'features' => ['finance']],
        'ledger' => ['route' => 'admin.finance.ledger', 'roles' => ['Admin', 'Contabilidad'], 'features' => ['finance']],
        'expenses' => ['route' => 'admin.finance.expenses', 'roles' => ['Admin', 'Contabilidad'], 'features' => ['finance']],
        'hr_attendances' => ['route' => 'admin.hr.attendances', 'roles' => ['Admin', 'Contabilidad'], 'features' => []],
        'reports' => ['route' => 'reports.index', 'roles' => ['Admin', 'Registro', 'Contabilidad'], 'features' => ['reports_basic']],
        'activity_logs' => ['route' => 'admin.activity-logs.index', 'roles' => ['Admin'], 'features' => []],
    ];

    /** URL relativa del enlace o null si el usuario no tiene acceso. */
    public static function url(User $user, string $key): ?string
    {
        $link = self::LINKS[$key] ?? null;

        if ($link === null || ! $user->hasAnyRole($link['roles'])) {
            return null;
        }

        foreach ($link['features'] as $feature) {
            if (! FeatureGate::enabled($feature)) {
                return null;
            }
        }

        return route($link['route'], [], false);
    }
}
