<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class WipeStudents extends Command
{
    /**
     * El nombre y la firma del comando en la consola.
     *
     * @var string
     */
    protected $signature = 'app:wipe-students';

    /**
     * Descripción del comando.
     *
     * @var string
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

        // Desactivar chequeo de claves foráneas temporalmente para agilizar
        // (Aunque las cascadas funcionan mejor activadas, para delete masivo a veces ayuda gestionar el orden manualmente)
        // En este caso, confiaremos en el ON DELETE CASCADE de tus migraciones.
        
        try {
            DB::beginTransaction();

            // 1. Eliminar Perfiles de Estudiantes (Esto dispara la cascada a Payments, Enrollments, etc.)
            $this->info('Eliminando registros de la tabla `students` y datos vinculados (Pagos, Inscripciones)...');
            $studentsDeleted = DB::table('students')->delete();
            $this->info("Se eliminaron $studentsDeleted perfiles de estudiantes.");

            // 2. Eliminar Usuarios con Rol 'Estudiante'
            // Usamos una query directa para máxima velocidad (evita hidratar 280k modelos Eloquent)
            $this->info('Buscando y eliminando usuarios con rol "Estudiante"...');
            
            // Obtener ID del rol
            $role = DB::table('roles')->where('name', 'Estudiante')->first();

            if ($role) {
                // Borrado masivo usando JOIN para no borrar admins ni profesores
                $affectedUsers = DB::delete("
                    DELETE users 
                    FROM users 
                    INNER JOIN model_has_roles ON users.id = model_has_roles.model_id 
                    WHERE model_has_roles.role_id = ?
                ", [$role->id]);
                
                $this->info("Se eliminaron $affectedUsers cuentas de usuario.");
            } else {
                $this->warn('No se encontró el rol "Estudiante" en la base de datos. Los usuarios no se borraron.');
            }

            DB::commit();
            
            $duration = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("¡LIMPIEZA COMPLETADA CON ÉXITO EN $duration SEGUNDOS!");
            $this->line("Ahora puedes volver a importar tu base de datos limpia.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Ocurrió un error durante el borrado. Se han revertido los cambios.');
            $this->error($e->getMessage());
        }
    }
}