<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'serial_number',
        'asset_tag',
        'category',
        'status',
        'classroom_id',
        'notes',
        'purchase_date',
        'cost',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'cost' => 'decimal:2',
    ];

    // Relación con Aula (Si es null, es Almacén)
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Helper para el color del estado
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Operativo' => 'green',
            'Defectuoso' => 'red',
            'En Reparación' => 'yellow',
            'Obsoleto' => 'gray',
            default => 'gray',
        };
    }

    // Helper para ubicación legible
    public function getLocationNameAttribute()
    {
        return $this->classroom ? $this->classroom->name : '📦 Almacén Central';
    }
}