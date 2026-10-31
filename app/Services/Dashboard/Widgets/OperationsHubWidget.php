<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;
use Closure;

/**
 * Módulos del sistema con su cifra principal y dos accesos. Un módulo se muestra
 * solo si el usuario puede abrir al menos uno de sus enlaces; la cifra se calcula
 * únicamente para los módulos visibles.
 */
final class OperationsHubWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $modules = [
            $this->module($user, 'curriculum', 'Gestión curricular', 'book-open', ['courses' => 'Catálogo de cursos', 'careers' => 'Planes de carrera'],
                fn () => ['value' => Course::count(), 'unit' => 'cursos']),
            $this->module($user, 'registry', 'Registro y admisiones', 'graduation-cap', ['students' => 'Directorio de estudiantes', 'admissions' => 'Solicitudes de ingreso'],
                fn () => ['value' => Student::excludingWalkIn()->count(), 'unit' => 'estudiantes']),
            $this->module($user, 'faculty', 'Claustro docente', 'users', ['teachers' => 'Gestión del profesorado', 'hr_attendances' => 'Ponche y asistencia'],
                fn () => ['value' => User::whereHas('roles', fn ($q) => $q->where('name', 'Profesor'))->count(), 'unit' => 'docentes']),
            $this->module($user, 'treasury', 'Tesorería y cobros', 'credit-card', ['pos' => 'Terminal de cobro', 'ledger' => 'Libro mayor', 'finance' => 'Panel financiero'],
                fn () => ['value' => Payment::where('status', 'Pendiente')->count(), 'unit' => 'pagos pendientes']),
        ];

        return ['modules' => array_values(array_filter($modules))];
    }

    /**
     * @param  array<string, string>  $links  clave de DashboardLinks => título (se usan los 2 primeros permitidos)
     * @param  Closure(): array{value: int, unit: string}  $metric
     */
    private function module(User $user, string $key, string $title, string $icon, array $links, Closure $metric): ?array
    {
        $allowed = [];
        foreach ($links as $link => $label) {
            $href = DashboardLinks::url($user, $link);
            if ($href !== null) {
                $allowed[] = ['title' => $label, 'href' => $href];
            }
        }

        if ($allowed === []) {
            return null;
        }

        return ['key' => $key, 'title' => $title, 'icon' => $icon, 'metric' => $metric(), 'links' => array_slice($allowed, 0, 2)];
    }
}
