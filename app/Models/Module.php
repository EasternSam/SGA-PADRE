<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Importar
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Module extends Model
{
    use HasFactory, SoftDeletes; // <-- Usar Trait

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
        'order',
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
        return $this->hasMany(CourseSchedule::class, 'module_id');
    }

    /**
     * Relación: Un módulo tiene muchas inscripciones a través de sus secciones.
     */
    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(Enrollment::class, CourseSchedule::class);
    }
}