<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin', 'web');
        Role::findOrCreate('Estudiante', 'web');
        Role::findOrCreate('Profesor', 'web');
        Role::findOrCreate('Registro', 'web');
        Role::findOrCreate('Contabilidad', 'web');
        Role::findOrCreate('Caja', 'web');
    }

    public function test_admin_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirectToRoute('admin.dashboard');
    }

    public function test_student_redirects_to_student_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Estudiante');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirectToRoute('student.dashboard');
    }

    public function test_teacher_redirects_to_teacher_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Profesor');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirectToRoute('teacher.dashboard');
    }

    public function test_registro_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Registro');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirectToRoute('admin.dashboard');
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_without_role_gets_403(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(403);
    }
}
