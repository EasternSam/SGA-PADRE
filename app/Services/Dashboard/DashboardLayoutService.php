<?php

namespace App\Services\Dashboard;

use App\Models\DashboardLayout;
use App\Models\User;

/**
 * Lee y guarda layouts del dashboard. Todo layout se sanea con el registry antes
 * de persistirse y al leerse (los permisos o features pueden cambiar con el tiempo).
 */
final class DashboardLayoutService
{
    private readonly WidgetRegistry $registry;

    public function __construct(?WidgetRegistry $registry = null)
    {
        $this->registry = $registry ?? DashboardWidgetCatalog::registry();
    }

    public function registry(): WidgetRegistry
    {
        return $this->registry;
    }

    /** @return list<string> */
    public function rolesOf(User $user): array
    {
        return $user->getRoleNames()->values()->all();
    }

    /** @return list<array{key: string, size: string}> */
    public function resolveFor(User $user): array
    {
        $roles = $this->rolesOf($user);
        $primary = $this->registry->primaryRole($roles);

        return $this->registry->resolve(
            DashboardLayout::forUser($user)->first()?->widgets,
            $primary !== null ? DashboardLayout::forRole($primary)->first()?->widgets : null,
            $roles,
        );
    }

    /** @return list<array{key: string, size: string}> */
    public function saveForUser(User $user, mixed $widgets): array
    {
        $clean = $this->registry->sanitize($widgets, $this->rolesOf($user));

        DashboardLayout::updateOrCreate(['user_id' => $user->getKey()], ['role' => null, 'widgets' => $clean]);

        return $clean;
    }

    /** Elimina el diseño personal: el usuario vuelve al predeterminado de su rol. */
    public function resetForUser(User $user): array
    {
        DashboardLayout::forUser($user)->delete();

        return $this->resolveFor($user);
    }

    /**
     * El diseño se sanea contra los permisos del rol destino, no contra los del
     * Admin que lo guarda: así nunca incluye widgets que ese rol no puede ver.
     *
     * @return list<array{key: string, size: string}>
     */
    public function saveRoleDefault(string $role, mixed $widgets): array
    {
        $clean = $this->registry->sanitize($widgets, [$role]);

        DashboardLayout::updateOrCreate(['user_id' => null, 'role' => $role], ['widgets' => $clean]);

        return $clean;
    }

    /** @return list<array{key: string, size: string}> Diseño de fábrica del rol. */
    public function clearRoleDefault(string $role): array
    {
        DashboardLayout::forRole($role)->delete();

        return $this->registry->codeDefaultFor([$role]);
    }
}
