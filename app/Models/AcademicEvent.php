<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AcademicEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'type', // 'academic', 'holiday', 'grading_period', etc.
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getColorAttribute()
    {
        return match($this->type) {
            'holiday' => 'bg-red-100 text-red-700 border-red-200',
            'administrative' => 'bg-amber-100 text-amber-700 border-amber-200',
            'grading_period' => 'bg-purple-100 text-purple-700 border-purple-200', // Nuevo tipo
            'academic' => 'bg-blue-100 text-blue-700 border-blue-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Verifica si hoy es un día permitido para una acción específica.
     * Ejemplo: AcademicEvent::isActionActive('grading_period');
     */
    public static function isActionActive($type)
    {
        $today = Carbon::now()->startOfDay();
        
        // Buscamos si existe un evento de este tipo para HOY
        // (Asumiendo que el admin crea eventos rango día a día o un evento puntual)
        return self::where('type', $type)
            ->whereDate('date', $today)
            ->exists();
    }
}