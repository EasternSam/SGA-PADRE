<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'course_schedule_id',
        'status',
    ];

    /**
     * Define la relación con el estudiante.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * ¡¡¡CORRECCIÓN!!!
     * Se añade la relación 'courseSchedule' que faltaba.
     * Eloquent la buscará usando la llave foránea 'course_schedule_id'.
     */
    public function courseSchedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }
}

