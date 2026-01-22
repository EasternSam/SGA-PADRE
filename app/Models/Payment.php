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
        'user_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'due_date',
        // --- Campos e-CF ---
        'ncf',
        'ncf_type',
        'security_code',
        'ncf_expiration',
        'dgii_track_id',
        'dgii_status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'ncf_expiration' => 'date',
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

    public function paymentConcept()
    {
        return $this->belongsTo(PaymentConcept::class, 'payment_concept_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genera la URL oficial de consulta para el código QR
     */
    public function getDgiiQrUrlAttribute()
    {
        // RNC del Emisor (CENTU) - Debe venir de config
        $rncEmisor = '101000000'; 
        
        // Si no hay NCF o código de seguridad, no se puede generar un QR válido de e-CF
        if (!$this->ncf || !$this->security_code) {
            return null;
        }

        return "https://ecf.dgii.gov.do/consultas?rnc={$rncEmisor}&encf={$this->ncf}&monto={$this->amount}&codigoseguridad={$this->security_code}";
    }
}