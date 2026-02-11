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
}