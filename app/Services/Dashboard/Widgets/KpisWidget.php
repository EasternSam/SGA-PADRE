<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;
use App\Services\Dashboard\Support\FeatureGate;

/**
 * Indicadores según el rol: académicos para Admin/Registro y de caja/cobros para
 * Admin/Contabilidad/Caja. Cada cifra sale de la BD; si es 0 se muestra 0.
 */
final class KpisWidget implements WidgetDataProvider
{
    public function data(User $user): array
    {
        $items = [];

        if (FeatureGate::enabled('academic') && $user->hasAnyRole(['Admin', 'Registro'])) {
            $items[] = $this->item('students', 'Estudiantes activos', Student::excludingWalkIn()->whereIn('status', ['Activo', 'Activa'])->count(), 'number', DashboardLinks::url($user, 'students'));
            $items[] = $this->item('teachers', 'Docentes', User::whereHas('roles', fn ($q) => $q->where('name', 'Profesor'))->count(), 'number', DashboardLinks::url($user, 'teachers'));
            $items[] = $this->item('courses', 'Asignaturas', Course::count(), 'number', DashboardLinks::url($user, 'courses'));
            $items[] = $this->item('sections', 'Secciones', CourseSchedule::count(), 'number', DashboardLinks::url($user, 'courses'));
        }

        if (FeatureGate::enabled('finance')) {
            $paid = fn () => Payment::where('status', 'Pagado');

            if ($user->hasAnyRole(['Contabilidad', 'Caja'])) {
                $items[] = $this->item('collected_today', 'Cobrado hoy', (float) $paid()->whereDate('created_at', today())->sum('amount'), 'currency', DashboardLinks::url($user, 'pos'));
                $items[] = $this->item('payments_today', 'Cobros de hoy', $paid()->whereDate('created_at', today())->count(), 'number', DashboardLinks::url($user, 'finance'));
            }

            if ($user->hasAnyRole(['Admin', 'Contabilidad', 'Caja'])) {
                $items[] = $this->item('collected_month', 'Cobrado este mes', (float) $paid()->whereBetween('created_at', [now()->startOfMonth(), now()])->sum('amount'), 'currency', DashboardLinks::url($user, 'finance'));
            }

            if ($user->hasAnyRole(['Admin', 'Contabilidad'])) {
                $items[] = $this->item('receivable', 'Por cobrar', (float) Payment::where('status', 'Pendiente')->sum('amount'), 'currency', DashboardLinks::url($user, 'ledger'));
            }
        }

        return ['items' => $items];
    }

    /** @return array{key: string, label: string, value: int|float, format: string, href: ?string} */
    private function item(string $key, string $label, int|float $value, string $format, ?string $href): array
    {
        return compact('key', 'label', 'value', 'format', 'href');
    }
}
