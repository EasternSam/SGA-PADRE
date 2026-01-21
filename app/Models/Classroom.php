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
    
    // Helper para ver disponibilidad en tiempo real
    public function isOccupiedNow()
    {
        $now = now();
        $currentDay = ucfirst($now->locale('es')->dayName); // Lunes, Martes...
        
        return $this->schedules()
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereJsonContains('days_of_week', $currentDay)
            ->where('start_time', '<=', $now->format('H:i'))
            ->where('end_time', '>', $now->format('H:i'))
            ->exists();
    }
}