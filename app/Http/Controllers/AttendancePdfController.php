<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendancePdfController extends Controller
{
    /**
     * Genera y descarga el PDF de asistencia.
     */
    public function download(CourseSchedule $section)
    {
        // 1. Cargar relaciones necesarias
        // Cargamos los estudiantes activos y ordenados alfabéticamente
        $section->load(['module.course', 'teacher', 'enrollments' => function($query) {
            // CORRECCIÓN: Especificar 'enrollments.status' para evitar la ambigüedad de columnas
            // debido al JOIN con la tabla 'students'.
            $query->whereNotIn('enrollments.status', ['Pendiente', 'pendiente'])
                  ->with('student')
                  ->join('students', 'enrollments.student_id', '=', 'students.id')
                  ->orderBy('students.last_name')
                  ->orderBy('students.first_name')
                  ->select('enrollments.*'); // Importante para evitar conflictos de ID
        }]);

        // 2. Generar el rango de fechas completo basado en la duración del curso
        $startDate = Carbon::parse($section->start_date);
        $endDate = Carbon::parse($section->end_date);
        
        // Determinar qué días de la semana se imparte la clase
        $allowedDays = $this->parseCourseDays($section->days);
        
        $dates = collect();
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Si no hay días definidos, mostramos todos.
            // Si hay días definidos, filtramos para mostrar solo esos días.
            if (empty($allowedDays) || in_array($date->dayOfWeekIso, $allowedDays)) {
                $dates->push($date);
            }
        }

        // 3. Obtener registros de asistencia existentes
        $attendanceRecords = Attendance::where('course_schedule_id', $section->id)->get();

        // 4. Estructurar datos: [fecha_Y-m-d][enrollment_id] = registro
        $attendances = [];
        foreach ($attendanceRecords as $record) {
            $dateStr = Carbon::parse($record->attendance_date)->format('Y-m-d');
            $attendances[$dateStr][$record->enrollment_id] = $record;
        }

        // 5. Generar PDF
        $pdf = Pdf::loadView('livewire.reports.attendance-report', [
            'section' => $section,
            'enrollments' => $section->enrollments,
            'dates' => $dates,
            'attendances' => $attendances
        ]);

        // Configuración de papel horizontal para que quepan más fechas
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Asistencia_' . $section->section_name . '.pdf');
    }

    /**
     * Convierte la lista de días (string o array) a números ISO (1=Lunes, 7=Domingo)
     */
    private function parseCourseDays($days)
    {
        if (empty($days)) return [];

        // Mapa de días a ISO-8601 (1 para Lunes, 7 para Domingo)
        $map = [
            'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'miércoles' => 3,
            'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'sábado' => 6, 'domingo' => 7,
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7
        ];

        $result = [];
        
        // Si viene como JSON o Array
        if (is_string($days)) {
            // Intentar decodificar JSON o separar por comas
            $decoded = json_decode($days, true);
            $list = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) 
                ? $decoded 
                : explode(',', $days);
        } elseif (is_array($days)) {
            $list = $days;
        } else {
            return [];
        }

        foreach ($list as $day) {
            $cleanDay = strtolower(trim(str_replace(['"', '[', ']'], '', $day)));
            if (isset($map[$cleanDay])) {
                $result[] = $map[$cleanDay];
            }
        }

        return $result;
    }
}