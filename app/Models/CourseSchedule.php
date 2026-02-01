<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSchedule extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'module_id',
        'classroom_id',
        'start_time',
        'end_time',
        'start_date', 
        'end_date',   
        'teacher_id', 
        'days_of_week', 
        'section_name',
        'modality', 
        'capacity', // <-- Nuevo campo agregado
    ];

    protected $casts = [
        'days_of_week' => 'array', 
        'capacity' => 'integer', // <-- Cast a entero
    ];

    /**
     * Accessor para compatibilidad con vistas que usan 'day_of_week' (singular).
     * Devuelve el primer día del array o el valor directo si no es array.
     */
    public function getDayOfWeekAttribute()
    {
        if (empty($this->days_of_week)) {
            return null;
        }

        // Si es array (por el cast), devolvemos el primer elemento
        if (is_array($this->days_of_week)) {
            return $this->days_of_week[0] ?? null;
        }

        // Si por alguna razón es string, lo devolvemos tal cual
        return $this->days_of_week;
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_schedule_id');
    }

    public function mapping()
    {
        return $this->hasOne(ScheduleMapping::class, 'course_schedule_id');
    }
    
    // Helper para verificar cupos disponibles
    public function getAvailableSpotsAttribute()
    {
        return $this->capacity - $this->enrollments()->count();
    }
    
    public function isFull()
    {
        return $this->enrollments()->count() >= $this->capacity;
    }
}