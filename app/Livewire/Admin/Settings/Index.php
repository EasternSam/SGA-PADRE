<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads; // Vital para subir el logo
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithFileUploads;

    public $activeTab = 'general'; // Empezamos en 'general' para ver la personalización

    // Archivo temporal para la subida de logo
    public $logo;

    // Array de datos vinculados al formulario
    public $state = [];

    public function mount()
    {
        // Cargar configuraciones existentes de la BD
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Definir la estructura base de configuraciones
        // Usamos las claves que AppServiceProvider espera para la personalización
        $this->state = [
            // --- PERSONALIZACIÓN / MARCA BLANCA ---
            'institution_name'    => $settings['institution_name'] ?? config('app.name'),
            'institution_logo'    => $settings['institution_logo'] ?? null,
            'brand_primary_color' => $settings['brand_primary_color'] ?? '#1e3a8a', // Azul default
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
        // Reglas de Validación
        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'state.support_email'       => 'nullable|email',
            'logo'                      => 'nullable|image|max:2048', // Validación del logo
            'state.wp_api_url'          => 'nullable|url',
            'state.moodle_url'          => 'nullable|url',
        ]);

        // 1. Manejo de subida de Logo (si se seleccionó uno nuevo)
        if ($this->logo) {
            // Guardar en disco publico
            $path = $this->logo->store('public/branding');
            // Obtener URL accesible
            $url = Storage::url($path);
            // Actualizar estado para que se guarde en BD
            $this->state['institution_logo'] = $url;
        }

        // 2. Guardar configuraciones en la base de datos
        foreach ($this->state as $key => $value) {
            
            // Determinar grupo visual
            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_')) $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            // Determinar tipo
            $type = str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'string';
            if ($key === 'institution_logo') $type = 'image';

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'type' => $type]
            );
        }

        // 3. Auditoría
        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Configuración del Sistema',
                'description' => 'Actualizó la personalización y ajustes globales (Tab: '.strtoupper($this->activeTab).').',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        // Limpiar caché para que los cambios de color se vean al instante
        \Illuminate\Support\Facades\Cache::flush();

        // Recargar para limpiar el input file y ver cambios
        session()->flash('message', 'Personalización guardada correctamente. El sistema se ha actualizado.');
        return redirect()->route('admin.settings.index');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}