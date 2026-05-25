<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectionSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'schedule',
    ];

    protected $casts = [
        'schedule' => 'array',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    // ── Helpers ─────────────────────────────────────────────

    /**
     * Nombre completo para display: "Matemáticas - 3ro A"
     */
    public function getDisplayNameAttribute(): string
    {
        $subjectName = $this->subject?->name ?? 'Asignatura';
        $sectionName = $this->section?->full_name ?? 'Sección';
        return "{$subjectName} - {$sectionName}";
    }
}
