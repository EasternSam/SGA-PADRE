<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'title',
        'description',
        'reserved_date',
        'start_time',
        'end_time',
        'created_by',
    ];

    protected $casts = [
        'reserved_date' => 'date',
        'start_time' => 'datetime', // Casting a datetime para facilitar formateo, aunque solo guarde hora
        'end_time' => 'datetime',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}