<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Attendance;
use App\Models\Payment;
use App\Traits\RecordsActivity; // <-- Importar el Trait de Auditoría

class Enrollment extends Model
{
    use HasFactory, RecordsActivity; // <-- Activar Auditoría Automática

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'course_schedule_id',
        'payment_id', // <-- NUEVO: Vinculación a pago agrupado
        'status', // Ej. Pendiente, Cursando, Completado
        'final_grade',
        'next_billing_date', // <-- NUEVO: Facturación Delta
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'next_billing_date' => 'date',
    ];

    /**
     * Método "booted" para lógica automática del modelo.
     */
    protected static function booted()
    {
        static::deleting(function ($enrollment) {
            // --- SOLUCIÓN AL PROBLEMA DE DEUDA HUÉRFANA ---
            // Antes de eliminar la inscripción, buscamos pagos PENDIENTES vinculados a ella
            // y los eliminamos. Como Payment también tiene el Trait RecordsActivity,
            // esto dejará un log: "Sistema eliminó registro en Payment: Monto..."
            
            Payment::where('enrollment_id', $enrollment->id)
                ->whereIn('status', ['Pendiente', 'pendiente']) // Solo borrar si no se ha pagado
                ->get()
                ->each(function ($payment) {
                    $payment->delete(); 
                });
        });
    }

    /**
     * Define la relación con el estudiante.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Define la relación con el horario del curso (sección).
     */
    public function courseSchedule(): BelongsTo
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }

    /**
     * Define la relación con las asistencias.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relación con el Pago Global (si aplica).
     * Un enrollment pertenece a un pago (cuando es agrupado).
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Relación inversa para pagos individuales que apuntan a este enrollment.
     */
    public function individualPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    /**
     * Determina si la inscripción está pagada verificando ambos tipos de pagos (Agrupados e Individuales).
     */
    public function getIsPaidAttribute(): bool
    {
        $paidStatuses = ['paid', 'pagado', 'completado', 'aprobado', 'succeeded', 'active', 'activo'];

        // 1. Revisar pago agrupado ($this->payment)
        if ($this->payment && in_array(strtolower($this->payment->status ?? ''), $paidStatuses)) {
            return true;
        }

        // 2. Revisar pagos individuales en la colección cargada o con consulta lazy loading
        if ($this->relationLoaded('individualPayments')) {
            return $this->individualPayments->contains(function ($payment) use ($paidStatuses) {
                return in_array(strtolower($payment->status ?? ''), $paidStatuses);
            });
        }

        // Fallback por si no se cargó con eager loading
        return $this->individualPayments()
            ->whereIn('status', ['Completado', 'Pagado', 'Aprobado', 'Paid', 'Succeeded', 'completado', 'pagado', 'aprobado', 'paid', 'succeeded', 'activo', 'active'])
            ->exists();
    }
}