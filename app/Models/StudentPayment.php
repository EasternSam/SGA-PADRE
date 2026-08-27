<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPayment extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'type', 'concept',
        'amount', 'paid', 'status', 'due_date', 'paid_date',
        'method', 'receipt_number', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'paid'      => 'decimal:2',
        'due_date'  => 'date',
        'paid_date' => 'date',
    ];

    const TYPES = [
        'inscription' => 'Inscripción',
        'monthly'     => 'Mensualidad',
        'uniform'     => 'Uniforme',
        'material'    => 'Material',
        'event'       => 'Evento',
        'other'       => 'Otro',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'partial' => 'Parcial',
        'paid'    => 'Pagado',
        'waived'  => 'Exonerado',
    ];

    const METHODS = [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'other'    => 'Otro',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function getBalanceAttribute(): float
    {
        return $this->amount - $this->paid;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->paid >= $this->amount;
    }
}
