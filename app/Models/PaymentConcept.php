<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentConcept extends Model
{
    use HasFactory;
    protected $guarded = []; // Permitir asignación masiva

    /**
     * Un concepto de pago puede estar en múltiples pagos.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_concept_id');
    }
}