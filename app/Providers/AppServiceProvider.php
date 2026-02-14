<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
// Modelos y Observadores
use App\Models\Setting; // <--- CAMBIO CRÍTICO: Usamos Setting, no SystemOption
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

        // 2. CORRECCIÓN PARA NGROK (Forzar HTTPS si es producción o ngrok)
        if ($this->app->environment('production') || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // 3. CORRECCIÓN LÍMITE LIVEWIRE (Aumentar tamaño de subida temporal)
        config(['livewire.temporary_file_upload.rules' => 'file|max:102400']);

        // 4. REGISTRAR OBSERVADORES
        Enrollment::observe(EnrollmentObserver::class);
        Payment::observe(PaymentObserver::class);

        // 5. CARGAR PERSONALIZACIÓN DEL SISTEMA (Marca Blanca)
        try {
            // Verificar conexión antes de consultar para no romper despliegues
            // Usamos el driver PDO para chequear conexión rápida
            DB::connection()->getPdo();
            
            // Verificamos que la tabla 'settings' exista antes de intentar leerla
            if (Schema::hasTable('settings')) {
                $this->bootSystemCustomization();
            } else {
                // Si no hay tabla, cargamos branding por defecto (Azul)
                $this->shareDefaultBranding();
            }
        } catch (\Exception $e) {
            // Si falla la BD (ej. durante migración), cargamos default
            $this->shareDefaultBranding();
        }
    }

    private function bootSystemCustomization()
    {
        // 1. Cargar Nombre de la Institución (Desde la tabla settings)
        // Usamos el helper get() del modelo Setting
        $appName = Setting::get('institution_name');
        
        if ($appName) {
            Config::set('app.name', $appName);
        }

        // 2. Cargar Logo y Colores
        // Si no existen en BD, usamos los defaults
        $brandSettings = [
            'logo_url' => Setting::get('institution_logo'), 
            'primary_color' => Setting::get('brand_primary_color', '#1e3a8a'), // Azul default
        ];

        // 3. Convertir Hex a RGB para Tailwind (Ej: #e41b12 -> "228 27 18")
        // Esto es vital para que las variables CSS --color-primary funcionen
        $brandSettings['primary_rgb'] = $this->hex2rgb($brandSettings['primary_color']);

        // Compartir la variable $branding globalmente en todos los blades
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

    // Función auxiliar para convertir Hex a RGB string
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