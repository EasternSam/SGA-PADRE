<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'event_date',
        'amount',
        'score',
        'end_date',
        'description',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'score' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
