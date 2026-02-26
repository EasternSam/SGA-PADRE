<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = ['key', 'value', 'group', 'type'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Helper para obtener valores rápidamente
    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Helper para guardar valores y limpiar caché
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
        Cache::forget("setting_{$key}");
    }

    /**
     * Alias para get() para compatibilidad con llamadas legacy o errores de tipeo.
     * Permite usar Setting::val('key')
     */
    public static function val($key, $default = null)
    {
        return self::get($key, $default);
    }
}