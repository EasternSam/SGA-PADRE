<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module);
            $lowerName = Str::lower($moduleName);

            // 1. Cargar Rutas
            if (File::exists($module . '/Routes/web.php')) {
                $this->loadRoutesFrom($module . '/Routes/web.php');
            }

            // 2. Cargar Vistas (namespace: module_name::view)
            if (File::exists($module . '/Views')) {
                $this->loadViewsFrom($module . '/Views', $lowerName);
            }

            // 3. Registrar Componentes Livewire automáticamente
            // Busca archivos en app/Modules/X/Livewire y los registra como 'x-componente'
            if (File::exists($module . '/Livewire')) {
                $this->registerLivewireComponents($module . '/Livewire', $lowerName);
            }
        }
    }

    protected function registerLivewireComponents($directory, $modulePrefix)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $className = 'App\\Modules\\' . ucfirst($modulePrefix) . '\\Livewire\\' . 
                         str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            
            if (class_exists($className)) {
                // Nombre del componente: 'hr-dashboard' (ejemplo)
                $componentName = $modulePrefix . '-' . Str::kebab($file->getFilenameWithoutExtension());
                Livewire::component($componentName, $className);
            }
        }
    }
}