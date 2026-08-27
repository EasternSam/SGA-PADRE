<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    protected $fillable = [
        'channel', 'type', 'subject', 'body',
        'sent_by', 'student_id', 'section_id',
        'recipients_count', 'status', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    const CHANNELS = [
        'whatsapp' => 'WhatsApp',
        'email'    => 'Email',
        'sms'      => 'SMS',
        'push'     => 'Push',
        'internal' => 'Interno',
    ];

    const TYPES = [
        'individual' => 'Individual',
        'section'    => 'Sección',
        'grade'      => 'Grado',
        'all'        => 'Todos',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
