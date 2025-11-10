<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Llama al seeder de Roles y Permisos (esto crea los usuarios base)
        $this->call(RolesAndPermissionsSeeder::class);

        // --- AÑADIDO ---
        // Llama al nuevo seeder para crear datos de demostración
        $this->call(DemoDataSeeder::class);
        // --- FIN DE LO AÑADIDO ---
    }
}