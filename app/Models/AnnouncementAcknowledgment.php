<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementAcknowledgment extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'acknowledged_at'];

    protected $casts = ['acknowledged_at' => 'datetime'];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(SchoolAnnouncement::class, 'announcement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
