<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\CheckSaaSProfile;
// Importamos el nuevo middleware
use App\Http\Middleware\EnsureFeatureEnabled; 
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
            // ===> NUEVO ALIAS PARA PROTEGER RUTAS <===
            'feature' => EnsureFeatureEnabled::class, 
        ]);

        $middleware->appendToGroup('web', ForcePasswordChange::class);
        $middleware->appendToGroup('web', CheckSaaSProfile::class); 
        
        $middleware->web(append: [
            AuditLogMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/log-click', 
        ]);

        $middleware->trustProxies(at: '*');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();