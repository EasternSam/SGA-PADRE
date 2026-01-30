<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'building_id', 'name', 'capacity', 
        'pc_count', 'type', 'equipment', 'is_active'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function schedules()
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function reservations()
    {
        return $this->hasMany(ClassroomReservation::class);
    }
    
    // Helper para ver disponibilidad en tiempo real con prioridad de RESERVA
    public function isOccupiedNow()
    {
        $now = now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');
        $currentDay = ucfirst($now->locale('es')->dayName); 

        // 1. PRIORIDAD: Verificar si hay una RESERVA puntual para HOY AHORA
        $reservation = $this->reservations()
            ->where('reserved_date', $currentDate)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->first();

        if ($reservation) {
            // Si hay reserva, está ocupada por la reserva (ignoramos el horario normal)
            return true; 
        }

        // 2. Si NO hay reserva hoy, verificamos el horario normal
        // Pero OJO: Si hubiera una reserva para "Todo el día" que cancela la clase, 
        // la lógica anterior ya lo cubrió (si la reserva existe, ocupa).
        // La regla dice: "si choca... se ocupe la actividad". 
        
        return $this->schedules()
            ->whereNull('deleted_at') // <--- FORZAR FILTRO
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereJsonContains('days_of_week', $currentDay)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->exists();
    }

    // Helper opcional para obtener QUIÉN la ocupa (para mostrar en el frontend)
    public function getCurrentOccupantLabel()
    {
        $now = now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');
        $currentDay = ucfirst($now->locale('es')->dayName); 

        // 1. Chequear Reserva
        $reservation = $this->reservations()
            ->where('reserved_date', $currentDate)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->first();

        if ($reservation) {
            return "Reservado: " . $reservation->title;
        }

        // 2. Chequear Materia
        $schedule = $this->schedules()
            ->with('module.course')
            ->whereNull('deleted_at')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereJsonContains('days_of_week', $currentDay)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->first();

        if ($schedule) {
            return $schedule->module->course->name ?? 'Clase Regular';
        }

        return 'Libre';
    }
}