<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // <-- ¡Importante! Asegúrate de que esto esté.

// --- AÑADIDOS PARA EL NUEVO REPORTE ---
use App\Models\CourseSchedule;
use App\Models\Attendance as AttendanceModel;
use Carbon\CarbonPeriod;
// --- FIN DE AÑADIDOS ---

class ReportController extends Controller
{
    /**
     * Genera un reporte de estudiante.
     *
     * @param Student $student
     * @return \Illuminate\Http\Response
     */
    public function generateStudentReport(Student $student)
    {
        // Cargar las relaciones necesarias para el reporte
        $student->load(
            'enrollments.courseSchedule.module.course', 
            'enrollments.courseSchedule.teacher', 
            'payments.paymentConcept'
        );

        $data = [
            'student' => $student,
            'enrollments' => $student->enrollments,
            'payments' => $student->payments,
        ];

        // --- ¡ESTA ES LA CORRECCIÓN! ---
        
        // 1. Cargamos la misma vista que ya tenías ('reports.student-report')
        //    y le pasamos los datos.
        $pdf = Pdf::loadView('reports.student-report', $data);

        // 2. En lugar de descargar, usamos ->stream() para que se muestre 
        //    en el navegador (perfecto para nuestro <iframe> en el modal).
        return $pdf->stream('reporte-estudiante-' . $student->id . '.pdf');
    }

    // --- ¡¡¡NUEVO MÉTODO AÑADIDO!!! ---
    /**
     * Genera un reporte de asistencia completo para una sección.
     *
     * @param CourseSchedule $section
     * @return \Illuminate\Http\Response
     */
    public function generateAttendanceReport(CourseSchedule $section)
    {
        // Cargar las relaciones necesarias
        $section->load('module.course', 'teacher', 'enrollments.student');

        // Obtener todos los estudiantes inscritos
        $enrollments = $section->enrollments()->with('student')->get();

        // Obtener TODAS las asistencias de esta sección, agrupadas por fecha y luego por ID de inscripción
        // para una búsqueda súper rápida en la vista
        $attendances = AttendanceModel::where('course_schedule_id', $section->id)
            ->get()
            ->groupBy(function ($date) {
                // Agrupa por fecha en formato Y-m-d
                return \Carbon\Carbon::parse($date->attendance_date)->format('Y-m-d');
            })
            ->map(function ($day) {
                // Dentro de cada fecha, indexa por enrollment_id
                return $day->keyBy('enrollment_id');
            });

        // Generar el rango de todas las fechas desde el inicio hasta el fin del curso
        $dates = CarbonPeriod::create($section->start_date, $section->end_date);

        // Datos para la vista
        $data = [
            'section' => $section,
            'enrollments' => $enrollments,
            'attendances' => $attendances,
            'dates' => $dates,
        ];

        // Generar el PDF
        $pdf = Pdf::loadView('reports.attendance-report', $data);
        
        // Configurar para paisaje (landscape) y un tamaño de papel más grande
        $pdf->setPaper('A3', 'landscape');

        // Retornar el PDF al navegador
        return $pdf->stream('reporte_asistencia_' . $section->id . '.pdf');
    }
    // --- FIN DEL NUEVO MÉTODO ---
}