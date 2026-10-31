<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<array{key: string, size: string}> $widgets
 */
class DashboardLayout extends Model
{
    protected $fillable = ['user_id', 'role', 'widgets'];

    protected $casts = [
        'widgets' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->whereNull('user_id')->where('role', $role);
    }
}
