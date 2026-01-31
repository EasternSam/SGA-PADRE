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
        'days_of_week', // Revertido a 'days_of_week' (plural) para mantener consistencia con el esquema
        'section_name',
        'modality', 
    ];

    // Mantenemos el cast si la columna en base de datos espera almacenar arrays/JSON
    // O si el sistema espera interactuar con este campo como un array.
    protected $casts = [
        'days_of_week' => 'array',
    ];

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
}