<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity; // <-- IMPORTANTE
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, RecordsActivity, LogsActivity; // <-- ACTIVAR AUDITORÍA (Spatie + Custom)

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

    /**
     * DGII NCF & Accounting Auto-Generation Logic
     */
    protected static function booted()
    {
        // 1. Antes de Guardar (Para asignar NCFs atómicamente a la fila)
        static::creating(function ($payment) {
            self::processPaymentFiscal($payment);
        });

        static::updating(function ($payment) {
            self::processPaymentFiscal($payment);
        });

        // 2. Después de Guardar (Para tener un ID de pago real para el Asiento Contable)
        static::created(function ($payment) {
            self::processPaymentAccounting($payment);
        });

        static::updated(function ($payment) {
            self::processPaymentAccounting($payment);
        });
    }

    protected static function processPaymentFiscal($payment)
    {
        // Require 'paid' status
        if ($payment->status === 'paid' || $payment->status === 'Completado') {
            
            // 1. DGII NCF Generation
            if (empty($payment->ncf)) {
                $typeCode = ($payment->ncf_type_requested === '01') ? '31' : '32';
                
                $sequence = \App\Models\NcfSequence::where('type_code', $typeCode)
                                                   ->where('is_active', true)
                                                   ->first();
                
                if ($sequence) {
                    $ncf = $sequence->getNextNcf(); // ATOMIC and thread-safe
                    if ($ncf) {
                        $payment->ncf = $ncf;
                        $payment->ncf_type = $typeCode;
                        $payment->security_code = str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                        $payment->ncf_expiration = $sequence->expiration_date;
                    }
                }
            }
        }
    }

    protected static function processPaymentAccounting($payment)
    {
        // Require 'paid' status
        if ($payment->status === 'paid' || $payment->status === 'Completado') {
            // Check if already has an accounting entry to prevent duplicates on `updated`
            $alreadyExists = \App\Models\AccountingEntry::where('reference_type', self::class)
                                ->where('reference_id', $payment->id)
                                ->exists();

            if (!$alreadyExists) {
                app(\App\Services\AccountingEngine::class)->registerStudentPayment($payment);
            }
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'gateway', 'transaction_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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