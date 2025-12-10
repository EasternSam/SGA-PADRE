<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'payment_concept_id',
        'user_id', // Agregamos user_id al fillable por si usas la asignación masiva
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relación con el concepto de pago.
     * Renombrado de 'concept' a 'paymentConcept' para coincidir con tu código Livewire.
     */
    public function paymentConcept()
    {
        return $this->belongsTo(PaymentConcept::class, 'payment_concept_id');
    }

    /**
     * Relación con el usuario (quien registró o procesó el pago, si aplica).
     * Agregado porque tu código intenta hacer ->with('user').
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}