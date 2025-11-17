<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
// 1. IMPORTAMOS EL FACADE DE URL
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tu código original
        Schema::defaultStringLength(191);

        // 2. AÑADIMOS ESTA CONDICIÓN PARA ARREGLAR NGROK (Mixed Content)
        // Si la app está en 'local' (para Ngrok) o 'production',
        // fuerza que todos los enlaces (asset(), route()) se generen con https.
        
        // ¡CORRECCIÓN! Debe ser 'https' (con una 's'), no 'httpss'.
        if (config('app.env') === 'local' || config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}