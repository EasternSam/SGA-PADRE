<?php

namespace App\Enums;

/**
 * Estados canónicos para Inscripciones (Enrollments).
 * 
 * Uso: EnrollmentStatus::ACTIVE->value  => 'Cursando'
 *      EnrollmentStatus::values()       => ['Pendiente', 'Cursando', ...]
 *      EnrollmentStatus::isActive($val) => true/false
 */
enum EnrollmentStatus: string
{
    case PENDING   = 'Pendiente';
    case ACTIVE    = 'Cursando';
    case COMPLETED = 'Completado';
    case APPROVED  = 'Aprobado';
    case WITHDRAWN = 'Retirado';
    case CANCELLED = 'Cancelado';
    case EQUIVALENT = 'Equivalida';

    /**
     * Todos los valores posibles.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Verifica si un valor (en cualquier capitalización) es considerado "activo" (cursando).
     */
    public static function isActive(string $status): bool
    {
        return in_array(
            strtolower($status),
            ['cursando', 'activo', 'active', 'enrolled']
        );
    }

    /**
     * Verifica si un valor es considerado "completado/aprobado".
     */
    public static function isCompleted(string $status): bool
    {
        return in_array(
            strtolower($status),
            ['completado', 'aprobado', 'approved', 'completed', 'equivalida']
        );
    }

    /**
     * Verifica si un valor es considerado "pendiente".
     */
    public static function isPending(string $status): bool
    {
        return in_array(strtolower($status), ['pendiente', 'pending']);
    }

    /**
     * Verifica si un valor es "retirado/cancelado".
     */
    public static function isWithdrawn(string $status): bool
    {
        return in_array(strtolower($status), ['retirado', 'cancelado', 'withdrawn', 'cancelled']);
    }

    /**
     * Todos los posibles valores que representan "activo" (para whereIn).
     */
    public static function activeValues(): array
    {
        return ['Cursando', 'cursando', 'Activo', 'activo'];
    }

    /**
     * Todos los posibles valores que representan "completado" (para whereIn).
     */
    public static function completedValues(): array
    {
        return ['Completado', 'completado', 'Aprobado', 'aprobado', 'Equivalida'];
    }

    /**
     * Todos los posibles valores que representan "pendiente" (para whereIn).
     */
    public static function pendingValues(): array
    {
        return ['Pendiente', 'pendiente'];
    }

    /**
     * Valores "no pendiente" — útil para whereNotIn.
     */
    public static function notPendingValues(): array
    {
        return ['Cursando', 'Completado', 'Aprobado', 'Retirado', 'Cancelado', 'Equivalida'];
    }
}
