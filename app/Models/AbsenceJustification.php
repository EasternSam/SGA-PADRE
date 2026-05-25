<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceJustification extends Model
{
    protected $fillable = [
        'student_id', 'date_from', 'date_to', 'reason',
        'description', 'document_path', 'status',
        'submitted_by', 'reviewed_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    const REASONS = [
        'medical'     => '🏥 Médica',
        'family'      => '👨‍👩‍👧 Familiar',
        'travel'      => '✈️ Viaje',
        'appointment' => '📋 Cita/Trámite',
        'other'       => '📌 Otra',
    ];

    const STATUSES = [
        'pending'  => '⏳ Pendiente',
        'approved' => '✅ Aprobada',
        'rejected' => '❌ Rechazada',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }
}
