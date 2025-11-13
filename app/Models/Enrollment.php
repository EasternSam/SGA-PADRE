<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        // --- ¡¡¡AÑADIDO!!! ---
        // Asegúrate de que 'final_grade' esté aquí si quieres actualizarlo
        // desde el componente de notas del profesor (TeacherPortal\Grades).
        'final_grade',
    ];

    /**
     * Define la relación con el estudiante.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Define la relación con el horario del curso (sección).
     */
    public function courseSchedule(): BelongsTo
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }

    // --- ¡¡¡NUEVA RELACIÓN!!! ---
    /**
     * Define la relación con las asistencias.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}