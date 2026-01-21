<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'module_id',
        'classroom_id', // <-- NUEVO
        'start_time',
        'end_time',
        'start_date', 
        'end_date',   
        'teacher_id', 
        'days_of_week', 
        'section_name',
        'modality', 
    ];

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
    
    // Relación con el Aula
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