<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeStudents extends Command
{
    /**
     * El nombre y la firma del comando en la consola.
     */
    protected $signature = 'app:wipe-students';

    /**
     * Descripción del comando.
     */
    protected $description = 'Elimina masivamente todos los estudiantes, sus usuarios asociados y su data financiera/académica.';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $this->error('¡ATENCIÓN! ESTA ES UNA OPERACIÓN DESTRUCTIVA.');
        $this->line('Se eliminarán:');
        $this->line('- Todos los perfiles de Estudiantes.');
        $this->line('- Todos los Usuarios con rol "Estudiante".');
        $this->line('- Todo el historial financiero (Pagos) asociado.');
        $this->line('- Todas las inscripciones y notas asociadas.');
        
        if (!$this->confirm('¿Estás 100% seguro de que quieres continuar?')) {
            $this->info('Operación cancelada.');
            return;
        }

        $startTime = microtime(true);
        $this->info('Iniciando proceso de limpieza...');

        // 1. Detección de Driver para desactivar FK Checks (Compatibilidad MySQL/SQLite)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('SET UNIQUE_CHECKS=0;');
        }
        
        try {
            // No usamos transacciones gigantes (DB::beginTransaction) para evitar desbordamiento de memoria
            // en borrados masivos. Con FK desactivadas es seguro ir tabla por tabla.

            // ==============================================================
            // FASE 1: ELIMINAR ESTUDIANTES (TABLA STUDENTS)
            // ==============================================================
            $totalStudents = DB::table('students')->count();
            
            if ($totalStudents > 0) {
                $this->info("Eliminando $totalStudents perfiles de estudiantes...");
                $bar = $this->output->createProgressBar($totalStudents);
                $bar->start();

                // Borramos en lotes de 2000 para actualizar la barra y no matar la memoria
                // Usamos chunkById para ser eficientes
                DB::table('students')->orderBy('id')->chunkById(2000, function ($students) use ($bar) {
                    $ids = $students->pluck('id')->toArray();
                    DB::table('students')->whereIn('id', $ids)->delete();
                    $bar->advance(count($ids));
                });

                $bar->finish();
                $this->newLine();
                $this->info("✔ Estudiantes eliminados.");
            } else {
                $this->info("✔ No se encontraron perfiles de estudiantes.");
            }

            // ==============================================================
            // FASE 2: ELIMINAR USUARIOS (TABLA USERS CON ROL ESTUDIANTE)
            // ==============================================================
            $this->newLine();
            $this->info('Buscando usuarios con rol "Estudiante"...');
            
            // Obtener ID del rol
            $role = DB::table('roles')->where('name', 'Estudiante')->first();

            if ($role) {
                // Contar cuántos usuarios tienen ese rol
                $totalUsers = DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_type', 'App\Models\User')
                    ->count();

                if ($totalUsers > 0) {
                    $this->info("Eliminando $totalUsers cuentas de usuario...");
                    $barUsers = $this->output->createProgressBar($totalUsers);
                    $barUsers->start();

                    // Obtenemos los IDs de usuarios a borrar por lotes
                    DB::table('model_has_roles')
                        ->where('role_id', $role->id)
                        ->where('model_type', 'App\Models\User')
                        ->orderBy('model_id')
                        ->chunkById(2000, function ($relations) use ($barUsers) {
                            $userIds = $relations->pluck('model_id')->toArray();
                            
                            // Borramos los usuarios (la relación en model_has_roles se borra sola o queda huérfana, 
                            // pero como borraremos todo, limpiamos también la relación manualmente por si acaso)
                            DB::table('users')->whereIn('id', $userIds)->delete();
                            DB::table('model_has_roles')->whereIn('model_id', $userIds)->where('model_type', 'App\Models\User')->delete();
                            
                            $barUsers->advance(count($userIds));
                        }, 'model_id'); // chunkById usa la columna 'model_id' como índice

                    $barUsers->finish();
                    $this->newLine();
                    $this->info("✔ Usuarios eliminados.");
                } else {
                    $this->info("✔ No se encontraron usuarios con rol Estudiante.");
                }
            } else {
                $this->warn('⚠ No se encontró el rol "Estudiante" en la base de datos.');
            }

            // ==============================================================
            // FASE 3: LIMPIEZA DE HUÉRFANOS (OPCIONAL PERO RECOMENDADO)
            // ==============================================================
            // Al borrar estudiantes, los pagos y enrollments deberían irse si hay ON DELETE CASCADE.
            // Pero como desactivamos FK checks para velocidad, vamos a asegurarnos de limpiar tablas hijas.
            
            $this->newLine();
            $this->info('Limpiando tablas dependientes (Pagos, Inscripciones)...');
            
            // Borrar pagos sin estudiante asociado
            // (Nota: En DB grandes esto puede ser lento, pero asegura limpieza total)
            DB::table('payments')->truncate(); 
            DB::table('enrollments')->truncate();
            // También borramos student_requests si tienes esa tabla
            if (DB::getSchemaBuilder()->hasTable('student_requests')) {
                DB::table('student_requests')->truncate();
            }

            $this->info("✔ Tablas financieras y académicas vaciadas.");

            $duration = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("¡LIMPIEZA COMPLETADA CON ÉXITO EN $duration SEGUNDOS!");
            $this->line("Ahora puedes volver a importar tu base de datos limpia.");

        } catch (\Exception $e) {
            $this->error('Ocurrió un error durante el borrado:');
            $this->error($e->getMessage());
        } finally {
            // 4. Reactivar FK Checks SIEMPRE (incluso si falla)
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                DB::statement('SET UNIQUE_CHECKS=1;');
            }
        }
    }
}