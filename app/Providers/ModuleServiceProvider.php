<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Definir la "Bahía de Carga" donde se instalarán los addons
        $modulesPath = app_path('Modules');

        // Si la carpeta no existe (sistema limpio), no hacemos nada
        if (!File::exists($modulesPath)) {
            return;
        }

        // 2. Escanear carpetas (cada carpeta es un módulo: HumanResources, Library, etc.)
        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module); // Ej: HumanResources
            $lowerName = Str::lower($moduleName); // Ej: humanresources

            // A. Cargar Rutas (web.php del módulo)
            if (File::exists($module . '/Routes/web.php')) {
                $this->loadRoutesFrom($module . '/Routes/web.php');
            }

            // B. Cargar Vistas (Se usarán como: humanresources::nombre-vista)
            if (File::exists($module . '/Views')) {
                $this->loadViewsFrom($module . '/Views', $lowerName);
            }

            // C. Registrar Componentes Livewire Automáticamente
            // Busca archivos en app/Modules/HumanResources/Livewire
            if (File::exists($module . '/Livewire')) {
                $this->registerLivewireComponents($module . '/Livewire', $moduleName, $lowerName);
            }
        }
    }

    protected function registerLivewireComponents($directory, $moduleName, $modulePrefix)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            // Generar el namespace correcto: App\Modules\HumanResources\Livewire\Dashboard
            $relativePath = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $class = "App\\Modules\\{$moduleName}\\Livewire\\{$relativePath}";

            if (class_exists($class)) {
                // Generar nombre del componente kebab-case: 'humanresources-dashboard'
                $componentName = $modulePrefix . '-' . Str::kebab($file->getFilenameWithoutExtension());
                Livewire::component($componentName, $class);
            }
        }
    }
}