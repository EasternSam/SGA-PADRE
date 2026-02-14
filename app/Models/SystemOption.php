<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemOption extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    // Helper para obtener un valor rápidamente (con caché)
    public static function get($key, $default = null)
    {
        return Cache::remember("sys_opt_{$key}", 3600, function () use ($key, $default) {
            $option = self::where('key', $key)->first();
            return $option ? $option->value : $default;
        });
    }

    // Helper para guardar y limpiar caché
    public static function set($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("sys_opt_{$key}");
    }
}