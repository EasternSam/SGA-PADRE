<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
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
        // 1. Configuración de BD
        Schema::defaultStringLength(191);

        // 2. CORRECCIÓN PARA NGROK (Error 401)
        if ($this->app->environment('production') || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // 3. CORRECCIÓN LÍMITE LIVEWIRE (Error 12288KB)
        // Livewire trae un límite por defecto de 12MB en su configuración interna.
        // Aquí lo forzamos a 100MB (102400 KB) para que acepte tu CSV.
        config(['livewire.temporary_file_upload.rules' => 'file|max:102400']);
    }
}