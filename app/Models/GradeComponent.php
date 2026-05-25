<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeComponent extends Model
{
    protected $fillable = [
        'section_subject_id',
        'evaluation_period_id',
        'name',
        'weight',
        'max_score',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    // Plantillas de componentes por nivel
    const TEMPLATES = [
        'primario' => [
            ['name' => 'Prueba Escrita', 'weight' => 30],
            ['name' => 'Trabajo Práctico', 'weight' => 30],
            ['name' => 'Participación', 'weight' => 15],
            ['name' => 'Actitud y Valores', 'weight' => 10],
            ['name' => 'Tareas', 'weight' => 15],
        ],
        'secundario' => [
            ['name' => 'Prueba Escrita', 'weight' => 30],
            ['name' => 'Producción Escrita/Proyecto', 'weight' => 25],
            ['name' => 'Trabajo Práctico', 'weight' => 20],
            ['name' => 'Participación', 'weight' => 15],
            ['name' => 'Actitud y Valores', 'weight' => 10],
        ],
    ];

    public function sectionSubject(): BelongsTo
    {
        return $this->belongsTo(SectionSubject::class);
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function partialGrades()
    {
        return $this->hasMany(PartialGrade::class);
    }
}
