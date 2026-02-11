<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Attendance;
use App\Models\Payment;
use App\Traits\RecordsActivity; // <-- IMPORTANTE

class Enrollment extends Model
{
    use HasFactory, RecordsActivity; // <-- ACTIVAR AUDITORÍA

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
}