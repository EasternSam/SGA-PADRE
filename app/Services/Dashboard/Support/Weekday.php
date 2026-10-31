<?php

namespace App\Services\Dashboard\Support;

/**
 * Días lectivos tal como se guardan en course_schedules.days_of_week
 * (JSON con escapes \u00e9, a veces sin tildes o con otra capitalización).
 */
final class Weekday
{
    /** Días con clases, en orden de la semana académica. */
    public const SCHOOL_DAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public const SHORT = [
        'Lunes' => 'Lun', 'Martes' => 'Mar', 'Miércoles' => 'Mié',
        'Jueves' => 'Jue', 'Viernes' => 'Vie', 'Sábado' => 'Sáb', 'Domingo' => 'Dom',
    ];

    /** Índices de Carbon::dayOfWeek (0 = domingo). */
    private const BY_DAY_OF_WEEK = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public static function normalize(string $day): ?string
    {
        $key = strtr(mb_strtolower(trim($day)), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        foreach (self::BY_DAY_OF_WEEK as $canonical) {
            if (strtr(mb_strtolower($canonical), ['á' => 'a', 'é' => 'e']) === $key) {
                return $canonical;
            }
        }

        return null;
    }

    /** @return list<string> Días canónicos únicos, en el orden recibido. */
    public static function decode(mixed $value): array
    {
        $days = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : null);

        if (! is_array($days)) {
            return [];
        }

        $clean = [];
        foreach ($days as $day) {
            $canonical = is_string($day) ? self::normalize($day) : null;
            if ($canonical !== null && ! in_array($canonical, $clean, true)) {
                $clean[] = $canonical;
            }
        }

        return $clean;
    }

    public static function fromDayOfWeek(int $dayOfWeek): string
    {
        return self::BY_DAY_OF_WEEK[$dayOfWeek % 7];
    }
}
