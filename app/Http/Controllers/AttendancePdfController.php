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
        // Normalizamos el array de días guardado en BD usando lógica robusta
        // para soportar tanto números como nombres de días.
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
     * Soporta enteros, strings numéricos y nombres de días en español/inglés.
     */
    private function normalizeDays($days)
    {
        if (empty($days)) return [];

        // Si viene como string JSON o lista separada por comas, decodificar
        if (is_string($days)) {
            $decoded = json_decode($days, true);
            $days = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) 
                ? $decoded 
                : explode(',', $days);
        }

        if (!is_array($days)) return [];

        $map = [
            // ISO Standards
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7,
            
            // Español (sin tildes para búsqueda fácil)
            'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'miércoles' => 3, 
            'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'sábado' => 6, 'domingo' => 7,
            'lu' => 1, 'ma' => 2, 'mi' => 3, 'ju' => 4, 'vi' => 5, 'sa' => 6, 'do' => 7,
            
            // English
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7,
        ];

        $result = [];

        foreach ($days as $day) {
            // Caso 1: Entero o string numérico simple
            if (is_numeric($day)) {
                $val = (int)$day;
                if ($val >= 1 && $val <= 7) {
                    $result[] = $val;
                    continue;
                }
            }

            // Caso 2: Texto (Nombre del día)
            if (is_string($day)) {
                $clean = mb_strtolower(trim(str_replace(['"', "'", '[', ']', '{', '}'], '', $day)), 'UTF-8');
                $clean = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $clean);

                if (isset($map[$clean])) {
                    $result[] = $map[$clean];
                    continue;
                }

                // Búsqueda parcial (ej: "Sáb" coincide con "sabado")
                foreach ($map as $key => $val) {
                    if (is_string($key) && (str_starts_with($key, $clean) || str_starts_with($clean, $key))) {
                        $result[] = $val;
                        break;
                    }
                }
            }
        }

        return array_unique($result);
    }
}