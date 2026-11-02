<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    // ... otros providers ...
    
    // NUEVO: Sistema de Módulos Dinámicos
    App\Providers\ModuleServiceProvider::class,

    // Motor Transaccional y Tributario Gridbase Bills Core
    Gridbase\BillsCore\BillsCoreServiceProvider::class,
];