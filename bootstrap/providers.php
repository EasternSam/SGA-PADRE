<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    // ... otros providers ...

    // NUEVO: Cargador de Módulos Dinámicos (Addons)
    App\Providers\ModuleServiceProvider::class,
];