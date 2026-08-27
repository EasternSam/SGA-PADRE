<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'area',
        'is_core',
        'weekly_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_core'      => 'boolean',
        'weekly_hours' => 'integer',
        'is_active'    => 'boolean',
    ];

    // ── Áreas curriculares MINERD ───────────────────────────

    const AREAS = [
        'lengua_espanola'    => 'Lengua Española',
        'matematicas'        => 'Matemáticas',
        'ciencias_naturaleza'=> 'Ciencias de la Naturaleza',
        'ciencias_sociales'  => 'Ciencias Sociales',
        'educacion_artistica'=> 'Educación Artística',
        'educacion_fisica'   => 'Educación Física',
        'formacion_humana'   => 'Formación Integral Humana y Religiosa',
        'lenguas_extranjeras'=> 'Lenguas Extranjeras',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_subject')
                    ->withPivot('weekly_hours')
                    ->withTimestamps();
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class);
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    public function scopeByArea($query, string $area)
    {
        return $query->where('area', $area);
    }
}
