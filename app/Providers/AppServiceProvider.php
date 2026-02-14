<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
// Modelos y Observadores
use App\Models\SystemOption;
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
        // Estos observers se disparan automáticamente al crear/actualizar modelos
        Enrollment::observe(EnrollmentObserver::class);
        Payment::observe(PaymentObserver::class);

        // 5. CARGAR PERSONALIZACIÓN DEL SISTEMA (SaaS Branding)
        // Solo intentamos cargar si la tabla existe para no romper migraciones iniciales
        try {
            // Verificar si la conexión es SQLite y si el archivo existe antes de consultar
            // Esto evita errores en el primer despliegue o si se borra el archivo .sqlite
            $canConnect = true;
            if (DB::connection()->getDriverName() === 'sqlite') {
                $dbPath = DB::connection()->getDatabaseName();
                if (!file_exists($dbPath) && $dbPath !== ':memory:') {
                    $canConnect = false;
                }
            }

            if ($canConnect && Schema::hasTable('system_options')) {
                $this->bootSystemCustomization();
            } else {
                // Fallback branding si no hay DB
                View::share('branding', (object) [
                    'logo_url' => null,
                    'primary_color' => '#1e3a8a',
                    'primary_rgb' => '30 58 138'
                ]);
            }
        } catch (\Exception $e) {
            // Ignorar errores de BD durante despliegue inicial
            // Proveer branding por defecto para que no falle la vista
             View::share('branding', (object) [
                'logo_url' => null,
                'primary_color' => '#1e3a8a',
                'primary_rgb' => '30 58 138'
            ]);
        }
    }

    private function bootSystemCustomization()
    {
        // 1. Cargar Nombre de la Institución
        $appName = SystemOption::get('institution_name');
        if ($appName) {
            Config::set('app.name', $appName);
        }

        // 2. Cargar Logo y Colores para compartirlos con TODAS las vistas
        $brandSettings = [
            'logo_url' => SystemOption::get('institution_logo'), // Si es null, usará el componente default
            'primary_color' => SystemOption::get('brand_primary_color', '#1e3a8a'), // Azul default
        ];

        // 3. Convertir Hex a RGB para Tailwind (Ej: #ffffff -> "255 255 255")
        $brandSettings['primary_rgb'] = $this->hex2rgb($brandSettings['primary_color']);

        // Compartir la variable $branding globalmente en todos los blades
        View::share('branding', (object) $brandSettings);
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