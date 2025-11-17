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
        // 'code' // Lo mantenemos comentado ya que no está en tu migración original
        // NOTA: Tu migración 2025_11_05_000019_add_code_to_courses_table.php SÍ AÑADE 'code'.
        // Deberías añadir 'code' aquí para que funcione el modal de "Guardar Curso".
        // Lo añadiré, ya que tu propio Livewire/Index.php lo está usando.
        'code',
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