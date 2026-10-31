<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de forma. Qué widgets y tamaños son válidos para cada rol lo decide
 * WidgetRegistry::sanitize() en el servicio.
 */
class UpdateDashboardLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Las rutas ya exigen rol de dashboard (y Admin para defaults por rol).
    }

    public function rules(): array
    {
        return [
            'widgets' => ['present', 'array', 'max:50'],
            'widgets.*' => ['array'],
            'widgets.*.key' => ['required', 'string', 'max:64'],
            'widgets.*.size' => ['nullable', 'string', 'max:8'],
        ];
    }
}
