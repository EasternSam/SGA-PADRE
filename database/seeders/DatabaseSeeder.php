<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Solo crea roles, permisos y el usuario administrador.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permisos ───
        Permission::firstOrCreate(['name' => 'ver dashboard']);
        Permission::firstOrCreate(['name' => 'gestionar usuarios']);
        Permission::firstOrCreate(['name' => 'ver cursos']);
        Permission::firstOrCreate(['name' => 'gestionar cursos']);
        Permission::firstOrCreate(['name' => 'ver estudiantes']);

        // ─── Roles ───
        $roleAdmin        = Role::firstOrCreate(['name' => 'Admin']);
        $roleProfesor     = Role::firstOrCreate(['name' => 'Profesor']);
        $roleEstudiante   = Role::firstOrCreate(['name' => 'Estudiante']);
        $roleRegistro     = Role::firstOrCreate(['name' => 'Registro']);
        $roleContabilidad = Role::firstOrCreate(['name' => 'Contabilidad']);
        $roleCaja         = Role::firstOrCreate(['name' => 'Caja']);
        $roleSolicitante  = Role::firstOrCreate(['name' => 'Solicitante']);

        // ─── Asignar permisos ───
        $roleAdmin->givePermissionTo(Permission::all());
        $roleProfesor->givePermissionTo(['ver dashboard', 'ver cursos', 'ver estudiantes']);
        $roleEstudiante->givePermissionTo(['ver dashboard', 'ver cursos']);
        $roleSolicitante->givePermissionTo(['ver dashboard']);

        // ─── Usuario Admin único ───
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Administrador',
                'password' => bcrypt('Password'),
            ]
        );
        $admin->assignRole($roleAdmin);
    }
}