<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardLayoutService;
use App\Services\Dashboard\WidgetDefinition;
use App\Services\Dashboard\WidgetRegistry;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Dashboard modular. Envía el layout del usuario, el catálogo que puede usar y los
 * datos de cada widget: inmediatos para los que están en el layout y opcionales
 * (solo bajo demanda vía partial reload) para el resto de los permitidos.
 */
class AdminDashboardController extends Controller
{
    public function __construct(private readonly DashboardLayoutService $layouts)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $registry = $this->layouts->registry();
        $roles = $this->layouts->rolesOf($user);
        $layout = $this->layouts->resolveFor($user);
        $inLayout = array_column($layout, 'key');

        $props = [
            'layout' => $layout,
            'catalog' => $registry->catalogFor($roles),
            'canManageRoleDefaults' => $user->hasRole('Admin'),
            'dashboardRoles' => WidgetRegistry::DASHBOARD_ROLES,
            'currentDate' => now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
        ];

        foreach ($registry->availableFor($roles) as $key => $widget) {
            $resolver = fn () => $this->widgetData($widget, $user);
            $props["widget_{$key}"] = in_array($key, $inLayout, true) ? $resolver : Inertia::optional($resolver);
        }

        return Inertia::render('Admin/Dashboard', $props);
    }

    /**
     * Un widget que falla no tumba el dashboard: se reporta y el cliente muestra
     * un estado de error solo en esa tarjeta.
     *
     * @return array<string, mixed>
     */
    private function widgetData(WidgetDefinition $widget, User $user): array
    {
        if ($widget->provider === null) {
            return [];
        }

        try {
            return app($widget->provider)->data($user);
        } catch (Throwable $e) {
            report($e);

            return ['error' => true];
        }
    }
}
