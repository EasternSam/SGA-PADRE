<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeBlock extends Model
{
    protected $fillable = [
        'academic_year_id', 'name', 'start_time', 'end_time', 'type', 'order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'class'    => '📚 Clase',
        'break'    => '☕ Recreo',
        'lunch'    => '🍽️ Almuerzo',
        'assembly' => '🎤 Acto/Formación',
    ];

    // Plantillas por tanda
    const TEMPLATES = [
        'matutina' => [
            ['name' => 'Formación', 'start_time' => '07:30', 'end_time' => '07:45', 'type' => 'assembly'],
            ['name' => '1er Bloque', 'start_time' => '07:45', 'end_time' => '08:30', 'type' => 'class'],
            ['name' => '2do Bloque', 'start_time' => '08:30', 'end_time' => '09:15', 'type' => 'class'],
            ['name' => 'Recreo', 'start_time' => '09:15', 'end_time' => '09:35', 'type' => 'break'],
            ['name' => '3er Bloque', 'start_time' => '09:35', 'end_time' => '10:20', 'type' => 'class'],
            ['name' => '4to Bloque', 'start_time' => '10:20', 'end_time' => '11:05', 'type' => 'class'],
            ['name' => '5to Bloque', 'start_time' => '11:05', 'end_time' => '11:50', 'type' => 'class'],
            ['name' => '6to Bloque', 'start_time' => '11:50', 'end_time' => '12:30', 'type' => 'class'],
        ],
        'vespertina' => [
            ['name' => 'Formación', 'start_time' => '13:30', 'end_time' => '13:45', 'type' => 'assembly'],
            ['name' => '1er Bloque', 'start_time' => '13:45', 'end_time' => '14:30', 'type' => 'class'],
            ['name' => '2do Bloque', 'start_time' => '14:30', 'end_time' => '15:15', 'type' => 'class'],
            ['name' => 'Recreo', 'start_time' => '15:15', 'end_time' => '15:35', 'type' => 'break'],
            ['name' => '3er Bloque', 'start_time' => '15:35', 'end_time' => '16:20', 'type' => 'class'],
            ['name' => '4to Bloque', 'start_time' => '16:20', 'end_time' => '17:05', 'type' => 'class'],
            ['name' => '5to Bloque', 'start_time' => '17:05', 'end_time' => '17:30', 'type' => 'class'],
        ],
        'jornada_extendida' => [
            ['name' => 'Formación', 'start_time' => '08:00', 'end_time' => '08:15', 'type' => 'assembly'],
            ['name' => '1er Bloque', 'start_time' => '08:15', 'end_time' => '09:00', 'type' => 'class'],
            ['name' => '2do Bloque', 'start_time' => '09:00', 'end_time' => '09:45', 'type' => 'class'],
            ['name' => 'Recreo', 'start_time' => '09:45', 'end_time' => '10:00', 'type' => 'break'],
            ['name' => '3er Bloque', 'start_time' => '10:00', 'end_time' => '10:45', 'type' => 'class'],
            ['name' => '4to Bloque', 'start_time' => '10:45', 'end_time' => '11:30', 'type' => 'class'],
            ['name' => 'Almuerzo', 'start_time' => '11:30', 'end_time' => '12:30', 'type' => 'lunch'],
            ['name' => '5to Bloque', 'start_time' => '12:30', 'end_time' => '13:15', 'type' => 'class'],
            ['name' => '6to Bloque', 'start_time' => '13:15', 'end_time' => '14:00', 'type' => 'class'],
            ['name' => 'Recreo', 'start_time' => '14:00', 'end_time' => '14:15', 'type' => 'break'],
            ['name' => '7mo Bloque', 'start_time' => '14:15', 'end_time' => '15:00', 'type' => 'class'],
            ['name' => '8vo Bloque', 'start_time' => '15:00', 'end_time' => '15:45', 'type' => 'class'],
            ['name' => '9no Bloque', 'start_time' => '15:45', 'end_time' => '16:00', 'type' => 'class'],
        ],
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SchoolSchedule::class);
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeClassOnly($query)
    {
        return $query->where('type', 'class');
    }
}
