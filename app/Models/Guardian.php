<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'cedula', 'phone', 'phone_alt',
        'email', 'address', 'relationship', 'occupation', 'workplace',
        'is_emergency_contact',
    ];

    protected $casts = [
        'is_emergency_contact' => 'boolean',
    ];

    const RELATIONSHIPS = [
        'padre'  => 'Padre',
        'madre'  => 'Madre',
        'tutor'  => 'Tutor Legal',
        'abuelo' => 'Abuelo',
        'abuela' => 'Abuela',
        'tio'    => 'Tío',
        'tia'    => 'Tía',
        'otro'   => 'Otro',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getRelationshipLabelAttribute(): string
    {
        return self::RELATIONSHIPS[$this->relationship] ?? $this->relationship;
    }
}
