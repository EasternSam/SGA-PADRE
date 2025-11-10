<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    protected $guarded = []; // Permitir asignación masiva

    /**
     * Un registro de actividad pertenece a un usuario (o es del sistema).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}