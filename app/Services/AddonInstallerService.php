<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;
use App\Helpers\SaaS;

class AddonInstallerService
{
    /**
     * Descarga e instala un módulo desde el Maestro.
     */
    public function install($featureCode)
    {
        // 1. Verificar si tenemos permiso
        if (!SaaS::has($featureCode)) {
            throw new \Exception("No tienes licencia para el módulo: $featureCode");
        }

        // Configuración
        $masterUrl = config('services.saas.master_url', 'https://gestion.90s.agency');
        $licenseKey = config('services.saas.license_key', env('APP_LICENSE_KEY'));
        
        // 2. Solicitar URL de descarga al maestro (Simulado por ahora)
        // En producción: $response = Http::post("$masterUrl/api/v1/addons/download", ['code' => $featureCode, ...]);
        
        // --- SIMULACIÓN DE INSTALACIÓN ---
        // Aquí es donde descomprimiríamos el ZIP en app/Modules/
        
        $moduleName = $this->getModuleNameFromCode($featureCode);
        $targetPath = app_path("Modules/$moduleName");

        if (File::exists($targetPath)) {
            return "El módulo ya está instalado.";
        }

        // Crear estructura básica (esto lo haría el ZIP automáticamente)
        File::makeDirectory($targetPath, 0755, true);
        File::makeDirectory("$targetPath/Routes", 0755, true);
        File::makeDirectory("$targetPath/Livewire", 0755, true);
        File::makeDirectory("$targetPath/Views", 0755, true);

        return "Módulo $moduleName preparado. (Esperando archivos ZIP del maestro)";
    }

    private function getModuleNameFromCode($code)
    {
        return match ($code) {
            'hr' => 'HumanResources',
            'library' => 'Library',
            default => ucfirst($code),
        };
    }
}