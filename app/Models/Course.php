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
        // 'code' // Lo mantenemos comentado ya que no está en tu migración
    ];

    /**
     * Relación: Un Curso tiene muchos Módulos.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}