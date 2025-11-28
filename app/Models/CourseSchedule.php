<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;
    
    /**
     * ¡¡¡CORRECCIÓN!!!
     * Se actualiza 'days' a 'days_of_week' para que coincida
     * con la migración '...0016_modify_days_in_course_schedules_table.php'.
     */
    protected $fillable = [
        'module_id',
        'start_time',
        'end_time',
        'start_date', 
        'end_date',   
        'teacher_id', 
        'days_of_week', // <-- Corregido de 'days'
        'section_name',
        'modality', // <-- NUEVO CAMPO AÑADIDO
    ];

    /**
     * ¡¡¡CORRECCIÓN!!!
     * Se actualiza el 'cast' de 'days' a 'days_of_week'.
     */
    protected $casts = [
        'days_of_week' => 'array', // <-- Corregido de 'days'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function teacher()
    {
        // La llave foránea es 'teacher_id' (según migración ...0014)
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_schedule_id');
    }

    // INICIO: Relación añadida para mapeo de secciones
    /**
     * Obtiene el mapeo de horario de WordPress para esta sección.
     */
    public function mapping()
    {
        return $this->hasOne(ScheduleMapping::class, 'course_schedule_id');
    }
    // FIN: Relación añadida
}