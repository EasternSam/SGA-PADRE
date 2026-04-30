<?php

namespace App\Enums;

/**
 * Estados canónicos para Pagos (Payments).
 * 
 * Uso: PaymentStatus::COMPLETED->value => 'Completado'
 *      PaymentStatus::isPaid($val)     => true/false
 */
enum PaymentStatus: string
{
    case PENDING   = 'Pendiente';
    case COMPLETED = 'Completado';
    case FAILED    = 'Fallido';
    case CANCELLED = 'Cancelado';
    case REFUNDED  = 'Reembolsado';

    /**
     * Todos los valores posibles.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Verifica si un valor (en cualquier capitalización/idioma) es considerado "pagado".
     */
    public static function isPaid(string $status): bool
    {
        return in_array(strtolower($status), [
            'completado', 'pagado', 'aprobado', 'paid', 
            'succeeded', 'active', 'activo', 'completed'
        ]);
    }

    /**
     * Verifica si un valor es considerado "pendiente".
     */
    public static function isPending(string $status): bool
    {
        return in_array(strtolower($status), ['pendiente', 'pending']);
    }

    /**
     * Valores para whereIn de pagos completados (compatibles con datos existentes).
     */
    public static function paidValues(): array
    {
        return ['Completado', 'Pagado', 'Aprobado', 'Succeeded', 'Paid'];
    }

    /**
     * Valores para whereIn de pagos pendientes.
     */
    public static function pendingValues(): array
    {
        return ['Pendiente', 'pendiente', 'pending'];
    }
}
