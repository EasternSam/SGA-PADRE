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
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'due_date', // Agregado
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

    public function concept()
    {
        return $this->belongsTo(PaymentConcept::class, 'payment_concept_id');
    }
}