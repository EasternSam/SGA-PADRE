<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requires_payment',
        'payment_amount',
        'requires_enrolled_course',
        'requires_completed_course',
        'is_active',
    ];

    protected $casts = [
        'requires_payment' => 'boolean',
        'requires_enrolled_course' => 'boolean',
        'requires_completed_course' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(StudentRequest::class);
    }
}