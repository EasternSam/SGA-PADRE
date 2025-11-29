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
        $pdf = Pdf::loadView('reports.student-report', $data);
        
        // Opcional: Configurar papel
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('reporte-estudiante-' . $student->id . '.pdf');
    }

    /**
     * Genera un reporte PDF de asistencia para una sección.
     * Coincide con la ruta: /reports/attendance-report/{section}
     *
     * @param CourseSchedule $section
     * @return \Illuminate\Http\Response
     */
    public function generateAttendanceReport(CourseSchedule $section) 
    {
        // Definir rango de fechas (Mes actual por defecto)
        $dateFrom = now()->startOfMonth()->format('Y-m-d');
        $dateTo = now()->endOfMonth()->format('Y-m-d');

        // Obtener Estudiantes (Excluyendo 'Pendiente')
        // Usamos DB::table para optimización, evitando cargar modelos pesados
        $students = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.course_schedule_id', $section->id)
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente']) 
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

        // Mapeo Enrollment -> Student (Filtrado)
        $enrollmentMap = DB::table('enrollments')
            ->where('course_schedule_id', $section->id)
            ->whereNotIn('status', ['Pendiente', 'pendiente'])
            ->pluck('student_id', 'id');

        // Obtener Asistencias
        $attendances = DB::table('attendances')
            ->where('course_schedule_id', $section->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->select('enrollment_id', 'attendance_date', 'status')
            ->get(); 

        // Construir Matriz de Asistencia en Memoria
        $attendanceMatrix = [];
        foreach ($attendances as $record) {
            $studentId = $enrollmentMap[$record->enrollment_id] ?? null;
            if ($studentId) {
                // Cortar fecha para asegurar formato Y-m-d
                $dateKey = substr($record->attendance_date, 0, 10); 
                $attendanceMatrix[$studentId][$dateKey] = $record->status;
            }
        }

        // Cargar relaciones de la sección para el encabezado del reporte
        $section->load('module.course', 'teacher');

        // --- ADAPTACIÓN DE DATOS PARA LA VISTA ---
        // La vista espera una colección de $enrollments donde pueda llamar a $enrollment->student->last_name
        // y un array de $attendances agrupado. Reconstruimos esa estructura de forma ligera.

        // 1. Recrear estructura de enrollments
        $enrollmentsForView = $students->map(function($student) use ($enrollmentMap) {
            $enrollmentId = $enrollmentMap->search($student->id);
            
            $enrollmentObj = new \stdClass();
            $enrollmentObj->id = $enrollmentId;
            $enrollmentObj->student = $student; // Contiene first_name y last_name
            return $enrollmentObj;
        });

        // 2. Recrear estructura de asistencias para la vista ($attendances[fecha][id_inscripcion])
        $attendancesForView = [];
        foreach ($dates as $dateStr) {
            foreach ($enrollmentMap as $enrId => $stuId) {
                if (isset($attendanceMatrix[$stuId][$dateStr])) {
                    $status = $attendanceMatrix[$stuId][$dateStr];
                    $obj = new \stdClass();
                    $obj->status = $status; // La vista espera $record->status
                    $attendancesForView[$dateStr][$enrId] = $obj;
                }
            }
        }

        // Convertir fechas a objetos Carbon
        $datesCarbon = collect($dates)->map(function($d) { return Carbon::parse($d); });

        $data = [
            'section' => $section,
            'enrollments' => $enrollmentsForView,
            'attendances' => $attendancesForView,
            'dates' => $datesCarbon,
        ];

        // Cargar vista limpia y generar PDF
        $pdf = Pdf::loadView('reports.attendance-report', $data);
        
        $pdf->setPaper('A4', 'landscape'); // Paisaje para que quepan las columnas

        return $pdf->stream('asistencia-' . $section->section_name . '.pdf');
    }
}