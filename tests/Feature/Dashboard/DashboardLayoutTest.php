<?php

namespace Tests\Feature\Dashboard;

use App\Models\DashboardLayout;
use App\Models\User;
use App\Services\Dashboard\DashboardWidgetCatalog;
use App\Services\Dashboard\WidgetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Persistencia del dashboard modular: cada usuario guarda su diseño, el servidor
 * lo sanea según sus roles y solo un Admin define los diseños por defecto por rol.
 */
class DashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Registro', 'Contabilidad', 'Caja', 'Estudiante'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        // Aísla las pruebas del plan SaaS activo: todas las features encendidas.
        $this->app->bind(WidgetRegistry::class, fn () => new WidgetRegistry(
            DashboardWidgetCatalog::definitions(),
            DashboardWidgetCatalog::roleDefaults(),
            fn () => true,
        ));
    }

    private function userWithRole(string $role): User
    {
        return tap(User::factory()->create(), fn (User $user) => $user->assignRole($role));
    }

    public function test_a_user_saves_a_layout_and_gets_it_back_sanitized(): void
    {
        $cashier = $this->userWithRole('Caja');

        $this->actingAs($cashier)
            ->putJson('/admin/dashboard/layout', ['widgets' => [
                ['key' => 'finance_today', 'size' => 'md'],
                ['key' => 'recent_activity', 'size' => 'sm'], // solo Admin
                ['key' => 'kpis', 'size' => 'sm'],            // tamaño no permitido
            ]])
            ->assertOk()
            ->assertExactJson(['layout' => [
                ['key' => 'finance_today', 'size' => 'md'],
                ['key' => 'kpis', 'size' => 'xl'],
            ]]);

        $this->assertSame(
            [['key' => 'finance_today', 'size' => 'md'], ['key' => 'kpis', 'size' => 'xl']],
            DashboardLayout::forUser($cashier)->first()->widgets
        );
    }

    public function test_an_empty_layout_can_be_saved(): void
    {
        $user = $this->userWithRole('Registro');

        $this->actingAs($user)->putJson('/admin/dashboard/layout', ['widgets' => []])
            ->assertOk()
            ->assertExactJson(['layout' => []]);
    }

    public function test_the_payload_shape_is_validated(): void
    {
        $user = $this->userWithRole('Registro');

        $this->actingAs($user)->putJson('/admin/dashboard/layout', [])->assertUnprocessable();
        $this->actingAs($user)->putJson('/admin/dashboard/layout', ['widgets' => 'kpis'])->assertUnprocessable();
    }

    public function test_reset_returns_the_role_default(): void
    {
        $user = $this->userWithRole('Caja');
        DashboardLayout::create(['user_id' => $user->id, 'widgets' => []]);

        $this->actingAs($user)->deleteJson('/admin/dashboard/layout')
            ->assertOk()
            ->assertExactJson(['layout' => DashboardWidgetCatalog::roleDefaults()['Caja']]);

        $this->assertNull(DashboardLayout::forUser($user)->first());
    }

    public function test_users_without_dashboard_role_cannot_save(): void
    {
        $this->actingAs($this->userWithRole('Estudiante'))
            ->putJson('/admin/dashboard/layout', ['widgets' => []])
            ->assertForbidden();
    }

    public function test_only_admin_can_define_role_defaults(): void
    {
        $this->actingAs($this->userWithRole('Registro'))
            ->putJson('/admin/dashboard/layout/defaults/Caja', ['widgets' => []])
            ->assertForbidden();
    }

    public function test_role_defaults_are_sanitized_against_the_target_role(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->putJson('/admin/dashboard/layout/defaults/Caja', ['widgets' => [
                ['key' => 'recent_activity', 'size' => 'sm'], // Caja no lo ve
                ['key' => 'finance_today', 'size' => 'md'],
            ]])
            ->assertOk()
            ->assertExactJson(['role' => 'Caja', 'layout' => [['key' => 'finance_today', 'size' => 'md']]]);

        $this->actingAs($this->userWithRole('Caja'))
            ->get('/admin/dashboard')
            ->assertInertia(fn ($page) => $page->where('layout', [['key' => 'finance_today', 'size' => 'md']]));
    }

    public function test_role_defaults_reject_roles_outside_the_dashboard(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->putJson('/admin/dashboard/layout/defaults/Estudiante', ['widgets' => []])
            ->assertNotFound();
    }

    public function test_the_dashboard_only_ships_data_for_widgets_in_the_layout(): void
    {
        $cashier = $this->userWithRole('Caja');
        DashboardLayout::create(['user_id' => $cashier->id, 'widgets' => [['key' => 'quick_actions', 'size' => 'sm']]]);

        $this->actingAs($cashier)->get('/admin/dashboard')
            ->assertInertia(fn ($page) => $page
                ->has('widget_quick_actions')
                ->missing('widget_kpis')
                ->missing('widget_recent_activity'));
    }
}
