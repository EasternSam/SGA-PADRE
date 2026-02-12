<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    public $activeTab = 'apis'; // apis, finance, general

    // Array de datos vinculados al formulario
    public $state = [];

    public function mount()
    {
        // Cargar configuraciones existentes de la BD
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Definir la estructura base de configuraciones esperadas con sus valores por defecto
        $this->state = [
            // APIs (WordPress / Moodle)
            'wp_api_url' => $settings['wp_api_url'] ?? '',
            'wp_api_secret' => $settings['wp_api_secret'] ?? '',
            'moodle_url' => $settings['moodle_url'] ?? '',
            'moodle_token' => $settings['moodle_token'] ?? '',
            
            // Finanzas (Cardnet / DGII)
            'cardnet_merchant_id' => $settings['cardnet_merchant_id'] ?? '',
            'cardnet_terminal_id' => $settings['cardnet_terminal_id'] ?? '',
            'ecf_rnc_emisor' => $settings['ecf_rnc_emisor'] ?? '101000000',
            
            // General
            'school_name' => $settings['school_name'] ?? 'Mi Institución',
            'support_email' => $settings['support_email'] ?? 'soporte@institucion.edu',
        ];
    }

    public function save()
    {
        // Reglas de Validación
        $this->validate([
            'state.wp_api_url' => 'nullable|url',
            'state.moodle_url' => 'nullable|url',
            'state.support_email' => 'nullable|email',
        ]);

        // Guardar cada configuración en la base de datos
        foreach ($this->state as $key => $value) {
            
            // Determinar el grupo visual
            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_')) $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            // Determinar tipo (para ocultar contraseñas)
            $type = str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'string';

            Setting::set($key, $value, $group, $type);
        }

        // Auditoría
        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Configuración del Sistema',
                'description' => 'Actualizó los parámetros de configuración global del sistema (Pestaña: '.strtoupper($this->activeTab).').',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        session()->flash('message', 'Configuraciones guardadas correctamente. Los cambios se han aplicado en todo el sistema.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}