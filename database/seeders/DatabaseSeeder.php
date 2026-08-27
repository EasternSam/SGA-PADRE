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
     * Registra roles, permisos, y cuentas oficiales para cada departamento del colegio.
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

        // ─── Cuentas Oficiales para Roles ───

        // 1. Administrador General
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Administrador',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole($roleAdmin);
        }

        // 2. Profesor de Prueba
        $profesor = User::firstOrCreate(
            ['email' => 'profesor@colegio.edu.do'],
            [
                'name'     => 'Profesor de Prueba',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$profesor->hasRole('Profesor')) {
            $profesor->assignRole($roleProfesor);
        }

        // 3. Encargado de Registro
        $registro = User::firstOrCreate(
            ['email' => 'registro@colegio.edu.do'],
            [
                'name'     => 'Encargado de Registro',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$registro->hasRole('Registro')) {
            $registro->assignRole($roleRegistro);
        }

        // 4. Encargado de Contabilidad
        $contabilidad = User::firstOrCreate(
            ['email' => 'contabilidad@colegio.edu.do'],
            [
                'name'     => 'Encargado de Contabilidad',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$contabilidad->hasRole('Contabilidad')) {
            $contabilidad->assignRole($roleContabilidad);
        }

        // 5. Cajero Principal
        $caja = User::firstOrCreate(
            ['email' => 'caja@colegio.edu.do'],
            [
                'name'     => 'Cajero Principal',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$caja->hasRole('Caja')) {
            $caja->assignRole($roleCaja);
        }

        // 6. Estudiante de Prueba
        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@colegio.edu.do'],
            [
                'name'     => 'Estudiante de Prueba',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$estudiante->hasRole('Estudiante')) {
            $estudiante->assignRole($roleEstudiante);
        }

        // 7. Aspirante de Prueba (Admisiones)
        $solicitante = User::firstOrCreate(
            ['email' => 'solicitante@colegio.edu.do'],
            [
                'name'     => 'Aspirante de Prueba',
                'password' => bcrypt('Password'),
            ]
        );
        if (!$solicitante->hasRole('Solicitante')) {
            $solicitante->assignRole($roleSolicitante);
        }

        $this->command->info('Cuentas oficiales para todos los roles del colegio creadas correctamente');

        // ─── Datos de demostración ───
        $this->call(SchoolDemoSeeder::class);
    }
}