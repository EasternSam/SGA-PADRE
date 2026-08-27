<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ParentAccessToken extends Model
{
    protected $fillable = [
        'guardian_id', 'student_id', 'token', 'pin',
        'is_active', 'last_accessed_at',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Generate a unique token for parent access
     */
    public static function generateForStudent(int $guardianId, int $studentId): self
    {
        // Deactivate existing tokens
        static::where('guardian_id', $guardianId)
            ->where('student_id', $studentId)
            ->update(['is_active' => false]);

        return static::create([
            'guardian_id' => $guardianId,
            'student_id'  => $studentId,
            'token'       => Str::random(48),
            'pin'         => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'is_active'   => true,
        ]);
    }
}
