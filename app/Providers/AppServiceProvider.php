<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
// Modelos y Observadores
use App\Models\Setting; // <--- CAMBIO IMPORTANTE: Usamos Setting
use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use App\Models\Payment;
use App\Observers\PaymentObserver;

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

        // 4. REGISTRAR OBSERVADORES
        Enrollment::observe(EnrollmentObserver::class);
        Payment::observe(PaymentObserver::class);

        // 5. CARGAR PERSONALIZACIÓN (Brand)
        try {
            // Verificar conexión antes de consultar
            DB::connection()->getPdo();
            
            if (Schema::hasTable('settings')) { // <--- Verificamos la tabla 'settings'
                $this->bootSystemCustomization();
            } else {
                $this->shareDefaultBranding();
            }
        } catch (\Exception $e) {
            $this->shareDefaultBranding();
        }
    }

    private function bootSystemCustomization()
    {
        // 1. Cargar Nombre (Usando el modelo Setting)
        // Usamos 'institution_name' para coincidir con el formulario
        $appName = Setting::get('institution_name'); 
        
        if ($appName) {
            Config::set('app.name', $appName);
        }

        // 2. Cargar Logo y Colores
        $brandSettings = [
            'logo_url' => Setting::get('institution_logo'), 
            'primary_color' => Setting::get('brand_primary_color', '#1e3a8a'),
        ];

        // 3. RGB para Tailwind
        $brandSettings['primary_rgb'] = $this->hex2rgb($brandSettings['primary_color']);

        View::share('branding', (object) $brandSettings);
    }

    private function shareDefaultBranding()
    {
        View::share('branding', (object) [
            'logo_url' => null,
            'primary_color' => '#1e3a8a',
            'primary_rgb' => '30 58 138'
        ]);
    }

    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return "$r $g $b";
    }
}