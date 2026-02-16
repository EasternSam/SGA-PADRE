<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use App\Helpers\SaaS;

class AddonInstallerService
{
    protected $masterUrl;
    protected $licenseKey;

    public function __construct()
    {
        $this->masterUrl = rtrim(env('SAAS_MASTER_URL'), '/');
        $this->licenseKey = env('APP_LICENSE_KEY');
    }

    /**
     * Descarga e instala un módulo específico.
     * @param string $featureCode El código del addon (ej: 'hr', 'library')
     * @return array Status y mensaje
     */
    public function install($featureCode)
    {
        // 1. Verificar si tenemos permiso (Licencia)
        if (!SaaS::has($featureCode)) {
            return ['success' => false, 'message' => "Tu licencia no incluye el módulo: $featureCode"];
        }

        // 2. Definir rutas
        // Mapeamos códigos simples a Nombres de Carpeta StudlyCase
        // Ej: 'hr' -> 'HumanResources', 'library' -> 'Library'
        $moduleName = $this->getModuleName($featureCode);
        $targetPath = app_path("Modules/$moduleName");
        $tempZipPath = storage_path("app/temp_{$featureCode}.zip");

        // 3. Descargar el archivo desde el Maestro
        try {
            $domain = request()->getHost();
            $url = "{$this->masterUrl}/api/v1/addons/download/{$featureCode}?license_key={$this->licenseKey}&domain={$domain}";
            
            // Usamos 'sink' para guardar el archivo directamente en disco y no saturar la RAM
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

            // 5. Limpiar Caché de Laravel para que reconozca las nuevas rutas/vistas
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');

            return ['success' => true, 'message' => "Módulo $moduleName instalado correctamente."];
        } else {
            return ['success' => false, 'message' => "No se pudo descomprimir el archivo del módulo."];
        }
    }

    /**
     * Mapeo simple de código a nombre de carpeta.
     * Puedes expandir esto o hacerlo dinámico si el maestro enviara el nombre real.
     */
    private function getModuleName($code)
    {
        return match ($code) {
            'hr' => 'HumanResources',
            'library' => 'Library',
            'inventory' => 'Inventory',
            default => ucfirst($code), // Fallback: 'chat' -> 'Chat'
        };
    }

    /**
     * Verifica si el módulo ya existe físicamente en el disco.
     */
    public function isInstalled($featureCode)
    {
        $moduleName = $this->getModuleName($featureCode);
        return File::exists(app_path("Modules/$moduleName/Routes/web.php"));
    }
}