<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'name',
        'full_name',
        'homeroom_teacher_id',
        'classroom_id',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'capacity'  => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Boot ────────────────────────────────────────────────

    protected static function booted()
    {
        // Auto-generar full_name al crear/actualizar
        static::saving(function ($section) {
            if ($section->gradeLevel) {
                $section->full_name = "{$section->gradeLevel->short_name} {$section->name}";
            }
        });
    }

    // ── Relaciones ──────────────────────────────────────────

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->capacity - $this->student_count);
    }

    public function isFull(): bool
    {
        return $this->available_slots <= 0;
    }
}
