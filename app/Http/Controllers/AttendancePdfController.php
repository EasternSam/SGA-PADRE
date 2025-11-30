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
        // 1. Cargar relaciones necesarias
        $section->load(['module.course', 'teacher', 'enrollments' => function($query) {
            $query->whereNotIn('status', ['Pendiente', 'pendiente'])
                  ->with('student')
                  ->orderBy('id');
        }]);

        // 2. Obtener fechas únicas de asistencia
        $dates = Attendance::where('course_schedule_id', $section->id)
            ->select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'asc')
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // 3. Obtener registros de asistencia crudos
        $attendanceRecords = Attendance::where('course_schedule_id', $section->id)->get();

        // 4. Estructurar datos: [fecha_Y-m-d][enrollment_id] = registro
        $attendances = [];
        foreach ($attendanceRecords as $record) {
            $dateStr = Carbon::parse($record->attendance_date)->format('Y-m-d');
            $attendances[$dateStr][$record->enrollment_id] = $record;
        }

        // 5. Generar PDF
        // CORRECCIÓN: Apuntando a la vista en la carpeta livewire/reports
        $pdf = Pdf::loadView('livewire.reports.attendance-report', [
            'section' => $section,
            'enrollments' => $section->enrollments,
            'dates' => $dates,
            'attendances' => $attendances
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Asistencia_' . $section->module->name . '.pdf');
    }
}