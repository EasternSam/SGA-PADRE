<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Payment extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'payment_concept_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        // No incluimos 'user_id' porque no está en la migración
    ];

    // --- ¡¡¡AÑADIDO!!! ---
    /**
     * Los atributos que deben ser casteados.
     * Esto asegura que el 'amount' se trate como un número decimal.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];
    // --- FIN DE LA ACTUALIZACIÓN ---

    /**
     * Obtiene el estudiante al que pertenece el pago.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Obtiene el concepto del pago.
     * ¡ESTA ES LA RELACIÓN QUE FALTABA!
     */
    public function paymentConcept(): BelongsTo
    {
        return $this->belongsTo(PaymentConcept::class);
    }

    /**
     * Obtiene la inscripción (opcional) a la que pertenece el pago.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}