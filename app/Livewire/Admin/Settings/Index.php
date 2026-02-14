<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting; 
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithFileUploads;

    public $activeTab = 'general'; 

    public $logo;

    public $state = [];

    public function mount()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $this->state = [
            'institution_name'    => $settings['institution_name'] ?? config('app.name'),
            'institution_logo'    => $settings['institution_logo'] ?? null,
            'brand_primary_color' => $settings['brand_primary_color'] ?? '#1e3a8a',
            'support_email'       => $settings['support_email'] ?? 'soporte@institucion.edu',
            'wp_api_url'    => $settings['wp_api_url'] ?? '',
            'wp_api_secret' => $settings['wp_api_secret'] ?? '',
            'moodle_url'    => $settings['moodle_url'] ?? '',
            'moodle_token'  => $settings['moodle_token'] ?? '',
            'cardnet_merchant_id' => $settings['cardnet_merchant_id'] ?? '',
            'cardnet_terminal_id' => $settings['cardnet_terminal_id'] ?? '',
            'ecf_rnc_emisor'      => $settings['ecf_rnc_emisor'] ?? '101000000',
        ];
    }

    // --- NUEVA FUNCIÓN: RESTAURAR VALORES POR DEFECTO ---
    public function restoreDefaults()
    {
        $this->state['institution_name'] = 'SGA Academic+';
        $this->state['brand_primary_color'] = '#1e3a8a'; // Azul Original
        $this->state['institution_logo'] = null; // Al ser null, el sistema usará el componente <x-application-logo>
        $this->logo = null; // Limpiar subida temporal

        // Opcional: Guardar automáticamente o dejar que el usuario guarde
        // $this->save(); 
        
        session()->flash('message', 'Valores por defecto restablecidos. Pulsa "Guardar" para aplicar.');
    }

    public function save()
    {
        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'state.support_email'       => 'nullable|email',
            'logo'                      => 'nullable|image|max:2048', 
            'state.wp_api_url'          => 'nullable|url',
            'state.moodle_url'          => 'nullable|url',
        ]);

        if ($this->logo) {
            try {
                $filename = 'logo_' . time() . '.' . $this->logo->getClientOriginalExtension();
                $this->logo->storeAs('branding', $filename, 'hosting_public');
                $url = "/branding/" . $filename;
                $this->state['institution_logo'] = $url;
                // Log::info('Logo subido: ' . $url); // Log deshabilitado
            } catch (\Exception $e) {
                // Log::error('Error subiendo logo: ' . $e->getMessage()); // Log deshabilitado
                session()->flash('error', 'Error al guardar la imagen: ' . $e->getMessage());
                return;
            }
        }

        foreach ($this->state as $key => $value) {
            $type = str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'string';
            if ($key === 'institution_logo') $type = 'image';

            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_')) $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            try {
                Setting::set($key, $value, $group, $type);
            } catch (\Exception $e) {
                // Log::error("Error guardando $key: " . $e->getMessage());
            }
        }

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Configuración del Sistema',
                'description' => 'Actualizó la personalización y ajustes globales.',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        Cache::flush();

        session()->flash('message', 'Personalización guardada correctamente.');
        return redirect()->route('admin.settings.index');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}