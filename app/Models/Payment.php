<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, RecordsActivity, LogsActivity;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'payment_concept_id',
        'user_id',
        'amount',
        'original_amount',
        'discount_amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'due_date',
        'notes',

        // --- Campos e-CF (Facturación Electrónica - Gridbase Bills Core) ---
        'ncf',
        'ncf_type',
        'security_code',
        'ncf_expiration',
        'dgii_track_id',
        'dgii_status',

        // --- Campos NCF Cliente ---
        'rnc_client',
        'company_name',
        'ncf_type_requested',

        // --- Campos de Integración con Bills ---
        'bills_invoice_id',
        'bills_invoice_number',
        'bills_sync_status',
        'bills_sync_error',

        // --- Campos de Caja (POS) ---
        'quantity',
        'concept_label',
        'cash_received',
        'change_amount',
    ];

    protected $casts = [
        'due_date' => 'date',
        'ncf_expiration' => 'date',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * DGII NCF & Accounting Auto-Generation Logic — Powered by Gridbase Bills Core
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

        // 1.5 Antes de Eliminar (Prevenir vacíos contables en DGII)
        static::deleting(function ($payment) {
            if (!empty($payment->ncf)) {
                throw new \Exception("PROTECCIÓN FISCAL: No se puede eliminar este cobro porque ya tiene un Comprobante Fiscal (NCF: {$payment->ncf}) generado. Para anularlo legalmente y no crear un salto de secuencia en la DGII, debe usar el proceso de Nota de Crédito.");
            }
        });

        // 2. Después de Guardar (Para tener un ID de pago real para el Asiento Contable)
        static::created(function ($payment) {
            self::processPaymentAccounting($payment);
            self::processPaymentBillsSync($payment);
        });

        static::updated(function ($payment) {
            self::processPaymentAccounting($payment);
            self::processPaymentBillsSync($payment);
        });
    }

    protected static function processPaymentFiscal($payment)
    {
        // Si la facturación externa vía API en Bills está activa, omitimos la generación local,
        // ya que el webhook/job remoto se encargará de ello.
        if (\App\Models\Setting::get('enable_bills_invoicing', 'false') === 'true') {
            return;
        }

        /** @var \Gridbase\BillsCore\Contracts\IssuerConfigInterface $issuer */
        $issuer = app(\Gridbase\BillsCore\Contracts\IssuerConfigInterface::class);

        // Verificar si la facturación electrónica está habilitada (Fix: precedencia de operadores)
        if (!$issuer->isElectronicBillingEnabled()) {
            return;
        }

        // Require 'paid' status
        if ($payment->status === 'paid' || $payment->status === 'Completado') {
            if (empty($payment->ncf)) {
                $requested = (string) ($payment->ncf_type_requested ?? '02');
                $typeCode = in_array($requested, ['01', '31'], true) ? '31' : '32';

                try {
                    /** @var \Gridbase\BillsCore\Services\Dgii\EcfManagerService $ecfManager */
                    $ecfManager = app(\Gridbase\BillsCore\Services\Dgii\EcfManagerService::class);

                    $amount = (float) ($payment->amount ?? 0);
                    $conceptName = $payment->concept_label
                        ?: ($payment->paymentConcept->name ?? null)
                        ?: ($payment->notes ?: 'Servicio Educativo');

                    $buyerRnc = $payment->rnc_client
                        ?: ($payment->student->cedula ?? '');
                    $buyerName = $payment->company_name
                        ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Consumidor Final');

                    $issued = $ecfManager->issueDocument([
                        'ecf_type' => (int) $typeCode,
                        'issue_date' => now()->format('Y-m-d'),
                        'subtotal' => $amount,
                        'discount_amount' => (float) ($payment->discount_amount ?? 0),
                        'tax_amount' => 0.00,
                        'total' => $amount,
                        'buyer_rnc' => (string) $buyerRnc,
                        'buyer_name' => (string) $buyerName,
                        'items' => [
                            [
                                'description' => $conceptName,
                                'quantity' => max(1, (int) ($payment->quantity ?? 1)),
                                'unit_price' => $amount / max(1, (int) ($payment->quantity ?? 1)),
                                'amount' => $amount,
                                'is_exempt' => true,
                                'tax_rate' => 0.0,
                            ],
                        ],
                    ], $issuer, false);

                    $payment->ncf = $issued['ncf'];
                    $payment->ncf_type = $issued['ncf_type'];
                    $payment->security_code = $issued['security_code'];
                    $payment->ncf_expiration = $issued['ncf_expiration'];
                    $payment->dgii_status = $issued['dgii_status'] ?? 'issued_local';
                    if (!empty($issued['dgii_track_id'])) {
                        $payment->dgii_track_id = $issued['dgii_track_id'];
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[BillsCore] No se pudo emitir NCF para el pago: ' . $e->getMessage());
                }
            }
        }
    }

    protected static function processPaymentAccounting($payment)
    {
        // Require 'paid' status
        if ($payment->status === 'paid' || $payment->status === 'Completado') {
            $alreadyExists = \App\Models\AccountingEntry::where('reference_type', self::class)
                                ->where('reference_id', $payment->id)
                                ->exists();

            if (!$alreadyExists) {
                app(\App\Services\AccountingEngine::class)->registerStudentPayment($payment);
            }
        }
    }

    protected static function processPaymentBillsSync($payment)
    {
        if (\App\Models\Setting::get('enable_bills_invoicing', 'false') === 'true') {
            if (empty($payment->bills_invoice_id)
                && $payment->bills_sync_status !== 'synced'
                && $payment->bills_sync_status !== 'processing') {
                \App\Jobs\SyncPaymentToBillsJob::dispatch($payment);
            } elseif (!empty($payment->bills_invoice_id)
                && ($payment->status === 'paid' || $payment->status === 'Completado')
                && empty($payment->ncf)) {
                \App\Jobs\SyncPaymentToBillsJob::dispatch($payment);
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

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

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
     * Genera la URL oficial de consulta para el código QR usando Gridbase Bills Core
     */
    public function getDgiiQrUrlAttribute()
    {
        if (!$this->ncf || !$this->security_code) {
            return null;
        }

        /** @var \Gridbase\BillsCore\Contracts\IssuerConfigInterface $issuer */
        $issuer = app(\Gridbase\BillsCore\Contracts\IssuerConfigInterface::class);

        /** @var \Gridbase\BillsCore\Services\Dgii\EcfManagerService $ecfManager */
        $ecfManager = app(\Gridbase\BillsCore\Services\Dgii\EcfManagerService::class);

        return $ecfManager->buildQrUrl(
            $issuer->getEmitterRnc(),
            (string) $this->ncf,
            (float) $this->amount,
            (string) $this->security_code,
            $issuer->getEnvironment(),
            (string) ($this->rnc_client ?? ''),
            $this->created_at ? $this->created_at->format('Y-m-d') : date('Y-m-d')
        );
    }
}
