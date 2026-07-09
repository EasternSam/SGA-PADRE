<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DegreePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'pace',
        'status',
    ];

    /**
     * Relación: Un Plan de Carrera pertenece a un Estudiante.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relación: Un Plan de Carrera tiene muchas Materias Planificadas.
     */
    public function plannedModules(): HasMany
    {
        return $this->hasMany(PlannedModule::class);
    }
}
