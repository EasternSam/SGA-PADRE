<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'details',
        'status',
        'admin_notes',
    ];

    /**
     * Obtiene el estudiante que realizó la solicitud.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}