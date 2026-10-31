<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCenterController extends Controller
{
    /**
     * Display the reporting dashboard & data grid.
     */
    public function index(Request $request): Response
    {
        $reportType = $request->query('report_type', 'payments');
        $courseId = $request->query('course_id');
        $moduleId = $request->query('module_id');
        $scheduleId = $request->query('schedule_id');
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->endOfMonth()->toDateString());
        $status = $request->query('status', 'all');

        $courses = Course::select('id', 'name', 'code')->orderBy('name')->get();
        $modules = $courseId ? Module::where('course_id', $courseId)->select('id', 'name', 'code')->orderBy('name')->get() : [];
        $schedules = $moduleId ? CourseSchedule::where('module_id', $moduleId)->select('id', 'section_name', 'start_time', 'end_time')->get() : [];

        $reportData = [];
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
                        Carbon::parse($dateTo)->endOfDay()
                    ]);

                if ($status !== 'all') {
                    $query->where('status', $status);
                }

                $payments = $query->latest('created_at')->take(100)->get();
                $totalAmount = $payments->where('status', 'Pagado')->sum('amount');
                $pendingAmount = $payments->whereIn('status', ['Pendiente', 'pending', 'Vencido'])->sum('amount');

                $reportData = $payments->map(fn($p) => [
                    'id' => $p->id,
                    'date' => $p->created_at?->format('d/m/Y H:i'),
                    'student_code' => $p->student?->student_code ?? sprintf('EST-%04d', $p->student_id),
                    'student_name' => $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : 'Estudiante General',
                    'concept' => $p->paymentConcept?->name ?? 'Cuota Mensual',
                    'amount' => (float)$p->amount,
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
                    ->when($scheduleId, fn($q) => $q->where('course_schedule_id', $scheduleId))
                    ->when(!$scheduleId && $moduleId, fn($q) => $q->whereHas('courseSchedule', fn($sq) => $sq->where('module_id', $moduleId)))
                    ->when(!$scheduleId && !$moduleId && $courseId, fn($q) => $q->whereHas('courseSchedule.module', fn($mq) => $mq->where('course_id', $courseId)))
                    ->latest('attendance_date')
                    ->take(100)
                    ->get();

                $presentCount = $attendances->where('status', 'present')->count();
                $totalAtt = $attendances->count();
                $rate = $totalAtt > 0 ? round(($presentCount / $totalAtt) * 100, 1) : 100;

                $reportData = $attendances->map(fn($a) => [
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
                    ->take(100)
                    ->get();

                $avg = $grades->count() > 0 ? round($grades->avg('score'), 1) : 0;
                $approved = $grades->where('score', '>=', 70)->count();

                $reportData = $grades->map(fn($g) => [
                    'id' => $g->id,
                    'student_name' => $g->student ? ($g->student->first_name . ' ' . $g->student->last_name) : 'Estudiante',
                    'student_code' => $g->student?->student_code ?? 'N/A',
                    'subject' => $g->sectionSubject?->subject?->name ?? 'Materia Académica',
                    'period' => $g->evaluationPeriod?->name ?? 'P1',
                    'score' => (float)$g->score,
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
                    ->latest()
                    ->take(100)
                    ->get();

                $reportData = $students->map(fn($s) => [
                    'id' => $s->id,
                    'student_code' => $s->student_code ?? sprintf('EST-%04d', $s->id),
                    'student_name' => $s->first_name . ' ' . $s->last_name,
                    'email' => $s->email ?? 'contacto@instituto.edu.do',
                    'phone' => $s->phone ?? '-',
                    'career' => $s->course?->name ?? 'Carrera General',
                    'status' => $s->status ?? 'Activo',
                ]);

                $summary = [
                    'total_records' => $students->count(),
                    'primary_metric' => (string)$students->where('status', 'Activo')->count(),
                    'primary_label' => 'Estudiantes Activos',
                    'secondary_metric' => (string)$students->count(),
                    'secondary_label' => 'Padrón Total',
                ];
                break;

            case 'debtors':
                $debtorPayments = Payment::with(['student.course', 'paymentConcept'])
                    ->whereIn('status', ['Pendiente', 'pending', 'Vencido'])
                    ->latest()
                    ->take(100)
                    ->get();

                $totalDebt = $debtorPayments->sum('amount');

                $reportData = $debtorPayments->map(fn($p) => [
                    'id' => $p->id,
                    'student_code' => $p->student?->student_code ?? 'N/A',
                    'student_name' => $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : 'Estudiante',
                    'career' => $p->student?->course?->name ?? 'Carrera General',
                    'concept' => $p->paymentConcept?->name ?? 'Cuota Mensual',
                    'amount' => (float)$p->amount,
                    'due_date' => $p->due_date?->format('d/m/Y') ?? 'Vencido',
                    'phone' => $p->student?->phone ?? 'Sin contacto',
                ]);

                $summary = [
                    'total_records' => $debtorPayments->count(),
                    'primary_metric' => 'RD$ ' . number_format($totalDebt, 2),
                    'primary_label' => 'Monto Total Vencido',
                    'secondary_metric' => (string)$debtorPayments->unique('student_id')->count(),
                    'secondary_label' => 'Estudiantes con Balance',
                ];
                break;
        }

        return Inertia::render('Reports/Center', [
            'reportType' => $reportType,
            'filters' => [
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'schedule_id' => $scheduleId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ],
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'reportData' => $reportData,
            'summary' => $summary,
        ]);
    }

    /**
     * Download CSV report export.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $reportType = $request->query('report_type', 'payments');
        $fileName = "reporte_{$reportType}_" . now()->format('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($reportType) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($reportType === 'payments') {
                fputcsv($handle, ['ID', 'Fecha', 'Matrícula', 'Estudiante', 'Concepto', 'Monto (RD$)', 'Método', 'NCF', 'Estado']);
                $payments = Payment::with(['student', 'paymentConcept'])->latest()->take(500)->get();
                foreach ($payments as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->created_at?->format('d/m/Y H:i'),
                        $p->student?->student_code ?? '',
                        $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : '',
                        $p->paymentConcept?->name ?? '',
                        $p->amount,
                        $p->gateway ?? 'Caja',
                        $p->ncf ?? '',
                        $p->status,
                    ]);
                }
            } elseif ($reportType === 'attendance') {
                fputcsv($handle, ['ID', 'Fecha', 'Matrícula', 'Estudiante', 'Sección', 'Estado', 'Notas']);
                $attendances = Attendance::with(['enrollment.student', 'courseSchedule'])->latest('attendance_date')->take(500)->get();
                foreach ($attendances as $a) {
                    fputcsv($handle, [
                        $a->id,
                        $a->attendance_date ? Carbon::parse($a->attendance_date)->format('d/m/Y') : '',
                        $a->enrollment?->student?->student_code ?? 'N/A',
                        $a->enrollment?->student ? ($a->enrollment->student->first_name . ' ' . $a->enrollment->student->last_name) : 'Estudiante',
                        $a->courseSchedule?->section_name ?? 'Sección A',
                        $a->status ?? 'present',
                        $a->notes ?? '-',
                    ]);
                }
            } elseif ($reportType === 'grades') {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Asignatura', 'Período', 'Calificación', 'Estado']);
                $grades = StudentGrade::with(['student', 'sectionSubject.subject', 'evaluationPeriod'])->latest()->take(500)->get();
                foreach ($grades as $g) {
                    fputcsv($handle, [
                        $g->id,
                        $g->student?->student_code ?? 'N/A',
                        $g->student ? ($g->student->first_name . ' ' . $g->student->last_name) : 'Estudiante',
                        $g->sectionSubject?->subject?->name ?? 'Materia Académica',
                        $g->evaluationPeriod?->name ?? 'P1',
                        (float) $g->score,
                        $g->score >= 70 ? 'Aprobado' : 'Reprobado',
                    ]);
                }
            } elseif ($reportType === 'debtors') {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Carrera/Curso', 'Concepto', 'Monto (RD$)', 'Vencimiento', 'Teléfono']);
                $debtorPayments = Payment::with(['student.course', 'paymentConcept'])
                    ->whereIn('status', ['Pendiente', 'pending', 'Vencido'])
                    ->latest()
                    ->take(500)
                    ->get();
                foreach ($debtorPayments as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->student?->student_code ?? 'N/A',
                        $p->student ? ($p->student->first_name . ' ' . $p->student->last_name) : 'Estudiante',
                        $p->student?->course?->name ?? 'Carrera General',
                        $p->paymentConcept?->name ?? 'Cuota Mensual',
                        (float) $p->amount,
                        $p->due_date?->format('d/m/Y') ?? 'Vencido',
                        $p->student?->phone ?? 'Sin contacto',
                    ]);
                }
            } else {
                fputcsv($handle, ['ID', 'Matrícula', 'Estudiante', 'Email', 'Teléfono', 'Estado']);
                $students = Student::latest()->take(500)->get();
                foreach ($students as $s) {
                    fputcsv($handle, [
                        $s->id,
                        $s->student_code ?? '',
                        $s->first_name . ' ' . $s->last_name,
                        $s->email ?? '',
                        $s->phone ?? '',
                        $s->status ?? 'Activo',
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
