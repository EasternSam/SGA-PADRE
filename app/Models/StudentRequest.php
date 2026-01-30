<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'request_type_id', // Nuevo campo
        'course_id',
        'payment_id',
        'details',
        'status',
        'admin_notes',
    ];

    /**
     * Tipo de solicitud (Configuración).
     */
    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }

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