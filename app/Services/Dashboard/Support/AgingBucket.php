<?php

namespace App\Services\Dashboard\Support;

/**
 * Antigüedad de una cuenta por cobrar según los días transcurridos desde su vencimiento.
 */
final class AgingBucket
{
    public const LABELS = [
        'current' => 'Por vencer',
        '1_30' => '1–30 días',
        '31_60' => '31–60 días',
        '61_90' => '61–90 días',
        '90_plus' => '+90 días',
    ];

    public static function for(?int $daysOverdue): string
    {
        return match (true) {
            $daysOverdue === null || $daysOverdue <= 0 => 'current',
            $daysOverdue <= 30 => '1_30',
            $daysOverdue <= 60 => '31_60',
            $daysOverdue <= 90 => '61_90',
            default => '90_plus',
        };
    }
}
