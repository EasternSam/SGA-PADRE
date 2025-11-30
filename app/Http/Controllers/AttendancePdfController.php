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
        
        // --- LÓGICA DE FECHAS MEJORADA ---
        
        // A) Obtener días teóricos del horario (Ej: Lunes, Miércoles)
        $allowedDays = $this->parseCourseDays($section->days);
        $scheduleDates = collect();

        // Solo generamos fechas si hay días configurados
        if (!empty($allowedDays)) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                if (in_array($date->dayOfWeekIso, $allowedDays)) {
                    $scheduleDates->push($date);
                }
            }
        }

        // B) Obtener fechas reales donde YA se registró asistencia
        // Esto es crucial para incluir clases de recuperación o días fuera del horario habitual
        $recordedDates = Attendance::where('course_schedule_id', $section->id)
            ->select('attendance_date')
            ->distinct()
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // C) Fusión: Unimos horario teórico + registros reales y ordenamos
        // Esto elimina duplicados y asegura que "Solo días de la sección" (y excepciones) aparezcan.
        $finalDates = $scheduleDates->merge($recordedDates)
            ->unique(fn($d) => $d->format('Y-m-d'))
            ->sortBy(fn($d) => $d->timestamp)
            ->values(); // Reindexar

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
            'dates' => $finalDates, // Usamos la colección filtrada y fusionada
            'attendances' => $attendances
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Asistencia_' . $section->section_name . '.pdf');
    }

    /**
     * Convierte la configuración de días a ISO-8601 (1=Lunes ... 7=Domingo).
     */
    private function parseCourseDays($days)
    {
        if (empty($days)) return [];

        $list = [];

        // Normalizar entrada
        if (is_array($days)) {
            $list = $days;
        } elseif (is_string($days)) {
            $decoded = json_decode($days, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $list = $decoded;
            } else {
                $list = explode(',', $days);
            }
        }

        $map = [
            'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'miércoles' => 3,
            'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'sábado' => 6, 'domingo' => 7,
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7,
            'lu' => 1, 'ma' => 2, 'mi' => 3, 'ju' => 4, 'vi' => 5, 'sa' => 6, 'do' => 7,
            '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7
        ];

        $result = [];

        foreach ($list as $day) {
            if (is_int($day) && $day >= 1 && $day <= 7) {
                $result[] = $day;
                continue;
            }

            $clean = strtolower(trim(str_replace(['"', "'", '[', ']', '{', '}'], '', (string)$day)));

            // Búsqueda exacta
            if (isset($map[$clean])) {
                $result[] = $map[$clean];
            } else {
                // Búsqueda parcial (ej: "Lun" coincide con "lunes")
                foreach ($map as $key => $val) {
                    if (str_starts_with($key, $clean) || str_contains($clean, $key)) {
                        $result[] = $val;
                        break;
                    }
                }
            }
        }

        return array_unique($result);
    }
}