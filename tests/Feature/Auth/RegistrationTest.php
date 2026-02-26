<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Seeding roles so we can assign 'Solicitante'
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $response = $this->post('/register', [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'test@example.com',
            'cedula' => '12345678901',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('applicant.portal', absolute: false));
    }
}
