<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolConfig extends Model
{
    protected $table = 'school_config';

    protected $fillable = [
        'school_name', 'minerd_code', 'rnc', 'regional', 'district',
        'shift', 'school_type', 'level', 'director_name', 'director_cedula',
        'address', 'city', 'province', 'phone', 'email', 'website',
        'logo_path', 'motto', 'extra_config',
    ];

    protected $casts = [
        'extra_config' => 'array',
    ];

    const SHIFTS = [
        'matutina'          => 'Matutina (7:30 AM - 12:30 PM)',
        'vespertina'        => 'Vespertina (1:30 PM - 5:30 PM)',
        'jornada_extendida' => 'Jornada Escolar Extendida (8:00 AM - 4:00 PM)',
        'nocturna'          => 'Nocturna',
    ];

    const SCHOOL_TYPES = [
        'publico'     => 'Público',
        'privado'     => 'Privado',
        'semioficial' => 'Semi-oficial',
    ];

    const LEVELS = [
        'inicial'             => 'Nivel Inicial',
        'primario'            => 'Nivel Primario',
        'secundario'          => 'Nivel Secundario',
        'primario_secundario' => 'Primario y Secundario',
    ];

    const REGIONALS = [
        '01' => '01 - Barahona',
        '02' => '02 - San Juan de la Maguana',
        '03' => '03 - Azua',
        '04' => '04 - San Cristóbal',
        '05' => '05 - San Pedro de Macorís',
        '06' => '06 - La Vega',
        '07' => '07 - San Francisco de Macorís',
        '08' => '08 - Santiago',
        '09' => '09 - Mao',
        '10' => '10 - Santo Domingo (Norte)',
        '11' => '11 - Puerto Plata',
        '12' => '12 - Higüey',
        '13' => '13 - Monte Cristi',
        '14' => '14 - Nagua',
        '15' => '15 - Santo Domingo (Distrito Nacional)',
        '16' => '16 - Cotuí',
        '17' => '17 - Monte Plata',
        '18' => '18 - Neyba',
    ];

    const PROVINCES = [
        'Azua', 'Bahoruco', 'Barahona', 'Dajabón', 'Distrito Nacional',
        'Duarte', 'Elías Piña', 'El Seibo', 'Espaillat', 'Hato Mayor',
        'Hermanas Mirabal', 'Independencia', 'La Altagracia', 'La Romana',
        'La Vega', 'María Trinidad Sánchez', 'Monseñor Nouel', 'Monte Cristi',
        'Monte Plata', 'Pedernales', 'Peravia', 'Puerto Plata',
        'Samaná', 'San Cristóbal', 'San José de Ocoa', 'San Juan',
        'San Pedro de Macorís', 'Sánchez Ramírez', 'Santiago',
        'Santiago Rodríguez', 'Santo Domingo', 'Valverde',
    ];

    /**
     * Get singleton instance.
     */
    public static function current(): ?self
    {
        return static::first();
    }

    /**
     * Get or create config.
     */
    public static function getOrCreate(): self
    {
        return static::firstOrCreate([], [
            'school_name' => config('app.name', 'Mi Escuela'),
        ]);
    }
}
