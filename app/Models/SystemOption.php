<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemOption extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group']; // Added 'group' to fillable as it was used in previous context

    // Helper para obtener un valor rápidamente (con caché)
    public static function get($key, $default = null)
    {
        return Cache::remember("sys_opt_{$key}", 3600, function () use ($key, $default) {
            $option = self::where('key', $key)->first();
            return $option ? $option->value : $default;
        });
    }

    // Helper para guardar y limpiar caché
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group, // Ensure these columns exist in your DB or remove if not needed
                'type' => $type
            ]
        );
        Cache::forget("sys_opt_{$key}");
    }

    /**
     * Alias for get() to maintain compatibility with generated code.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getOption($key, $default = null)
    {
        return self::get($key, $default);
    }

    /**
     * Alias for set() to maintain compatibility with generated code.
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $type
     * @return void
     */
    public static function setOption($key, $value, $group = 'general', $type = 'string')
    {
        self::set($key, $value, $group, $type);
    }
}