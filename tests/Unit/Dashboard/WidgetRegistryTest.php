<?php

namespace Tests\Unit\Dashboard;

use App\Services\Dashboard\DashboardWidgetCatalog;
use App\Services\Dashboard\WidgetDefinition;
use App\Services\Dashboard\WidgetRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * El registry decide qué widgets puede ver cada usuario y limpia cualquier layout
 * que llegue del cliente. Es lógica pura: no toca BD ni el contenedor de Laravel.
 */
class WidgetRegistryTest extends TestCase
{
    private function registry(array $enabledFeatures = ['academic', 'finance'], array $roleDefaults = []): WidgetRegistry
    {
        $definitions = [
            new WidgetDefinition('kpis', 'Indicadores', 'KPIs', 'general', ['Admin', 'Registro', 'Caja'], ['lg', 'xl'], 'xl'),
            new WidgetDefinition('enrollment', 'Inscripciones', 'Serie', 'academico', ['Admin', 'Registro'], ['md', 'lg'], 'md', 'academic'),
            new WidgetDefinition('cash', 'Cobros', 'Caja del día', 'finanzas', ['Admin', 'Caja'], ['sm', 'md'], 'sm', 'finance'),
            new WidgetDefinition('audit', 'Auditoría', 'Logs', 'general', ['Admin'], ['sm', 'md'], 'sm'),
        ];

        return new WidgetRegistry(
            $definitions,
            $roleDefaults,
            fn (string $feature) => in_array($feature, $enabledFeatures, true),
        );
    }

    public function test_available_widgets_are_filtered_by_role(): void
    {
        $keys = array_keys($this->registry()->availableFor(['Caja']));

        $this->assertSame(['kpis', 'cash'], $keys);
    }

    public function test_widgets_whose_feature_is_disabled_are_not_available(): void
    {
        $keys = array_keys($this->registry(enabledFeatures: ['academic'])->availableFor(['Admin']));

        $this->assertSame(['kpis', 'enrollment', 'audit'], $keys);
    }

    public function test_a_user_with_several_roles_gets_the_union_of_widgets(): void
    {
        $keys = array_keys($this->registry()->availableFor(['Registro', 'Caja']));

        $this->assertSame(['kpis', 'enrollment', 'cash'], $keys);
    }

    public function test_sanitize_drops_unknown_and_unauthorized_widgets(): void
    {
        $clean = $this->registry()->sanitize([
            ['key' => 'kpis', 'size' => 'xl'],
            ['key' => 'does-not-exist', 'size' => 'md'],
            ['key' => 'audit', 'size' => 'sm'], // solo Admin
        ], ['Registro']);

        $this->assertSame([['key' => 'kpis', 'size' => 'xl']], $clean);
    }

    public function test_sanitize_keeps_order_and_removes_duplicates(): void
    {
        $clean = $this->registry()->sanitize([
            ['key' => 'audit', 'size' => 'md'],
            ['key' => 'kpis', 'size' => 'lg'],
            ['key' => 'audit', 'size' => 'sm'],
        ], ['Admin']);

        $this->assertSame([
            ['key' => 'audit', 'size' => 'md'],
            ['key' => 'kpis', 'size' => 'lg'],
        ], $clean);
    }

    public function test_sanitize_replaces_a_size_the_widget_does_not_allow_with_its_default(): void
    {
        $clean = $this->registry()->sanitize([
            ['key' => 'kpis', 'size' => 'sm'],      // kpis no admite sm
            ['key' => 'audit', 'size' => 'gigante'], // tamaño inexistente
            ['key' => 'cash'],                       // sin tamaño
        ], ['Admin']);

        $this->assertSame([
            ['key' => 'kpis', 'size' => 'xl'],
            ['key' => 'audit', 'size' => 'sm'],
            ['key' => 'cash', 'size' => 'sm'],
        ], $clean);
    }

    public function test_sanitize_ignores_malformed_input(): void
    {
        $registry = $this->registry();

        $this->assertSame([], $registry->sanitize('not-an-array', ['Admin']));
        $this->assertSame([], $registry->sanitize(null, ['Admin']));
        $this->assertSame(
            [['key' => 'kpis', 'size' => 'xl']],
            $registry->sanitize([42, 'kpis', ['size' => 'md'], ['key' => ['x']], ['key' => 'kpis', 'size' => 'xl', 'evil' => '<script>']], ['Admin'])
        );
    }

    public function test_an_empty_layout_is_a_valid_choice(): void
    {
        $this->assertSame([], $this->registry()->sanitize([], ['Admin']));
    }

    public function test_primary_role_follows_the_configured_priority(): void
    {
        $registry = $this->registry();

        $this->assertSame('Admin', $registry->primaryRole(['Caja', 'Admin']));
        $this->assertSame('Registro', $registry->primaryRole(['Caja', 'Registro']));
        $this->assertNull($registry->primaryRole(['Estudiante']));
    }

