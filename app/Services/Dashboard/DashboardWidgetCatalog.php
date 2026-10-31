<?php

namespace App\Services\Dashboard;

use App\Services\Dashboard\Support\FeatureGate;
use App\Services\Dashboard\Widgets;

/**
 * Catálogo de widgets del dashboard administrativo y diseños de fábrica por rol.
 *
 * Para añadir un widget: declarar su definición aquí, crear su provider en
 * Widgets/ y su componente en resources/js/components/dashboard/widgets/.
 */
final class DashboardWidgetCatalog
{
    private const ACADEMIC_STAFF = ['Admin', 'Registro'];

    private const FINANCE_STAFF = ['Admin', 'Contabilidad', 'Caja'];

    /** Registry de producción: catálogo real + features del plan activo. */
    public static function registry(): WidgetRegistry
    {
        return new WidgetRegistry(
            self::definitions(),
            self::roleDefaults(),
            fn (string $feature) => FeatureGate::enabled($feature),
        );
    }

    /** @return list<WidgetDefinition> */
    public static function definitions(): array
    {
        $all = WidgetRegistry::DASHBOARD_ROLES;

        return [
            new WidgetDefinition('kpis', 'Indicadores clave', 'Cifras principales de la institución según tu rol.', 'general', $all, ['lg', 'xl'], 'xl', null, Widgets\KpisWidget::class),
            new WidgetDefinition('enrollment_trend', 'Tendencia de inscripciones', 'Inscripciones por mes del año en curso y por período.', 'academico', self::ACADEMIC_STAFF, ['md', 'lg', 'xl'], 'md', 'academic', Widgets\EnrollmentTrendWidget::class),
            new WidgetDefinition('weekly_workload', 'Carga horaria semanal', 'Bloques de clase por día, turno y tipo de espacio.', 'academico', self::ACADEMIC_STAFF, ['md', 'lg', 'xl'], 'md', 'academic', Widgets\WeeklyWorkloadWidget::class),
            new WidgetDefinition('student_distribution', 'Distribución de estudiantes', 'Estudiantes por programa de estudio.', 'academico', self::ACADEMIC_STAFF, ['sm', 'md'], 'sm', 'academic', Widgets\StudentDistributionWidget::class),
            new WidgetDefinition('today_schedule', 'Clases de hoy', 'Secciones programadas para el día actual.', 'academico', self::ACADEMIC_STAFF, ['sm', 'md'], 'sm', 'academic', Widgets\TodayScheduleWidget::class),
            new WidgetDefinition('attendance_today', 'Asistencia del día', 'Presentes, ausentes y tardanzas del último día registrado.', 'academico', self::ACADEMIC_STAFF, ['sm', 'md'], 'sm', 'academic', Widgets\AttendanceTodayWidget::class),
            new WidgetDefinition('admissions_pipeline', 'Admisiones', 'Solicitudes pendientes, aprobadas y rechazadas.', 'academico', self::ACADEMIC_STAFF, ['sm', 'md'], 'sm', 'academic', Widgets\AdmissionsPipelineWidget::class),
            new WidgetDefinition('finance_today', 'Cobros de hoy', 'Total cobrado hoy, por método y tendencia de 7 días.', 'finanzas', self::FINANCE_STAFF, ['sm', 'md'], 'sm', 'finance', Widgets\FinanceTodayWidget::class),
            new WidgetDefinition('receivables', 'Cuentas por cobrar', 'Saldo pendiente, vencido y antigüedad de la deuda.', 'finanzas', ['Admin', 'Contabilidad'], ['md', 'lg'], 'md', 'finance', Widgets\ReceivablesWidget::class),
            new WidgetDefinition('quick_actions', 'Accesos rápidos', 'Atajos a las tareas más frecuentes de tu rol.', 'general', $all, ['sm', 'md'], 'sm', null, Widgets\QuickActionsWidget::class),
            new WidgetDefinition('operations_hub', 'Centro de operaciones', 'Módulos del sistema con sus cifras actuales.', 'general', $all, ['lg', 'xl'], 'xl', null, Widgets\OperationsHubWidget::class),
            new WidgetDefinition('calendar', 'Calendario', 'Calendario del mes actual.', 'general', $all, ['sm', 'md'], 'sm', null, Widgets\CalendarWidget::class),
            new WidgetDefinition('recent_activity', 'Actividad reciente', 'Últimas acciones registradas en el sistema.', 'general', ['Admin'], ['sm', 'md'], 'sm', null, Widgets\RecentActivityWidget::class),
        ];
    }

    /** @return array<string, list<array{key: string, size: string}>> */
    public static function roleDefaults(): array
    {
        return [
            'Admin' => [
                ['key' => 'kpis', 'size' => 'xl'],
                ['key' => 'enrollment_trend', 'size' => 'md'],
                ['key' => 'weekly_workload', 'size' => 'md'],
                ['key' => 'student_distribution', 'size' => 'sm'],
                ['key' => 'finance_today', 'size' => 'sm'],
                ['key' => 'recent_activity', 'size' => 'sm'],
                ['key' => 'operations_hub', 'size' => 'xl'],
            ],
            'Registro' => [
                ['key' => 'kpis', 'size' => 'xl'],
                ['key' => 'today_schedule', 'size' => 'sm'],
                ['key' => 'attendance_today', 'size' => 'sm'],
                ['key' => 'admissions_pipeline', 'size' => 'sm'],
                ['key' => 'enrollment_trend', 'size' => 'md'],
                ['key' => 'weekly_workload', 'size' => 'md'],
                ['key' => 'quick_actions', 'size' => 'sm'],
                ['key' => 'calendar', 'size' => 'sm'],
            ],
            'Contabilidad' => [
                ['key' => 'kpis', 'size' => 'xl'],
                ['key' => 'finance_today', 'size' => 'sm'],
                ['key' => 'receivables', 'size' => 'lg'],
                ['key' => 'quick_actions', 'size' => 'sm'],
                ['key' => 'operations_hub', 'size' => 'lg'],
            ],
            'Caja' => [
                ['key' => 'kpis', 'size' => 'xl'],
                ['key' => 'finance_today', 'size' => 'md'],
                ['key' => 'quick_actions', 'size' => 'md'],
                ['key' => 'calendar', 'size' => 'sm'],
            ],
        ];
    }
}
