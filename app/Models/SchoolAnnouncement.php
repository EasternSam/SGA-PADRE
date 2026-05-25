<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolAnnouncement extends Model
{
    protected $fillable = [
        'academic_year_id', 'author_id', 'title', 'body',
        'type', 'priority', 'audience', 'grade_level_id', 'section_id',
        'publish_date', 'expiry_date', 'is_published',
        'requires_acknowledgment', 'attachment_path',
    ];

    protected $casts = [
        'publish_date'  => 'date',
        'expiry_date'   => 'date',
        'is_published'  => 'boolean',
        'requires_acknowledgment' => 'boolean',
    ];

    const TYPES = [
        'circular'     => '📜 Circular',
        'announcement' => '📢 Aviso',
        'alert'        => '🚨 Alerta',
        'event'        => '🎭 Evento',
        'memo'         => '📝 Memorándum',
    ];

    const PRIORITIES = [
        'normal'    => '🟢 Normal',
        'important' => '🟡 Importante',
        'urgent'    => '🔴 Urgente',
    ];

    const AUDIENCES = [
        'all'      => '👥 Todos',
        'teachers' => '👩‍🏫 Docentes',
        'parents'  => '👨‍👩‍👧 Padres/Tutores',
        'students' => '🎓 Estudiantes',
        'staff'    => '🏢 Personal Admin.',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(AnnouncementAcknowledgment::class, 'announcement_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function($q) use ($audience) {
            $q->where('audience', 'all')
              ->orWhere('audience', $audience);
        });
    }
}
