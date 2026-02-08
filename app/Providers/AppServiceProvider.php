<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
// Imports Añadidos
use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use App\Models\Payment; // Importar Modelo
use App\Observers\PaymentObserver; // Importar Observador

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

        // 2. CORRECCIÓN PARA NGROK
        if ($this->app->environment('production') || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // 3. CORRECCIÓN LÍMITE LIVEWIRE
        config(['livewire.temporary_file_upload.rules' => 'file|max:102400']);

        // 4. REGISTRAR OBSERVER DE INSCRIPCIONES (NUEVO)
        Enrollment::observe(EnrollmentObserver::class);
        
        // --- NUEVO: Registrar el observador de pagos ---
        Payment::observe(PaymentObserver::class);
    }
}