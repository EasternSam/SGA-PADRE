import KpisWidget from './KpisWidget';
import EnrollmentTrendWidget from './EnrollmentTrendWidget';
import WeeklyWorkloadWidget from './WeeklyWorkloadWidget';
import StudentDistributionWidget from './StudentDistributionWidget';
import TodayScheduleWidget from './TodayScheduleWidget';
import AttendanceTodayWidget from './AttendanceTodayWidget';
import AdmissionsPipelineWidget from './AdmissionsPipelineWidget';
import FinanceTodayWidget from './FinanceTodayWidget';
import ReceivablesWidget from './ReceivablesWidget';
import QuickActionsWidget from './QuickActionsWidget';
import OperationsHubWidget from './OperationsHubWidget';
import CalendarWidget from './CalendarWidget';
import RecentActivityWidget from './RecentActivityWidget';

/**
 * Componente de cada widget del catálogo (app/Services/Dashboard/DashboardWidgetCatalog.php).
 * `bare`: el widget dibuja sus propias tarjetas y no va dentro del contenedor estándar.
 */
export const WIDGETS = {
    kpis: { component: KpisWidget, bare: true },
    enrollment_trend: { component: EnrollmentTrendWidget },
    weekly_workload: { component: WeeklyWorkloadWidget },
    student_distribution: { component: StudentDistributionWidget },
    today_schedule: { component: TodayScheduleWidget },
    attendance_today: { component: AttendanceTodayWidget },
    admissions_pipeline: { component: AdmissionsPipelineWidget },
    finance_today: { component: FinanceTodayWidget },
    receivables: { component: ReceivablesWidget },
    quick_actions: { component: QuickActionsWidget },
    operations_hub: { component: OperationsHubWidget },
    calendar: { component: CalendarWidget },
    recent_activity: { component: RecentActivityWidget },
};
