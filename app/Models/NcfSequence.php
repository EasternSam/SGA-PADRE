<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcfSequence extends Model
{
    protected $fillable = [
        'name', 'type_code', 'series', 
        'current_sequence', 'limit_sequence', 
        'expiration_date', 'is_active'
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * Obtiene el siguiente NCF formateado y avanza la secuencia.
     * Ej: E3100000001
     */
    public function getNextNcf()
    {
        if ($this->current_sequence >= $this->limit_sequence) {
            return null; // Secuencia agotada
        }

        if ($this->expiration_date < now()) {
            return null; // Secuencia vencida
        }

        $this->increment('current_sequence');
        
        // Formato estándar DGII: Serie (1) + Tipo (2) + Secuencia (10) = 13 caracteres (para e-CF)
        // Nota: e-CF usa E + Tipo (2) + Secuencia (10) = 13 caracteres.
        // Formato: E310000000001
        
        return $this->series . $this->type_code . str_pad($this->current_sequence, 10, '0', STR_PAD_LEFT);
    }
}