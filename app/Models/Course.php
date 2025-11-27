<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'code',
        'is_sequential', // Nuevo campo añadido
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'is_sequential' => 'boolean',
    ];

    /**
     * Relación: Un Curso tiene muchos Módulos.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    // ====================================================================
    // NUEVA RELACIÓN PARA ENLACE CON WP (PUNTO 3)
    // ====================================================================

    /**
     * Define la relación con el mapeo/enlace de WordPress.
     * Un curso de Laravel puede tener un (1) enlace a un curso de WP.
     */
    public function mapping()
    {
        return $this->hasOne(CourseMapping::class);
    }
    
    // ====================================================================
    // FIN DE NUEVA RELACIÓN
    // ====================================================================
}