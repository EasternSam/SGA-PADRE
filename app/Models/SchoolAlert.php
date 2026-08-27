<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolAlert extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'type', 'severity',
        'title', 'description', 'metadata', 'is_read', 'is_resolved',
        'resolved_at', 'resolved_by', 'resolution_note',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'is_read'     => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    const TYPES = [
        'absence_streak'  => 'Ausencias Consecutivas',
        'low_performance'  => 'Bajo Rendimiento',
        'dropout_risk'     => 'Riesgo de Abandono',
        'discipline'       => 'Disciplina',
        'custom'           => 'Personalizada',
    ];

    const SEVERITIES = [
        'info'     => ['label' => 'Informativa', 'color' => '#3b82f6'],
        'warning'  => ['label' => 'Advertencia', 'color' => '#f59e0b'],
        'critical' => ['label' => 'Crítica', 'color' => '#ef4444'],
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    public function resolve($userId, $note = null): void
    {
        $this->update([
            'is_resolved'     => true,
            'resolved_at'     => now(),
            'resolved_by'     => $userId,
            'resolution_note' => $note,
        ]);
    }
}
