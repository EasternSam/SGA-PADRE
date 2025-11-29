<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AttendancePdfController extends Controller
{
    /**
     * Genera y descarga el PDF de asistencia.
     */
    public function download(CourseSchedule $section)
    {
        // 1. Cargar relaciones necesarias (Sección, Curso, Profesor, Estudiantes activos)
        $section->load(['module.course', 'teacher', 'enrollments' => function($query) {
            $query->whereNotIn('status', ['Pendiente', 'pendiente'])
                  ->with('student')
                  ->orderBy('id'); // O ordenar por apellido si es posible en la relación
        }]);

        // 2. Obtener fechas únicas de asistencia registradas para esta sección
        // Ordenamos ascendente para que aparezcan cronológicamente en las columnas
        $dates = Attendance::where('course_schedule_id', $section->id)
            ->select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'asc')
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // 3. Obtener todos los registros de asistencia crudos
        $attendanceRecords = Attendance::where('course_schedule_id', $section->id)->get();

        // 4. Estructurar datos para la vista: [fecha_Y-m-d][enrollment_id] = registro
        // Esto permite un acceso rápido en la vista sin hacer consultas en el loop
        $attendances = [];
        foreach ($attendanceRecords as $record) {
            $dateStr = Carbon::parse($record->attendance_date)->format('Y-m-d');
            $attendances[$dateStr][$record->enrollment_id] = $record;
        }

        // 5. Generar PDF usando la vista 'reports.attendance-report'
        $pdf = Pdf::loadView('reports.attendance-report', [
            'section' => $section,
            'enrollments' => $section->enrollments,
            'dates' => $dates,
            'attendances' => $attendances
        ]);

        // Configuración opcional de papel (horizontal suele ser mejor para asistencia)
        $pdf->setPaper('a4', 'landscape');

        // Retornar el stream para visualización en el navegador/iframe
        return $pdf->stream('Reporte_Asistencia_' . $section->module->name . '.pdf');
    }
}