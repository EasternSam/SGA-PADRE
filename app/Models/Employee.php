<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'biometric_id',
        'position',
        'department',
        'contract_type',
        'base_salary',
        'hourly_rate',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'biometric_id', 'biometric_id');
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function events()
    {
        return $this->hasMany(EmployeeEvent::class)->orderByDesc('event_date')->orderByDesc('created_at');
    }
}
