<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Importar

class Course extends Model
{
    use HasFactory, SoftDeletes; // <-- Usar Trait

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'code',
        'is_sequential',
        'registration_fee',
        'monthly_fee',
        'credit_price', // <-- NUEVO: Precio por crédito configurable
        // Nuevos campos Universidad
        'program_type', // 'technical' o 'degree'
        'total_credits',
        'duration_periods',
        'degree_title',
        'status', // Agregado status que estaba en la migración original pero faltaba aquí
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'is_sequential' => 'boolean',
        'registration_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'credit_price' => 'decimal:2', // <-- NUEVO: Cast para decimal
        // Nuevos casts
        'total_credits' => 'integer',
        'duration_periods' => 'integer',
    ];

    /**
     * Relación: Un Curso tiene muchos Módulos.
     * En modo universidad, ordenamos por periodo (pensum).
     */
    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('period_number')->orderBy('order');
    }

    // ====================================================================
    // RELACIÓN PARA ENLACE CON WP
    // ====================================================================

    /**
     * Define la relación con el mapeo/enlace de WordPress.
     */
    public function mapping()
    {
        return $this->hasOne(CourseMapping::class);
    }

    // --- Helpers para Modo ---
    
    public function isUniversity()
    {
        return $this->program_type === 'degree';
    }

    public function isTechnical()
    {
        return $this->program_type === 'technical';
    }
}