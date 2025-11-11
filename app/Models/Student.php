<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- IMPORTANTE: Para Atributos
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * (¡CORREGIDO! Alineado con tus migraciones y formularios)
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'cedula',
        'email',
        'home_phone',
        'mobile_phone',
        'phone', // Añadido de la migración ...013
        'address',
        'city',
        'sector',
        'birth_date',
        'gender',
        'nationality',
        'how_found',
        'is_minor',
        'status',
        'wp_student_post_id',
        'tutor_name',
        'tutor_cedula',
        'tutor_phone',
        'tutor_relationship',
        'student_code', // Añadido para el reporte
        'admission_date', // Añadido para el reporte
        'dni', // Añadido para el reporte (aunque 'cedula' parece ser el correcto)
    ];

    /**
     * The attributes that should be cast.
     * (¡CORREGIDO!)
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'datetime',
        'admission_date' => 'datetime',
        'is_minor' => 'boolean',
    ];

    // --- Relación Inversa ---
    /**
     * Un Estudiante (perfil) pertenece a un Usuario (login).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Relaciones ---
    /**
     * Un Estudiante tiene muchas Matrículas.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Un Estudiante tiene muchos Pagos.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // --- ACCESORS (Atributos Calculados) ---

    /**
     * OBTENER EL NOMBRE COMPLETO.
     * (¡AÑADIDO! Esto soluciona los errores #3 y #4)
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . $this->last_name),
        );
    }

    /**
     * Obtener la edad del estudiante.
     * (¡ACTUALIZADO! Usa birth_date)
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->birth_date) {
                    // Gracias a $casts, $this->birth_date ya es un objeto Carbon
                    return Carbon::parse($this->birth_date)->age;
                }
                return null;
            },
        );
    }
}