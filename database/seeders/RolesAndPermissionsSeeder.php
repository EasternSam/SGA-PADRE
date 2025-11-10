<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Student; // <-- 1. IMPORTAR EL MODELO STUDENT

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        // (Podríamos añadir permisos específicos como 'view profile', 'view grades', etc.)
        // Por ahora, solo creamos los roles.

        // create roles
        // Usamos firstOrCreate para evitar errores si el seeder se corre múltiples veces
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleProfesor = Role::firstOrCreate(['name' => 'Profesor']);
        $roleEstudiante = Role::firstOrCreate(['name' => 'Estudiante']);

        // Crear usuario Administrador (con firstOrCreate)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // Crear usuario Profesor de prueba (con firstOrCreate)
        $profesor = User::firstOrCreate(
            ['email' => 'profesor@profesor.com'],
            [
                'name' => 'Profesor Prueba',
                'password' => bcrypt('password'),
            ]
        );
        $profesor->assignRole($roleProfesor);
        
        // --- SECCIÓN DE ESTUDIANTE CORREGIDA ---

        // 2. Crear usuario Estudiante de prueba (con firstOrCreate)
        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@estudiante.com'],
            [
                'name' => 'Estudiante Prueba',
                'password' => bcrypt('password'),
            ]
        );
        $estudiante->assignRole($roleEstudiante);

        // 3. Crear el PERFIL de estudiante y vincularlo al USUARIO
        // (Usamos la cédula para el firstOrCreate del perfil)
        Student::firstOrCreate(
            ['cedula' => '000-0000000-0'],
            [
                'user_id' => $estudiante->id, // <-- VINCULACIÓN
                'first_name' => 'Estudiante',
                'last_name' => 'Prueba',
                'email' => $estudiante->email,
                'phone' => '809-000-0000',
                'birth_date' => '2000-01-01',
                'gender' => 'Otro',
                'address' => 'Dirección de prueba',
                'is_minor' => false,
            ]
        );
    }
}