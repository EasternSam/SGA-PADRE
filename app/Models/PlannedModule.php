<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'degree_plan_id',
        'module_id',
        'target_period',
        'status', // planned, in_progress, completed
    ];

    /**
     * Relación: Una Materia Planificada pertenece a un Plan de Carrera.
     */
    public function degreePlan(): BelongsTo
    {
        return $this->belongsTo(DegreePlan::class);
    }

    /**
     * Relación: Una Materia Planificada pertenece a un Módulo (Materia del Pensum).
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
