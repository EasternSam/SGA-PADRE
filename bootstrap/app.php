<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\CheckSaaSProfile;
use App\Http\Middleware\VerifyLicense;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Console\Commands\ImportStudentsFast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/cardnet.php'));
            Route::middleware('web')
                ->group(base_path('routes/kiosk.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/student.php'));
            Route::middleware('web')
                ->group(base_path('routes/teacher.php'));
            Route::middleware('web')
                ->group(base_path('routes/reports.php'));
        },
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
            'auth.kiosk' => \App\Http\Middleware\RedirectIfUnauthenticatedKiosk::class,
        ]);

        $middleware->appendToGroup('web', ForcePasswordChange::class);
        $middleware->appendToGroup('web', CheckSaaSProfile::class); 
        
        $middleware->web(append: [
            AuditLogMiddleware::class,
            VerifyLicense::class,
        ]);

        $middleware->api(append: [
            VerifyLicense::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/log-click', 
        ]);

        $middleware->trustProxies(at: '*');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();