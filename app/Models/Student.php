<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // <-- AÑADIDO: Para vincular con el User
        'student_code', // Este será la "matrícula"
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
        'home_phone',
        'mobile_phone',
        'how_found', // (Ej. Redes Sociales, referido, etc.)
        'status', // (Ej. Activo, Inactivo, Graduado, Prospecto)
        'balance', // (Balance financiero pendiente)

        // Campos de Tutor (si es menor)
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
     * Obtiene el nombre completo del estudiante.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Un estudiante puede tener muchas solicitudes.
     * ESTA ES LA NUEVA FUNCIÓN AÑADIDA
     */
    public function requests()
    {
        return $this->hasMany(StudentRequest::class);
    }
}