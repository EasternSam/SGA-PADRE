<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'wp_schedule_string',
        'course_schedule_id',
        'wp_course_id', // <-- AÑADIDO: Faltaba este campo
    ];

    /**
     * Obtiene el horario de Laravel al que está enlazado.
     */
    public function schedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }
}