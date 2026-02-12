<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
// Importar middlewares personalizados
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\AuditLogMiddleware; // Importar el logger
use App\Http\Middleware\CheckSaaSProfile; // <-- NUEVO MIDDLEWARE SAAS
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
        
        // --- NUEVO: REGISTRO DEL GUARDIÁN SAAS ---
        // Se ejecuta en todas las peticiones web para proteger todo el sistema.
        $middleware->appendToGroup('web', CheckSaaSProfile::class); 
        
        // --- AUDITORÍA DE CAJA NEGRA ---
        // Registramos el middleware que loguea todas las peticiones
        $middleware->web(append: [
            AuditLogMiddleware::class,
        ]);

        // Excluimos la verificación CSRF para el endpoint del logger frontend
        $middleware->validateCsrfTokens(except: [
            'api/log-click', 
        ]);

        $middleware->trustProxies(at: '*');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();