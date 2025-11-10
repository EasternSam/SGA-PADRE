<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * (Alineado con la migración ...003)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'name',
        'code',
        'description',
        'price',
        'duration_hours',
        'status',
        'order', // Añadido desde Courses/Index.php
    ];

    /**
     * Relación: Un Módulo pertenece a un Curso.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación: Un Módulo tiene muchas Secciones (CourseSchedules).
     * ¡ESTA ES LA CORRECCIÓN #6!
     * Renombramos 'sections()' a 'schedules()' para que coincida
     * con la consulta en 'app/Livewire/Courses/Index.php'.
     */
    public function schedules()
    {
        // Esto asume que tu tabla 'course_schedules' tiene la foreign key 'module_id'
        return $this->hasMany(CourseSchedule::class, 'module_id');
    }
}