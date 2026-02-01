<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'identification_id',
        'birth_date',
        'course_id',
        'previous_school',
        'previous_gpa',
        'status', 
        'notes',
        'documents', 
        'document_status', // Nuevo campo
        'address',
        'work_place',
        'disease',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'documents' => 'array',
        'document_status' => 'array', // Cast automático
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}