    public function test_code_default_uses_the_primary_role_default_and_is_sanitized(): void
    {
        $registry = $this->registry(roleDefaults: [
            'Caja' => [['key' => 'cash', 'size' => 'md'], ['key' => 'audit', 'size' => 'sm'], ['key' => 'kpis', 'size' => 'xl']],
        ]);

        $this->assertSame(
            [['key' => 'cash', 'size' => 'md'], ['key' => 'kpis', 'size' => 'xl']],
            $registry->codeDefaultFor(['Caja'])
        );
    }

    public function test_code_default_falls_back_to_every_available_widget(): void
    {
        $this->assertSame(
            [['key' => 'kpis', 'size' => 'xl'], ['key' => 'enrollment', 'size' => 'md']],
            $this->registry()->codeDefaultFor(['Registro'])
        );
    }

    public function test_a_definition_rejects_a_default_size_it_does_not_allow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WidgetDefinition('bad', 'Bad', 'Bad', 'general', ['Admin'], ['sm'], 'xl');
    }

    public function test_a_definition_rejects_unknown_sizes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WidgetDefinition('bad', 'Bad', 'Bad', 'general', ['Admin'], ['sm', 'huge'], 'sm');
    }

    public function test_the_registry_rejects_duplicate_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WidgetRegistry([
            new WidgetDefinition('kpis', 'A', 'A', 'general', ['Admin'], ['xl'], 'xl'),
            new WidgetDefinition('kpis', 'B', 'B', 'general', ['Admin'], ['xl'], 'xl'),
        ], [], fn () => true);
    }

    public function test_public_catalog_payload_does_not_leak_server_internals(): void
    {
        $payload = $this->registry()->catalogFor(['Admin']);

        $this->assertSame(
            ['key', 'title', 'description', 'category', 'sizes', 'defaultSize'],
            array_keys($payload[0])
        );
    }

    public function test_resolve_prefers_the_user_layout(): void
    {
        $layout = $this->registry()->resolve(
            [['key' => 'audit', 'size' => 'md'], ['key' => 'cash', 'size' => 'xl']],
            [['key' => 'kpis', 'size' => 'xl']],
            ['Admin'],
        );

        $this->assertSame([['key' => 'audit', 'size' => 'md'], ['key' => 'cash', 'size' => 'sm']], $layout);
    }

    public function test_resolve_respects_an_intentionally_empty_user_layout(): void
    {
        $this->assertSame([], $this->registry()->resolve([], [['key' => 'kpis', 'size' => 'xl']], ['Admin']));
    }

    public function test_resolve_falls_back_to_the_stored_role_default_sanitized_for_the_user(): void
    {
        $layout = $this->registry()->resolve(
            null,
            [['key' => 'cash', 'size' => 'md'], ['key' => 'audit', 'size' => 'sm']],
            ['Caja'],
        );

        $this->assertSame([['key' => 'cash', 'size' => 'md']], $layout);
    }

    public function test_resolve_falls_back_to_the_code_default_when_nothing_is_stored(): void
    {
        $this->assertSame(
            [['key' => 'kpis', 'size' => 'xl'], ['key' => 'enrollment', 'size' => 'md']],
            $this->registry()->resolve(null, null, ['Registro'])
        );
    }

    /**
     * Integridad del catálogo real: los defaults por rol deben referirse a widgets
     * existentes, con tamaños permitidos y accesibles para ese rol.
     */
    public function test_production_catalog_role_defaults_are_consistent(): void
    {
        $registry = new WidgetRegistry(
            DashboardWidgetCatalog::definitions(),
            DashboardWidgetCatalog::roleDefaults(),
            fn () => true,
        );

        foreach (DashboardWidgetCatalog::roleDefaults() as $role => $widgets) {
            $this->assertContains($role, WidgetRegistry::DASHBOARD_ROLES, "Rol desconocido: {$role}");
            $this->assertNotEmpty($widgets, "El rol {$role} no tiene diseño por defecto");
            $this->assertSame(
                $widgets,
                $registry->sanitize($widgets, [$role]),
                "El diseño por defecto de {$role} contiene widgets sin acceso, duplicados o tamaños inválidos"
            );
        }
    }

    public function test_production_catalog_gives_every_dashboard_role_at_least_one_widget(): void
    {
        $registry = new WidgetRegistry(DashboardWidgetCatalog::definitions(), [], fn () => true);

        foreach (WidgetRegistry::DASHBOARD_ROLES as $role) {
            $this->assertNotEmpty($registry->availableFor([$role]), "{$role} no tiene widgets disponibles");
        }
    }
}
