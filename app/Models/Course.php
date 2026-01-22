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
    ];

    /**
     * Relación: Un Curso tiene muchos Módulos.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
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
}