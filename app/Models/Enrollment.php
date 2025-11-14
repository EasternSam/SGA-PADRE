<?php

namespace App\Models; // Corregido (tu namespace estaba bien, el del contexto tenía un punto)

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- AÑADIDO (para attendances)
use Illuminate\Database\Eloquent\Relations\HasOne;  // <-- AÑADIDO (para payment)
use App\Models\Attendance; // <-- AÑADIDO (para la relación)
use App\Models\Payment; // <-- AÑADIDO (para la relación)

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'course_schedule_id',
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
     * (Esta relación venía en tu archivo)
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Una inscripción puede tener un pago asociado (si se pagó por inscripción).
     * (Esta es la actualización que faltaba)
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}