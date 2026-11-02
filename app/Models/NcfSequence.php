<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NcfSequence extends Model
{
    protected $fillable = [
        'name',
        'type_code',
        'series',
        'current_sequence',
        'limit_sequence',
        'expiration_date',
        'is_active',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'is_active' => 'boolean',
        'current_sequence' => 'integer',
        'limit_sequence' => 'integer',
    ];

    /**
     * Asegura que las secuencias base DGII existan en la base de datos.
     */
    public static function ensureDefaults(): void
    {
        $defaults = [
            ['type_code' => '31', 'series' => 'E', 'name' => 'Factura de Crédito Fiscal Electrónica (E31)', 'limit' => 10000],
            ['type_code' => '32', 'series' => 'E', 'name' => 'Factura de Consumo Electrónica (E32)', 'limit' => 50000],
            ['type_code' => '33', 'series' => 'E', 'name' => 'Nota de Débito Electrónica (E33)', 'limit' => 5000],
            ['type_code' => '34', 'series' => 'E', 'name' => 'Nota de Crédito Electrónica (E34)', 'limit' => 5000],
            ['type_code' => '41', 'series' => 'E', 'name' => 'Comprobante de Compras Electrónico (E41)', 'limit' => 5000],
            ['type_code' => '43', 'series' => 'E', 'name' => 'Gastos Menores Electrónico (E43)', 'limit' => 5000],
            ['type_code' => '44', 'series' => 'E', 'name' => 'Regímenes Especiales Electrónico (E44)', 'limit' => 5000],
            ['type_code' => '45', 'series' => 'E', 'name' => 'Gubernamental Electrónico (E45)', 'limit' => 5000],
        ];

        foreach ($defaults as $def) {
            if (!self::where('type_code', $def['type_code'])->exists()) {
                self::create([
                    'name' => $def['name'],
                    'type_code' => $def['type_code'],
                    'series' => $def['series'],
                    'current_sequence' => 0,
                    'limit_sequence' => $def['limit'],
                    'expiration_date' => '2028-12-31',
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Obtiene el siguiente NCF/e-NCF formateado y avanza la secuencia de forma atómica.
     * Serie E: E + Tipo(2) + Secuencia(10) -> 13 caracteres (Ej: E310000000001)
     * Serie B: B + Tipo(2) + Secuencia(8)  -> 11 caracteres (Ej: B0100000001)
     */
    public function getNextNcf(): ?string
    {
        return DB::transaction(function () {
            $sequence = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$sequence || !$sequence->is_active) {
                return null;
            }

            if ($sequence->current_sequence >= $sequence->limit_sequence) {
                return null;
            }

            if ($sequence->expiration_date && $sequence->expiration_date->endOfDay()->lt(now())) {
                return null;
            }

            $sequence->current_sequence++;
            $sequence->save();

            $series = strtoupper(trim((string) $sequence->series)) ?: 'E';
            $padLen = $series === 'B' ? 8 : 10;

            return $series . $sequence->type_code . str_pad((string) $sequence->current_sequence, $padLen, '0', STR_PAD_LEFT);
        });
    }
}
