<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRequest extends Model
{
    use HasFactory;

    // Aseguramos que estos campos sean asignables para evitar "Curso No especificado"
    protected $fillable = [
        'student_id',
        'course_id',   // Crucial para vincular el curso
        'payment_id',  // Crucial para el cobro
        'type',
        'details',
        'status',
        'admin_notes',
    ];

    /**
     * Obtiene el estudiante que realizó la solicitud.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Obtiene el curso relacionado (si aplica).
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Obtiene el pago relacionado a esta solicitud (si aplica).
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}