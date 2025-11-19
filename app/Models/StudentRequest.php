<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id', // Agregado para vincular la solicitud al curso
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
}