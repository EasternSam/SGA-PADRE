<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CleanRolelessUsers extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'sga:clean-roleless-users';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Borrar todos los usuarios que no tengan roles asignados en el sistema';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        // Buscamos los usuarios que no tienen ninguna relación con la tabla de roles de Spatie
        $users = User::doesntHave('roles')->get();
        $count = $users->count();

        if ($count === 0) {
            $this->info('No se encontraron usuarios sin roles asignados. El sistema está limpio.');
            return 0;
        }

        $this->warn("Se han encontrado {$count} usuarios sin roles asignados.");

        // Pregunta de confirmación por seguridad al ser una operación de borrado
        if (!$this->confirm('¿Estás seguro de que deseas eliminar a estos usuarios? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada por el usuario.');
            return 0;
        }

        $this->info('Iniciando proceso de limpieza...');

        foreach ($users as $user) {
            $userName = $user->name;
            $userEmail = $user->email;

            try {
                // delete() disparará los observers si los tienes configurados
                $user->delete();
                $this->line("Eliminado: {$userName} (<{$userEmail}>)");
            } catch (\Exception $e) {
                $this->error("Error al eliminar a {$userName}: " . $e->getMessage());
            }
        }

        $this->info("Limpieza completada con éxito.");
        
        return 0;
    }
}