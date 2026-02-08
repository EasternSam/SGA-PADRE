<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Obtiene el siguiente NCF formateado y avanza la secuencia de forma atómica.
     * Ej: E3100000001
     */
    public function getNextNcf()
    {
        // Usamos una transacción manual para bloquear la fila
        return DB::transaction(function () {
            // Bloqueamos la fila para lectura/escritura hasta que termine la transacción
            $sequence = NcfSequence::where('id', $this->id)->lockForUpdate()->first();

            if (!$sequence || !$sequence->is_active) {
                return null;
            }

            if ($sequence->current_sequence >= $sequence->limit_sequence) {
                return null; // Secuencia agotada
            }

            if ($sequence->expiration_date < now()) {
                return null; // Secuencia vencida
            }

            // Incrementamos
            $sequence->current_sequence++;
            $sequence->save(); // Guardamos inmediatamente dentro del lock

            // Formato e-CF estándar: Serie (1) + Tipo (2) + Secuencia (10)
            // Ejemplo: E + 31 + 0000000001
            return $sequence->series . $sequence->type_code . str_pad($sequence->current_sequence, 10, '0', STR_PAD_LEFT);
        });
    }
}