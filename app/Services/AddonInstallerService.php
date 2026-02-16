<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;
use App\Helpers\SaaS;

class AddonInstallerService
{
    protected $masterUrl;
    protected $licenseKey;

    public function __construct()
    {
        $this->masterUrl = rtrim(config('services.saas.master_url', env('SAAS_MASTER_URL', 'https://gestion.90s.agency')), '/');
        $this->licenseKey = config('services.saas.license_key', env('APP_LICENSE_KEY'));
    }

    /**
     * Descarga e instala un módulo específico desde el maestro.
     */
    public function install($featureCode)
    {
        // 1. Verificar si la licencia permite este módulo
        if (!SaaS::has($featureCode)) {
            return ['success' => false, 'message' => "Tu licencia no incluye el módulo: $featureCode"];
        }

        // 2. Definir rutas
        $moduleName = $this->getModuleName($featureCode);
        $targetPath = app_path("Modules/$moduleName");
        $tempZipPath = storage_path("app/temp_{$featureCode}.zip");

        // 3. Descargar el archivo desde el Maestro
        try {
            $domain = request()->getHost();
            // Endpoint en el maestro que sirve el ZIP (debes tenerlo configurado allá)
            $url = "{$this->masterUrl}/api/v1/addons/download/{$featureCode}?license_key={$this->licenseKey}&domain={$domain}";
            
            // Usamos 'sink' para guardar el archivo directamente en disco
            $response = Http::withoutVerifying()->sink($tempZipPath)->get($url);

            if ($response->failed()) {
                if (File::exists($tempZipPath)) File::delete($tempZipPath);
                return ['success' => false, 'message' => "Error descargando desde el maestro: " . $response->status()];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Error de conexión: " . $e->getMessage()];
        }

        // 4. Descomprimir e Instalar
        $zip = new ZipArchive;
        if ($zip->open($tempZipPath) === TRUE) {
            
            // Crear carpeta si no existe
            if (!File::exists($targetPath)) {
                File::makeDirectory($targetPath, 0755, true);
            }

            // Extraer
            $zip->extractTo($targetPath);
            $zip->close();
            
            // Limpiar ZIP temporal
            File::delete($tempZipPath);

            // 5. Limpiar Caché de Laravel para que reconozca las nuevas rutas/vistas automáticamente
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');

            return ['success' => true, 'message' => "Módulo $moduleName instalado y activado correctamente."];
        } else {
            return ['success' => false, 'message' => "No se pudo descomprimir el archivo del módulo."];
        }
    }

    private function getModuleName($code)
    {
        // Mapeo de códigos de feature a nombres de carpeta reales
        return match ($code) {
            'hr' => 'HumanResources',
            'library' => 'Library',
            'inventory' => 'Inventory',
            default => ucfirst($code),
        };
    }

    public function isInstalled($featureCode)
    {
        $moduleName = $this->getModuleName($featureCode);
        return File::exists(app_path("Modules/$moduleName/Routes/web.php"));
    }
}