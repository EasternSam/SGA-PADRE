<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime', // Para formatear fácil con ->format('H:i')
        'end_time' => 'datetime',
    ];

    // Helper para obtener color según tipo
    public function getColorAttribute()
    {
        return match($this->type) {
            'holiday' => 'bg-red-100 text-red-700 border-red-200',
            'administrative' => 'bg-amber-100 text-amber-700 border-amber-200',
            'academic' => 'bg-blue-100 text-blue-700 border-blue-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}