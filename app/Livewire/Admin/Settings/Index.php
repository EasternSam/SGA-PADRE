<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting; // <--- Usamos el modelo Setting
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log; // <--- IMPORTANTE: Importar Log

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithFileUploads;

    public $activeTab = 'general'; 

    public $logo;

    public $state = [];

    public function mount()
    {
        // Cargar configuraciones de la tabla settings
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $this->state = [
            // --- PERSONALIZACIÓN / MARCA BLANCA ---
            'institution_name'    => $settings['institution_name'] ?? config('app.name'),
            'institution_logo'    => $settings['institution_logo'] ?? null,
            'brand_primary_color' => $settings['brand_primary_color'] ?? '#1e3a8a',
            'support_email'       => $settings['support_email'] ?? 'soporte@institucion.edu',

            // --- APIs ---
            'wp_api_url'    => $settings['wp_api_url'] ?? '',
            'wp_api_secret' => $settings['wp_api_secret'] ?? '',
            'moodle_url'    => $settings['moodle_url'] ?? '',
            'moodle_token'  => $settings['moodle_token'] ?? '',
            
            // --- FINANZAS ---
            'cardnet_merchant_id' => $settings['cardnet_merchant_id'] ?? '',
            'cardnet_terminal_id' => $settings['cardnet_terminal_id'] ?? '',
            'ecf_rnc_emisor'      => $settings['ecf_rnc_emisor'] ?? '101000000',
        ];
    }

    public function save()
    {
        // DEBUG: Inicio del proceso
        Log::info('--- INICIANDO GUARDADO DE SETTINGS ---');
        Log::info('Datos recibidos del formulario:', $this->state);

        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'state.support_email'       => 'nullable|email',
            'logo'                      => 'nullable|image|max:2048', 
            'state.wp_api_url'          => 'nullable|url',
            'state.moodle_url'          => 'nullable|url',
        ]);

        Log::info('Validación superada.');

        // 1. Manejo de subida de Logo
        if ($this->logo) {
            try {
                $path = $this->logo->store('public/branding');
                $url = Storage::url($path);
                $this->state['institution_logo'] = $url;
                Log::info('Logo subido exitosamente: ' . $url);
            } catch (\Exception $e) {
                Log::error('Error subiendo logo: ' . $e->getMessage());
            }
        }

        // 2. Guardar en la tabla settings usando el helper del modelo
        foreach ($this->state as $key => $value) {
            
            $type = str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'string';
            if ($key === 'institution_logo') $type = 'image';

            // Determinar grupo
            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_')) $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            try {
                // Usamos el método estático set
                Setting::set($key, $value, $group, $type);
                // Log::info("Guardado: $key"); // Descomentar si quieres ver cada campo
            } catch (\Exception $e) {
                Log::error("Error guardando $key: " . $e->getMessage());
            }
        }

        Log::info('Ciclo de guardado finalizado.');

        // 3. Auditoría
        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Configuración del Sistema',
                'description' => 'Actualizó la personalización y ajustes globales.',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        // 4. Recargar
        session()->flash('message', 'Configuración guardada. Los cambios visuales se aplicarán ahora.');
        
        Log::info('Redirigiendo...');
        
        return redirect()->route('admin.settings.index');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}