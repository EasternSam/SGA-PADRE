<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = []; // Permitir asignación masiva

    /**
     * Un pago pertenece a un estudiante.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Un pago pertenece a una inscripción (opcional).
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Un pago pertenece a un concepto de pago (opcional).
     */
    public function concept()
    {
        return $this->belongsTo(PaymentConcept::class, 'payment_concept_id');
    }

    /**
     * NUEVA RELACIÓN:
     * Un pago fue registrado por un Usuario (Admin).
     * Esta es la corrección que soluciona el error.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}