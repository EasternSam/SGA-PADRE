<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Student;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 0. CREAR PERMISOS (Básicos para el sistema)
        // Usamos firstOrCreate para no duplicar si se corre el seeder varias veces
        Permission::firstOrCreate(['name' => 'ver dashboard']);
        Permission::firstOrCreate(['name' => 'gestionar usuarios']);
        Permission::firstOrCreate(['name' => 'ver cursos']);
        Permission::firstOrCreate(['name' => 'gestionar cursos']);
        Permission::firstOrCreate(['name' => 'ver estudiantes']);

        // 1. CREAR ROLES
        
        // Roles Existentes
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleProfesor = Role::firstOrCreate(['name' => 'Profesor']);
        $roleEstudiante = Role::firstOrCreate(['name' => 'Estudiante']);

        // --- NUEVOS ROLES POR DEPARTAMENTO ---
        $roleRegistro = Role::firstOrCreate(['name' => 'Registro']);       // Control Académico
        $roleContabilidad = Role::firstOrCreate(['name' => 'Contabilidad']); // Reportes Financieros
        $roleCaja = Role::firstOrCreate(['name' => 'Caja']);               // Cobros diarios
        
        // --- NUEVO ROL SOLICITANTE (Para Admisiones) ---
        $roleSolicitante = Role::firstOrCreate(['name' => 'Solicitante']);

        // 1.1 ASIGNAR PERMISOS A ROLES
        $roleAdmin->givePermissionTo(Permission::all());
        $roleProfesor->givePermissionTo(['ver dashboard', 'ver cursos', 'ver estudiantes']);
        $roleEstudiante->givePermissionTo(['ver dashboard', 'ver cursos']);
        $roleSolicitante->givePermissionTo(['ver dashboard']); // Permiso mínimo para ver su portal

        // 2. CREAR USUARIOS DE PRUEBA (Opcional, para desarrollo)

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin General',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // Profesor
        $profesor = User::firstOrCreate(
            ['email' => 'profesor@profesor.com'],
            [
                'name' => 'Profesor Prueba',
                'password' => bcrypt('password'),
            ]
        );
        $profesor->assignRole($roleProfesor);
        
        // Estudiante
        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@estudiante.com'],
            [
                'name' => 'Estudiante Prueba',
                'password' => bcrypt('password'),
            ]
        );
        $estudiante->assignRole($roleEstudiante);

        // Crear perfil de estudiante vinculado
        Student::firstOrCreate(
            ['cedula' => '000-0000000-0'],
            [
                'user_id' => $estudiante->id,
                'first_name' => 'Estudiante',
                'last_name' => 'Prueba',
                'email' => $estudiante->email,
                'mobile_phone' => '809-000-0000',
                'birth_date' => '2000-01-01',
                'gender' => 'Otro',
                'address' => 'Dirección de prueba',
                'is_minor' => false,
            ]
        );

        // --- USUARIOS DE PRUEBA PARA NUEVOS ROLES ---
        
        $registroUser = User::firstOrCreate(
            ['email' => 'registro@centu.edu.do'],
            ['name' => 'Encargado Registro', 'password' => bcrypt('password')]
        );
        $registroUser->assignRole($roleRegistro);

        $cajaUser = User::firstOrCreate(
            ['email' => 'caja@centu.edu.do'],
            ['name' => 'Cajero Principal', 'password' => bcrypt('password')]
        );
        $cajaUser->assignRole($roleCaja);

        // --- USUARIO DE PRUEBA SOLICITANTE ---
        $solicitanteUser = User::firstOrCreate(
            ['email' => 'solicitante@prueba.com'],
            ['name' => 'Aspirante Prueba', 'password' => bcrypt('password')]
        );
        $solicitanteUser->assignRole($roleSolicitante);
    }
}