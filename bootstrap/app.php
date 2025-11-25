<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
// Importar el nuevo middleware
use App\Http\Middleware\ForcePasswordChange;
use App\Console\Commands\ImportStudentsFast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ImportStudentsFast::class, 
    ])
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        // --- REGISTRAR MIDDLEWARE GLOBAL O ALIAS ---
        // Lo agregamos al grupo 'web' para que se ejecute en todas las rutas de navegador
        $middleware->appendToGroup('web', ForcePasswordChange::class);

        $middleware->trustProxies(at: '*');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();