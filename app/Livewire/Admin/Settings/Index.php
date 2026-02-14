<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting; // Usamos el modelo Setting que ya confirmamos que funciona
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
        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'state.support_email'       => 'nullable|email',
            'logo'                      => 'nullable|image|max:2048', 
            'state.wp_api_url'          => 'nullable|url',
            'state.moodle_url'          => 'nullable|url',
        ]);

        // 1. Manejo de subida de Logo (CAMBIO: Usar disco hosting_public)
        if ($this->logo) {
            try {
                // Generar nombre único
                $filename = 'logo_' . time() . '.' . $this->logo->getClientOriginalExtension();
                
                // Guardar directamente en public/branding (sin symlinks)
                // Usamos el disco 'hosting_public' que definimos en config/filesystems.php
                // Esto guarda en: /home/usuario/public_html/branding/logo_xxxxx.png
                $this->logo->storeAs('branding', $filename, 'hosting_public');
                
                // La URL ahora es directa, sin /storage/
                $url = "/branding/" . $filename;
                
                $this->state['institution_logo'] = $url;
                Log::info('Logo subido a carpeta pública: ' . $url);
            } catch (\Exception $e) {
                Log::error('Error subiendo logo: ' . $e->getMessage());
                session()->flash('error', 'Error al guardar la imagen: ' . $e->getMessage());
                return;
            }
        }

        // 2. Guardar en la tabla settings
        foreach ($this->state as $key => $value) {
            
            $type = str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'string';
            if ($key === 'institution_logo') $type = 'image';

            // Determinar grupo
            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_')) $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            try {
                // Usamos el método estático set del modelo Setting
                Setting::set($key, $value, $group, $type);
            } catch (\Exception $e) {
                Log::error("Error guardando $key: " . $e->getMessage());
            }
        }

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

        // 4. LIMPIAR CACHÉ
        Cache::flush();

        session()->flash('message', 'Personalización guardada correctamente. El sistema se ha actualizado.');
        return redirect()->route('admin.settings.index');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}