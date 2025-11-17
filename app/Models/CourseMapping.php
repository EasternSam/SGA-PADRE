<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para el enlace de Cursos (CourseMapping).
 * Representa la conexión entre un curso de Laravel y un curso de WordPress.
 */
class CourseMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'wp_course_id',
        'wp_course_name',
    ];

    /**
     * Define la relación inversa con el Curso de Laravel.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}