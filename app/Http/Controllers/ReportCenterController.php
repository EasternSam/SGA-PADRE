<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGrade;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCenterController extends Controller
{
    /**
     * Build the report dataset, KPI summary, and metadata for UI, PDF, and CSV exports.
     */
    protected function buildReportPayload(Request $request, int $limit = 100): array
    {
        $reportType = $request->query('report_type', 'payments');
        $courseId = $request->query('course_id');
        $moduleId = $request->query('module_id');
        $scheduleId = $request->query('schedule_id');
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->endOfMonth()->toDateString());
        $status = $request->query('status', 'all');

        $reportTitles = [
            'payments' => 'Reporte Oficial de Cobros y Recaudación',
            'attendance' => 'Reporte Oficial de Control de Asistencia',
            'grades' => 'Reporte Oficial de Calificaciones y Rendimiento',
            'students' => 'Padrón Oficial Estudiantil',
            'debtors' => 'Reporte de Cuentas por Cobrar y Morosidad',
        ];

        $periodLabel = sprintf(
            'Período: %s al %s · Estado: %s',
            Carbon::parse($dateFrom)->format('d/m/Y'),
            Carbon::parse($dateTo)->format('d/m/Y'),
            $status === 'all' ? 'Todos' : $status
        );

        $reportData = collect();
        $summary = [
            'total_records' => 0,
            'primary_metric' => '0',
            'primary_label' => 'Total',
            'secondary_metric' => '0',
            'secondary_label' => 'Secundario',
        ];

        switch ($reportType) {
            case 'payments':
                $query = Payment::with(['student', 'paymentConcept'])
                    ->whereBetween('created_at', [
                        Carbon::parse($dateFrom)->startOfDay(),
                        Carbon::parse($dateTo)->endOfDay(),
                    ]);

                if ($status && $status !== 'all') {
                    $query->where('status', $status);
                }

                $payments = $query->latest('created_at')->take($limit)->get();
                $totalAmount = $payments->where('status', 'Pagado')->sum('amount');
                $pendingAmount = $payments->whereIn('status', ['Pendiente', 'pending', 'Vencido'])->sum('amount');

                $reportData = $payments->map(fn ($p) => [
                    'id' => $p->id,
                    'date' => $p->created_at?->format('d/m/Y H:i'),
                    'student_code' => $p->student?->student_code ?? sprintf('EST-%04d', $p->student_id),
                    'student_name' => $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : 'Estudiante General',
                    'concept' => $p->paymentConcept?->name ?? 'Cuota Mensual',
                    'amount' => (float) $p->amount,
                    'gateway' => $p->gateway ?? 'Caja Central',
                    'ncf' => $p->ncf ?? 'B0200000001',
                    'status' => $p->status,
                ]);

                $summary = [
                    'total_records' => $payments->count(),
                    'primary_metric' => 'RD$ ' . number_format($totalAmount, 2),
                    'primary_label' => 'Recaudado (Cobros)',
                    'secondary_metric' => 'RD$ ' . number_format($pendingAmount, 2),
                    'secondary_label' => 'Por Cobrar (Pendiente)',
                ];
                break;

            case 'attendance':
                $attendances = Attendance::with(['enrollment.student', 'courseSchedule'])
                    ->when($scheduleId, fn ($q) => $q->where('course_schedule_id', $scheduleId))
                    ->when(!$scheduleId && $moduleId, fn ($q) => $q->whereHas('courseSchedule', fn ($sq) => $sq->where('module_id', $moduleId)))
                    ->when(!$scheduleId && !$moduleId && $courseId, fn ($q) => $q->whereHas('courseSchedule.module', fn ($mq) => $mq->where('course_id', $courseId)))
                    ->latest('attendance_date')
                    ->take($limit)
                    ->get();

                $presentCount = $attendances->whereIn('status', ['present', 'Presente'])->count();
                $totalAtt = $attendances->count();
                $rate = $totalAtt > 0 ? round(($presentCount / $totalAtt) * 100, 1) : 100;

                $reportData = $attendances->map(fn ($a) => [
                    'id' => $a->id,
                    'date' => $a->attendance_date ? Carbon::parse($a->attendance_date)->format('d/m/Y') : now()->format('d/m/Y'),
                    'student_name' => $a->enrollment?->student ? ($a->enrollment->student->first_name . ' ' . $a->enrollment->student->last_name) : 'Estudiante',
                    'student_code' => $a->enrollment?->student?->student_code ?? 'N/A',
                    'section' => $a->courseSchedule?->section_name ?? 'Sección A',
                    'status' => $a->status ?? 'present',
                    'notes' => $a->notes ?? '-',
                ]);

                $summary = [
                    'total_records' => $attendances->count(),
                    'primary_metric' => "{$rate}%",
                    'primary_label' => 'Tasa de Asistencia',
                    'secondary_metric' => "{$presentCount} / {$totalAtt}",
                    'secondary_label' => 'Asistencias Efectivas',
                ];
                break;

            case 'grades':
                $grades = StudentGrade::with(['student', 'sectionSubject.subject', 'evaluationPeriod'])
                    ->latest()
                    ->take($limit)
                    ->get();

                $avg = $grades->count() > 0 ? round($grades->avg('score'), 1) : 0;
                $approved = $grades->where('score', '>=', 70)->count();

                $reportData = $grades->map(fn ($g) => [
                    'id' => $g->id,
                    'student_name' => $g->student ? ($g->student->first_name . ' ' . $g->student->last_name) : 'Estudiante',
                    'student_code' => $g->student?->student_code ?? 'N/A',
                    'subject' => $g->sectionSubject?->subject?->name ?? 'Materia Académica',
                    'period' => $g->evaluationPeriod?->name ?? 'P1',
                    'score' => (float) $g->score,
                    'status' => $g->score >= 70 ? 'Aprobado' : 'Reprobado',
                ]);

                $summary = [
                    'total_records' => $grades->count(),
                    'primary_metric' => "{$avg} pts",
                    'primary_label' => 'Promedio de Calificaciones',
                    'secondary_metric' => "{$approved} de {$grades->count()}",
                    'secondary_label' => 'Asignaturas Aprobadas',
                ];
                break;

            case 'students':
                $students = Student::with('course')
                    ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
                    ->latest()
                    ->take($limit)
                    ->get();

                $reportData = $students->map(fn ($s) => [
                    'id' => $s->id,
                    'student_code' => $s->student_code ?? sprintf('EST-%04d', $s->id),
                    'student_name' => $s->first_name . ' ' . $s->last_name,
                    'email' => $s->email ?? 'contacto@instituto.edu.do',
                    'phone' => $s->mobile_phone ?? $s->home_phone ?? $s->phone ?? '-',
                    'career' => $s->course?->name ?? 'Carrera General',
                    'status' => $s->status ?? 'Activo',
                ]);

                $summary = [
                    'total_records' => $students->count(),
                    'primary_metric' => (string) $students->where('status', 'Activo')->count(),
                    'primary_label' => 'Estudiantes Activos',
                    'secondary_metric' => (string) $students->count(),
                    'secondary_label' => 'Padrón Total',
                ];
                break;

            case 'debtors':
                $debtorPayments = Payment::with(['student.course', 'paymentConcept'])
                    ->whereIn('status', ['Pendiente', 'pending', 'Vencido'])
                    ->latest()
                    ->take($limit)
                    ->get();

                $totalDebt = $debtorPayments->sum('amount');

                $reportData = $debtorPayments->map(fn ($p) => [
                    'id' => $p->id,
                    'student_code' => $p->student?->student_code ?? 'N/A',
                    'student_name' => $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : 'Estudiante',
                    'career' => $p->student?->course?->name ?? 'Carrera General',
                    'concept' => $p->paymentConcept?->name ?? 'Cuota Mensual',
                    'amount' => (float) $p->amount,
                    'due_date' => $p->due_date?->format('d/m/Y') ?? 'Vencido',
                    'phone' => $p->student?->mobile_phone ?? $p->student?->phone ?? 'Sin contacto',
                ]);

                $summary = [
                    'total_records' => $debtorPayments->count(),
                    'primary_metric' => 'RD$ ' . number_format($totalDebt, 2),
                    'primary_label' => 'Monto Total Vencido',
                    'secondary_metric' => (string) $debtorPayments->unique('student_id')->count(),
                    'secondary_label' => 'Estudiantes con Balance',
                ];
                break;
        }

        return [
            'reportType' => $reportType,
            'reportTitle' => $reportTitles[$reportType] ?? 'Reporte Institucional',
            'reportSubtitle' => $periodLabel,
            'filters' => [
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'schedule_id' => $scheduleId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ],
            'reportData' => $reportData,
            'summary' => $summary,
        ];
    }

    /**
     * Display the reporting dashboard & data grid.
     */
    public function index(Request $request): Response
    {
        $payload = $this->buildReportPayload($request, 100);
        $courseId = $payload['filters']['course_id'];
        $moduleId = $payload['filters']['module_id'];

        $courses = Course::select('id', 'name', 'code')->orderBy('name')->get();
        $modules = $courseId ? Module::where('course_id', $courseId)->select('id', 'name', 'code')->orderBy('name')->get() : [];
        $schedules = $moduleId ? CourseSchedule::where('module_id', $moduleId)->select('id', 'section_name', 'start_time', 'end_time')->get() : [];

        return Inertia::render('Reports/Center', array_merge($payload, [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
        ]));
    }

    /**
     * Generate an official institutional PDF report using DomPDF.
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $payload = $this->buildReportPayload($request, 500);
        $reportType = $payload['reportType'];

        $orientation = in_array($reportType, ['payments', 'students', 'debtors'], true) ? 'landscape' : 'portrait';

        $pdf = Pdf::loadView('reports.center-pdf', $payload)
            ->setPaper('a4', $orientation);

        $fileName = "reporte_oficial_{$reportType}_" . now()->format('Ymd_His') . '.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    /**
     * Download CSV report export.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $payload = $this->buildReportPayload($request, 500);
        $reportType = $payload['reportType'];
        $reportData = $payload['reportData'];
        $fileName = "reporte_{$reportType}_" . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($reportType, $reportData) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($reportType === 'payments') {
                fputcsv($handle, ['ID', 'Fecha', 'Matrícula', 'Estudiante', 'Concepto', 'Monto (RD$)', 'Método', 'NCF', 'Estado']);
                foreach ($reportData as $row) {
                    fputcsv($handle, [
                        $row['id'],
                        $row['date'],
                        $row['student_code'],
                        $row['student_name'],
                        $row['concept'],
                        $row['amount'],
                        $row['gateway'],
                        $row['ncf'],
                        $row['status'],
                    ]);
                }
            } elseif ($reportType === 'attendance') {
                fputcsv($handle, ['ID', 'Fecha', 'Matrícula', 'Estudiante', 'Sección', 'Estado', 'Notas']);
                foreach ($reportData as $row) {
                    fputcsv($handle, [
                        $row['id'],
                        $row['date'],
                        $row['student_code'],
                        $row['student_name'],
                        $row['section'],
                        $row['status'],
                        $row['notes'],
                    ]);
                }
            } elseif ($reportType === 'grades') {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Asignatura', 'Período', 'Calificación', 'Estado']);
                foreach ($reportData as $row) {
                    fputcsv($handle, [
                        $row['id'],
                        $row['student_code'],
                        $row['student_name'],
                        $row['subject'],
                        $row['period'],
                        $row['score'],
                        $row['status'],
                    ]);
                }
            } elseif ($reportType === 'debtors') {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Carrera/Curso', 'Concepto', 'Monto (RD$)', 'Vencimiento', 'Teléfono']);
                foreach ($reportData as $row) {
                    fputcsv($handle, [
                        $row['id'],
                        $row['student_code'],
                        $row['student_name'],
                        $row['career'],
                        $row['concept'],
                        $row['amount'],
                        $row['due_date'],
                        $row['phone'],
                    ]);
                }
            } else {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Email', 'Teléfono', 'Carrera/Curso', 'Estado']);
                foreach ($reportData as $row) {
                    fputcsv($handle, [
                        $row['id'],
                        $row['student_code'],
                        $row['student_name'],
                        $row['email'],
                        $row['phone'],
                        $row['career'],
                        $row['status'],
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
