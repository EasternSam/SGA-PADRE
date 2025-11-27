<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Module extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
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
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación: Un Módulo tiene muchas Secciones (CourseSchedules).
     */
    public function schedules(): HasMany
    {
        // Esto asume que tu tabla 'course_schedules' tiene la foreign key 'module_id'
        return $this->hasMany(CourseSchedule::class, 'module_id');
    }

    /**
     * Relación: Un módulo tiene muchas inscripciones a través de sus secciones.
     * CORRECCIÓN: Usamos HasManyThrough para buscar enrollments vinculados a los schedules de este módulo.
     */
    public function enrollments(): HasManyThrough
    {
        // Estructura: Module -> tiene muchos CourseSchedule -> tiene muchos Enrollment
        // Laravel buscará:
        // 1. course_schedules.module_id (para conectar Módulo -> Sección)
        // 2. enrollments.course_schedule_id (para conectar Sección -> Inscripción)
        return $this->hasManyThrough(Enrollment::class, CourseSchedule::class);
    }
}