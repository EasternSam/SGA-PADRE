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
        
        // --- LÓGICA DE FECHAS ---
        
        // A) Obtener días teóricos del horario (Ej: [6] para solo Sábados)
        $allowedDays = $this->parseCourseDays($section->days);
        $scheduleDates = collect();

        // Generamos fechas para TODO el periodo
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            // CAMBIO IMPORTANTE: Eliminamos el "|| empty($allowedDays)".
            // Ahora, solo agregamos el día si ESTÁ explícitamente en la lista de días permitidos.
            // Si la configuración de días está vacía, no asumimos nada (solo saldrán las fechas registradas en el paso B).
            if (!empty($allowedDays) && in_array($date->dayOfWeekIso, $allowedDays)) {
                $scheduleDates->push($date->copy());
            }
        }

        // B) Obtener fechas reales donde YA se registró asistencia
        // Esto asegura que si diste una clase extra un día fuera de horario, aparezca en el reporte.
        $recordedDates = Attendance::where('course_schedule_id', $section->id)
            ->select('attendance_date')
            ->distinct()
            ->get()
            ->pluck('attendance_date')
            ->map(fn($date) => Carbon::parse($date));

        // C) Fusión: Unimos horario teórico + registros reales y ordenamos
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
            'dates' => $finalDates,
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
            // ISO Standards
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7,
            
            // Español (sin tildes para búsqueda fácil)
            'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 7,
            'lu' => 1, 'ma' => 2, 'mi' => 3, 'ju' => 4, 'vi' => 5, 'sa' => 6, 'do' => 7,
            
            // English
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7,
        ];

        $result = [];

        foreach ($list as $day) {
            // Limpieza robusta: minúsculas UTF8, quitar acentos básicos y caracteres extraños
            $clean = mb_strtolower(trim(str_replace(['"', "'", '[', ']', '{', '}'], '', (string)$day)), 'UTF-8');
            $clean = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $clean);

            // 1. Coincidencia Exacta
            if (isset($map[$clean])) {
                $result[] = $map[$clean];
                continue;
            }

            // 2. Coincidencia Parcial (ej: "Sábados" contiene "sabado" o empieza por "sab")
            foreach ($map as $key => $val) {
                // Evitamos coincidir números con strings vacíos
                if (is_string($key) && (str_contains($clean, $key) || str_starts_with($key, $clean))) {
                    $result[] = $val;
                    break;
                }
            }
        }

        return array_unique($result);
    }
}