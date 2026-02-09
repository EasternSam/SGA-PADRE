<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;

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
        'profile_photo_path', // <-- NUEVO: Ruta de foto personalizada
        
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

    // Relación con Admisión para buscar la foto original
    public function admission()
    {
        // Asumiendo que Admission tiene user_id o un link directo. 
        // Si no hay relación directa, usamos user_id como puente.
        return $this->hasOne(Admission::class, 'user_id', 'user_id');
    }

    /**
     * Obtiene el nombre completo del estudiante.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Obtiene la URL de la foto de perfil.
     * Prioridad: 1. Foto subida manual -> 2. Foto de Admisión -> 3. Avatar por defecto.
     */
    public function getProfilePhotoUrlAttribute()
    {
        // 1. Si el estudiante subió una foto personalizada
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::url($this->profile_photo_path);
        }

        // 2. Si tiene una foto de admisión (foto 2x2)
        // Buscamos la admisión asociada al usuario
        $admission = \App\Models\Admission::where('user_id', $this->user_id)->latest()->first();
        
        if ($admission && !empty($admission->photo_path)) {
             // Las fotos de admisión suelen guardarse en 'private' o 'public'. 
             // Si es pública, retornamos URL. Si es privada, necesitaríamos un controlador temporal, 
             // pero asumiremos que se movió a pública o es accesible.
             if (Storage::disk('public')->exists($admission->photo_path)) {
                 return Storage::url($admission->photo_path);
             }
        }

        // 3. Avatar por defecto (UI Avatars)
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }
}