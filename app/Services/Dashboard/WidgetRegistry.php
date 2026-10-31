<?php

namespace App\Services\Dashboard;

use Closure;
use InvalidArgumentException;

/**
 * Catálogo de widgets resuelto para un contexto (features del plan activas).
 *
 * Lógica pura: decide qué widgets ve cada conjunto de roles y convierte cualquier
 * layout recibido del cliente en uno válido. El servidor es la única fuente de verdad.
 */
final class WidgetRegistry
{
    public const SIZES = ['sm', 'md', 'lg', 'xl'];

    /** Roles con acceso al dashboard administrativo, de mayor a menor prioridad. */
    public const DASHBOARD_ROLES = ['Admin', 'Registro', 'Contabilidad', 'Caja'];

    /** @var array<string, WidgetDefinition> */
    private array $definitions = [];

    /**
     * @param  iterable<WidgetDefinition>  $definitions  En el orden en que se listan en la biblioteca.
     * @param  array<string, list<array{key: string, size?: string}>>  $roleDefaults  Diseños por defecto definidos en código.
     * @param  Closure(string): bool  $featureEnabled
     */
    public function __construct(
        iterable $definitions,
        private readonly array $roleDefaults,
        private readonly Closure $featureEnabled,
    ) {
        foreach ($definitions as $definition) {
            if (isset($this->definitions[$definition->key])) {
                throw new InvalidArgumentException("Widget duplicado en el catálogo: '{$definition->key}'.");
            }
            $this->definitions[$definition->key] = $definition;
        }
    }

    public function find(string $key): ?WidgetDefinition
    {
        return $this->definitions[$key] ?? null;
    }

    /**
     * @param  list<string>  $roles
     * @return array<string, WidgetDefinition>
     */
    public function availableFor(array $roles): array
    {
        return array_filter(
            $this->definitions,
            fn (WidgetDefinition $widget) => $widget->visibleTo($roles)
                && ($widget->feature === null || ($this->featureEnabled)($widget->feature)),
        );
    }

    /**
     * @param  list<string>  $roles
     * @return list<array{key: string, title: string, description: string, category: string, sizes: list<string>, defaultSize: string}>
     */
    public function catalogFor(array $roles): array
    {
        return array_values(array_map(
            fn (WidgetDefinition $widget) => $widget->toArray(),
            $this->availableFor($roles),
        ));
    }

    /**
     * Normaliza un layout: descarta entradas mal formadas, widgets desconocidos o sin
     * acceso y duplicados; corrige tamaños inválidos. Conserva el orden recibido.
     *
     * @param  list<string>  $roles
     * @return list<array{key: string, size: string}>
     */
    public function sanitize(mixed $widgets, array $roles): array
    {
        if (! is_array($widgets)) {
            return [];
        }

        $available = $this->availableFor($roles);
        $clean = [];

        foreach ($widgets as $item) {
            if (! is_array($item) || ! isset($item['key']) || ! is_string($item['key'])) {
                continue;
            }

            $definition = $available[$item['key']] ?? null;
            if ($definition === null || isset($clean[$definition->key])) {
                continue;
            }

            $size = $item['size'] ?? null;
            $clean[$definition->key] = [
                'key' => $definition->key,
                'size' => $definition->allowsSize($size) ? $size : $definition->defaultSize,
            ];
        }

        return array_values($clean);
    }

    /** @param  list<string>  $roles */
    public function primaryRole(array $roles): ?string
    {
        foreach (self::DASHBOARD_ROLES as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Diseño de fábrica: el default del rol principal o, si no existe, todos los
     * widgets disponibles con su tamaño por defecto.
     *
     * @param  list<string>  $roles
     * @return list<array{key: string, size: string}>
     */
    public function codeDefaultFor(array $roles): array
    {
        $primary = $this->primaryRole($roles);

        if ($primary !== null && isset($this->roleDefaults[$primary])) {
            return $this->sanitize($this->roleDefaults[$primary], $roles);
        }

        return array_values(array_map(
            fn (WidgetDefinition $widget) => ['key' => $widget->key, 'size' => $widget->defaultSize],
            $this->availableFor($roles),
        ));
    }

    /**
     * Layout efectivo. Un layout de usuario vacío ([]) es una elección válida y se
     * respeta; `null` significa "nunca personalizado".
     *
     * @param  list<string>  $roles
     * @return list<array{key: string, size: string}>
     */
    public function resolve(?array $userLayout, ?array $storedRoleDefault, array $roles): array
    {
        if ($userLayout !== null) {
            return $this->sanitize($userLayout, $roles);
        }

        if ($storedRoleDefault !== null) {
            return $this->sanitize($storedRoleDefault, $roles);
        }

        return $this->codeDefaultFor($roles);
    }
}
