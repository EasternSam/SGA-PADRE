<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordsActivity; // <-- IMPORTANTE

class Student extends Model
{
    use HasFactory, RecordsActivity; // <-- ACTIVAR AUDITORÍA

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Vinculación con User
        'course_id', // <-- NUEVO: Carrera/Curso asignado
        'student_code', // Matrícula
        'first_name',
        'last_name',
        'cedula',
        'email',
        'gender',
        'birth_date',
        'nationality',
        'address',
        'sector',
        'city',
        'home_phone', // Teléfono residencial/fijo
        'mobile_phone', // Teléfono celular
        'how_found', // Fuente de captación
        'status', // Activo, Inactivo, etc.
        'balance', // Balance financiero
        'rnc', // RNC para comprobantes fiscales
        
        // Campos de Tutor (para menores de edad)
        'is_minor',
        'tutor_name',
        'tutor_cedula',
        'tutor_phone',
        'tutor_relationship',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_minor' => 'boolean',
        'balance' => 'decimal:2',
    ];

    /**
     * Un estudiante (Student) pertenece a un usuario (User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la Carrera/Curso principal.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Un estudiante tiene muchas inscripciones (Enrollments).
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Un estudiante tiene muchos pagos (Payments).
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Un estudiante puede tener muchas solicitudes.
     */
    public function requests()
    {
        return $this->hasMany(StudentRequest::class);
    }

    /**
     * Obtiene el nombre completo del estudiante.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}