<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Attendance as AttendanceModel;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Muestra la página principal de gestión de reportes (Livewire).
     */
    public function index()
    {
        return view('livewire.reports.index');
    }

    /**
     * Genera un reporte PDF del estudiante.
     *
     * @param Student $student
     * @return \Illuminate\Http\Response
     */
    public function generateStudentReport(Student $student)
    {
        // Cargar relaciones necesarias
        $student->load([
            'enrollments.courseSchedule.module.course',
            'enrollments.courseSchedule.teacher',
            'payments.paymentConcept'
        ]);

        $data = [
            'student' => $student,
            'enrollments' => $student->enrollments,
            'payments' => $student->payments,
            'date' => now()->format('d/m/Y')
        ];

        // Cargar vista y generar PDF
        // Asegúrate de que 'reports.student-report' sea una vista limpia
        $pdf = Pdf::loadView('reports.student-report', $data);
        
        // Opcional: Configurar papel
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('reporte-estudiante-' . $student->id . '.pdf');
    }

    /**
     * Genera un reporte PDF de asistencia para una sección.
     *
     * @param CourseSchedule $schedule
     * @return \Illuminate\Http\Response
     */
    public function printAttendance(CourseSchedule $schedule) // Renombrado para consistencia o mantener generateAttendanceReport
    {
        // Definir rango de fechas (Mes actual por defecto)
        $dateFrom = now()->startOfMonth()->format('Y-m-d');
        $dateTo = now()->endOfMonth()->format('Y-m-d');

        // Obtener Estudiantes (Excluyendo 'Pendiente')
        // CORRECCIÓN APLICADA: Se agregan las condiciones whereNotIn para excluir inscripciones pendientes
        $students = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.course_schedule_id', $schedule->id)
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente']) // <-- FILTRO APLICADO
            ->select('students.id', 'students.first_name', 'students.last_name')
            ->distinct()
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->get();

        // Generar rango de fechas
        $period = CarbonPeriod::create($dateFrom, $dateTo);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        // Mapeo Enrollment -> Student
        $enrollmentMap = DB::table('enrollments')
            ->where('course_schedule_id', $schedule->id)
            ->whereNotIn('status', ['Pendiente', 'pendiente']) // <-- FILTRO APLICADO
            ->pluck('student_id', 'id');

        // Obtener Asistencias
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $schedule->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        // Construir Matriz
        $attendanceMatrix = [];
        foreach ($attendances as $record) {
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            if ($studentId) {
                $dateKey = substr($record->attendance_date, 0, 10); 
                $attendanceMatrix[$studentId][$dateKey] = $record->status;
            }
        }

        $reportData = [
            'schedule' => $schedule->load('module.course', 'teacher'),
            'students' => $students,
            'dates' => $dates,
            'matrix' => $attendanceMatrix,
            'start_date' => Carbon::parse($dateFrom),
            'end_date' => Carbon::parse($dateTo),
            'stats' => [
                'total_days' => count($dates),
                'total_students' => $students->count(),
            ]
        ];

        // Generar PDF usando la vista limpia
        // Usamos la misma vista que creamos para livewire pero cargada por DomPDF
        $pdf = Pdf::loadView('livewire.reports.attendance-report', ['reportData' => $reportData]);
        
        $pdf->setPaper('A4', 'landscape'); // Paisaje para que quepan las columnas

        return $pdf->stream('asistencia-' . $schedule->section_name . '.pdf');
    }
}