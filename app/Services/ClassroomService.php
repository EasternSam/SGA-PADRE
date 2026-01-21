<?php

namespace App\Services;

use App\Models\CourseSchedule;
use App\Models\Classroom;
use Carbon\Carbon;

class ClassroomService
{
    /**
     * Verifica si un aula está disponible para un horario específico.
     * * @param int $classroomId
     * @param array $days Días de la semana ['Lunes', 'Miércoles']
     * @param string $startTime 'HH:MM'
     * @param string $endTime 'HH:MM'
     * @param string $startDate 'Y-m-d'
     * @param string $endDate 'Y-m-d'
     * @param int|null $excludeScheduleId ID a excluir (para ediciones)
     * @return bool|string True si está libre, String con el error si está ocupada.
     */
    public function checkAvailability($classroomId, $days, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId = null)
    {
        if (empty($days) || !$classroomId) return true;

        // Buscar conflictos
        $conflicts = CourseSchedule::where('classroom_id', $classroomId)
            ->where(function ($query) use ($startDate, $endDate) {
                // Que las fechas del curso se solapen
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                // Que las horas se solapen
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->when($excludeScheduleId, function ($q) use ($excludeScheduleId) {
                $q->where('id', '!=', $excludeScheduleId);
            })
            ->with('module.course') // Para mostrar info del conflicto
            ->get();

        // Filtrar por días de la semana (JSON array filter no siempre es eficiente en SQL puro para arrays, lo hacemos en PHP para precisión)
        foreach ($conflicts as $conflict) {
            // Intersección de días: Si tienen días en común, hay choque
            $commonDays = array_intersect($days, $conflict->days_of_week ?? []);
            
            if (!empty($commonDays)) {
                $courseName = $conflict->module->course->name ?? 'Un curso';
                $daysStr = implode(', ', $commonDays);
                return "Conflicto con: {$courseName} ({$conflict->section_name}). Coincide en: {$daysStr} de {$conflict->start_time} a {$conflict->end_time}.";
            }
        }

        return true; // Disponible
    }
}