<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class DeploymentService
{
    public function getStatus()
    {
        try {
            $currentBranch = trim(exec('git rev-parse --abbrev-ref HEAD'));
            $currentHash = trim(exec('git rev-parse --short HEAD'));
            
            // Intentar obtener info del remoto
            exec('git fetch origin');
            $remoteHash = trim(exec("git rev-parse --short origin/{$currentBranch}"));
            
            return [
                'success' => true,
                'branch' => $currentBranch,
                'commit' => $currentHash,
                'latest_remote' => $remoteHash,
                'is_updated' => $currentHash === $remoteHash,
                'app_url' => config('app.url')
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deploy($targetBranch = null)
    {
        // Aumentar tiempo de ejecución y memoria para evitar timeouts
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $log = [];

        try {
            // 1. Determinar rama
            $currentBranch = trim(exec('git rev-parse --abbrev-ref HEAD'));
            $branch = $targetBranch ?: $currentBranch;

            // 2. Limpiar estado (Reset hard)
            // ADVERTENCIA: Esto borra cambios locales no commiteados
            exec("git reset --hard HEAD 2>&1", $log);

            // 3. Cambiar de rama si es necesario
            if ($targetBranch && $targetBranch !== $currentBranch) {
                exec("git fetch origin $branch 2>&1", $log);
                exec("git checkout $branch 2>&1", $log);
            }

            // 4. Pull
            exec("git pull origin $branch 2>&1", $log);

            // 5. Migraciones y Limpieza
            // Usamos try-catch interno para que un error de migración no oculte el log del pull
            try {
                Artisan::call('migrate', ['--force' => true]);
                $log[] = "Migraciones ejecutadas.";
                
                Artisan::call('optimize:clear');
                $log[] = "Caché limpiada.";
            } catch (\Exception $e) {
                $log[] = "Error post-deploy: " . $e->getMessage();
            }

            return [
                'success' => true,
                'message' => "Despliegue completado en rama: $branch",
                'log' => $log,
                'new_hash' => trim(exec('git rev-parse --short HEAD'))
            ];

        } catch (\Exception $e) {
            Log::error("Deploy failed: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => $e->getMessage(),
                'log' => $log
            ];
        }
    }
}