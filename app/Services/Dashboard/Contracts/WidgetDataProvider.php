<?php

namespace App\Services\Dashboard\Contracts;

use App\Models\User;

/**
 * Calcula los datos de un widget del dashboard. Solo se ejecuta cuando el widget
 * está en el layout del usuario (o se pide explícitamente con un partial reload).
 */
interface WidgetDataProvider
{
    /** @return array<string, mixed> */
    public function data(User $user): array;
}
