<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Importar
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        // Nuevos campos Universidad
        'credits',
        'period_number',
        'is_elective',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'credits' => 'integer',
        'is_elective' => 'boolean',
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

    // --- RELACIONES DE PRE-REQUISITOS (UNIVERSIDAD) ---

    /**
     * Materias que son requisito PARA cursar esta materia.
     * (Ej: Si soy "Matemática II", esto me devuelve "Matemática I")
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_prerequisites', 'module_id', 'prerequisite_id')
                    ->withTimestamps();
    }

    /**
     * Materias para las cuales esta materia ES requisito.
     * (Ej: Si soy "Matemática I", esto me devuelve "Matemática II")
     */
    public function requiredFor(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_prerequisites', 'prerequisite_id', 'module_id')
                    ->withTimestamps();
    }
}