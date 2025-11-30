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
        $section->load(['module.course', 'teacher', 'enrollments' => function($query) {
            // Especificamos 'enrollments.status' para evitar ambigüedad en el JOIN
            $query->whereNotIn('enrollments.status', ['Pendiente', 'pendiente'])
                  ->with('student')
                  ->join('students', 'enrollments.student_id', '=', 'students.id')
                  ->orderBy('students.last_name')
                  ->orderBy('students.first_name')
                  ->select('enrollments.*');
        }]);

        // 2. Definir rango de fechas
        $startDate = Carbon::parse($section->start_date);
        $endDate = Carbon::parse($section->end_date);
        
        // --- LÓGICA DE FECHAS (Ajustada a requerimientos) ---
        
        // A) Obtener días teóricos del horario (días de la sección)
        // Normalizamos el array de días guardado en BD
        $allowedDays = $this->normalizeDays($section->days_of_week);
        $scheduleDates = collect();

        // Generamos fechas para TODO el periodo (incluyendo fechas futuras/próximas)
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            // "haz que aparezcan los días de la seccion proximos, aunque estén en N/A"
            // Solo agregamos si el día coincide con la configuración de la sección (ej. Sábados)
            if (!empty($allowedDays) && in_array($date->dayOfWeekIso, $allowedDays)) {
                $scheduleDates->push($date->copy());
            }
        }

        // B) Obtener fechas reales donde YA se registró asistencia
        // "en caso de que se haya pasado lista un dia que no está programado... solo agrega ese día"
        $recordedDates = Attendance::where('course_schedule_id', $section->id)
            ->select('attendance_date')
            ->distinct()
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // C) Fusión: Días Programados + Días Extras con asistencia
        // El merge asegura que aparezcan:
        // 1. Todos los días oficiales del curso (pasados y futuros).
        // 2. Cualquier día extra (recuperación, cambio de día) que tenga asistencia registrada.
        $finalDates = $scheduleDates->merge($recordedDates)
            ->unique(fn($d) => $d->format('Y-m-d'))
            ->sortBy(fn($d) => $d->timestamp)
            ->values(); 

        // 3. Obtener registros de asistencia
        $attendanceRecords = Attendance::where('course_schedule_id', $section->id)->get();

        // 4. Estructurar datos para la vista
        $attendances = [];
        foreach ($attendanceRecords as $record) {
            $dateStr = Carbon::parse($record->attendance_date)->format('Y-m-d');
            $attendances[$dateStr][$record->enrollment_id] = $record;
        }

        // 5. Generar PDF
        $pdf = Pdf::loadView('livewire.reports.attendance-report', [
            'section' => $section,
            'enrollments' => $section->enrollments,
            'dates' => $finalDates,
            'attendances' => $attendances
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Asistencia_' . $section->section_name . '.pdf');
    }

    /**
     * Normaliza los días a un array de enteros ISO (1=Lunes ... 7=Domingo).
     */
    private function normalizeDays($days)
    {
        if (empty($days)) return [];

        if (is_string($days)) {
            $decoded = json_decode($days, true);
            $days = (json_last_error() === JSON_ERROR_NONE) ? $decoded : explode(',', $days);
        }

        if (!is_array($days)) return [];

        return array_map(function($day) {
            return (int) $day;
        }, $days);
    }
}