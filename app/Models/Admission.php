<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', // Nuevo campo vinculado
        'first_name',
        'last_name',
        'email',
        'phone',
        'identification_id',
        'birth_date',
        'course_id',
        'previous_school',
        'previous_gpa',
        'status', // pending, approved, rejected, info_required
        'notes',
        'documents', 
        'address',
        'work_place',
        'disease',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'documents' => 'array',
    ];

    // Relación con el usuario que creó la solicitud
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con la carrera de interés
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Helper para nombre completo
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}