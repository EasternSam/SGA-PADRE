<?php

namespace App\Services\Dashboard\Support;

/**
 * Convierte las descripciones técnicas de activity_logs en frases legibles.
 */
final class ActivityHumanizer
{
    private const KINDS = [
        'payment' => 'payment',
        'student' => 'student',
        'enrollment' => 'enrollment',
        'inscripción' => 'enrollment',
        'courseschedule' => 'schedule',
    ];

    /** @return array{text: string, kind: string} */
    public static function describe(?string $description, ?string $action): array
    {
        $kind = self::kindOf($action);
        $text = trim((string) $description);

        if ($text === '') {
            return ['text' => 'Actividad del sistema', 'kind' => $kind];
        }

        if (preg_match('/^Creó registro en Payment: Monto (RD\$\s?[\d.,]+)\s*\(Estado: ([^)]+)\)/u', $text, $m)) {
            return ['text' => "Pago registrado: {$m[1]} ({$m[2]})", 'kind' => 'payment'];
        }

        if (preg_match('/^(Creó|Actualizó) registro en Student:\s*([^(]+)/u', $text, $m)) {
            $prefix = $m[1] === 'Creó' ? 'Nuevo estudiante' : 'Estudiante actualizado';

            return ['text' => $prefix . ': ' . trim($m[2]), 'kind' => 'student'];
        }

        return ['text' => $text, 'kind' => $kind];
    }

    private static function kindOf(?string $action): string
    {
        $subject = mb_strtolower(strtok(trim((string) $action), ' ') ?: '');

        return self::KINDS[$subject] ?? 'general';
    }
}
