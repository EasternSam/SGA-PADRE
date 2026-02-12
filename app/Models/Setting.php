<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group', 'type'];

    /**
     * Obtener un valor de configuración.
     * Utiliza caché perpetua para evitar consultas a la BD en cada carga de página.
     * * Uso: Setting::val('wp_api_url', 'http://default.com')
     */
    public static function val($key, $default = null)
    {
        return Cache::rememberForever('sys_setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Actualizar o crear una configuración y limpiar su caché instantáneamente.
     */
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
        
        Cache::forget('sys_setting_' . $key); // Invalidar caché antiguo
        
        return $setting;
    }
}