<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity; // <-- IMPORTANTE

class Payment extends Model
{
    use HasFactory, RecordsActivity; // <-- ACTIVAR AUDITORÍA

    protected $fillable = [
        'student_id',
        'enrollment_id', // Mantenido para cursos individuales (Legacy/Directo)
        'payment_concept_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'due_date',
        'notes',
        
        // --- Campos e-CF (Facturación Electrónica) ---
        'ncf',
        'ncf_type',
        'security_code',
        'ncf_expiration',
        'dgii_track_id',
        'dgii_status',
        
        // --- Campos NCF Cliente ---
        'rnc_client',   
        'company_name',
        'ncf_type_requested'
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

    /**
     * Relación 1 a 1 (Cursos Individuales / Legacy).
     * Se usa cuando el pago es por UN curso específico directo.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * NUEVA: Relación 1 a Muchos (Carreras/Selección de Materias).
     * Se usa cuando el pago agrupa VARIAS materias (un cuatrimestre).
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
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
        $rncEmisor = '101000000'; // Debería venir de config('ecf.rnc_emisor')
        
        if (!$this->ncf || !$this->security_code) {
            return null;
        }

        return "https://ecf.dgii.gov.do/consultas?rnc={$rncEmisor}&encf={$this->ncf}&monto={$this->amount}&codigoseguridad={$this->security_code}";
    }
}