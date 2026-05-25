<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrientationRecord extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'type', 'title',
        'description', 'findings', 'recommendations',
        'priority', 'status', 'next_followup',
        'counselor_id', 'is_confidential',
    ];

    protected $casts = [
        'next_followup'  => 'date',
        'is_confidential' => 'boolean',
    ];

    const TYPES = [
        'interview'     => '🗣️ Entrevista',
        'observation'   => '👁️ Observación',
        'referral'      => '📋 Referimiento',
        'followup'      => '🔄 Seguimiento',
        'psychological' => '🧠 Psicológico',
        'family'        => '👨‍👩‍👧 Familiar',
        'academic'      => '📚 Académico',
    ];

    const PRIORITIES = [
        'low'    => '🟢 Baja',
        'medium' => '🟡 Media',
        'high'   => '🟠 Alta',
        'urgent' => '🔴 Urgente',
    ];

    const STATUSES = [
        'open'        => '📂 Abierto',
        'in_progress' => '🔄 En Progreso',
        'resolved'    => '✅ Resuelto',
        'referred'    => '📤 Referido',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
