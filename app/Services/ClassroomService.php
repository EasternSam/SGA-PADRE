<?php

namespace App\Services;

use App\Models\CourseSchedule;
use Carbon\Carbon;

class ClassroomService
{
    /**
     * Verifica si un aula está disponible para un horario específico.
     */
    public function checkAvailability($classroomId, $days, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId = null)
    {
        if (empty($days) || !$classroomId) return true;

        // Buscar conflictos
        $conflicts = CourseSchedule::query()
            ->whereNull('deleted_at') // <--- FORZAR FILTRO DE ELIMINADOS
            ->where('classroom_id', $classroomId)
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
            ->with('module.course') 
            ->get();

        foreach ($conflicts as $conflict) {
            $commonDays = array_intersect($days, $conflict->days_of_week ?? []);
            
            if (!empty($commonDays)) {
                $courseName = $conflict->module->course->name ?? 'Un curso';
                $daysStr = implode(', ', $commonDays);
                return "Conflicto con: {$courseName} ({$conflict->section_name}). Coincide en: {$daysStr} de {$conflict->start_time} a {$conflict->end_time}.";
            }
        }

        return true; 
    }
}