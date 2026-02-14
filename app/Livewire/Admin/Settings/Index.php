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

    // Propiedades para la gestión de degradados
    public $navbar_type = 'solid'; // 'solid' o 'gradient'
    public $navbar_gradient_start = '#1e3a8a';
    public $navbar_gradient_end = '#000000';
    public $navbar_gradient_direction = 'to right';

    // Propiedades para Presets
    public $presets = [];
    public $new_preset_name = '';

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

        // Cargar configuraciones de degradado guardadas
        $this->navbar_type = $settings['navbar_type'] ?? 'solid';
        $this->navbar_gradient_start = $settings['navbar_gradient_start'] ?? '#1e3a8a';
        $this->navbar_gradient_end = $settings['navbar_gradient_end'] ?? '#000000';
        $this->navbar_gradient_direction = $settings['navbar_gradient_direction'] ?? 'to right';

        // Cargar Presets
        $this->presets = json_decode($settings['theme_presets'] ?? '[]', true) ?? [];

        // Sincronizar el color inicial si es sólido
        if ($this->navbar_type === 'solid') {
            $this->navbar_gradient_start = $this->state['brand_primary_color'];
        }
    }

    public function updated($propertyName)
    {
        // Construir el color en tiempo real si cambian las propiedades del degradado
        if (in_array($propertyName, ['navbar_type', 'navbar_gradient_start', 'navbar_gradient_end', 'navbar_gradient_direction'])) {
            $this->buildNavbarColor();
        }
        
        // Si el usuario cambia el color en modo sólido directamente
        if ($propertyName === 'state.brand_primary_color' && $this->navbar_type === 'solid') {
            $this->navbar_gradient_start = $this->state['brand_primary_color'];
        }
    }

    public function buildNavbarColor()
    {
        if ($this->navbar_type === 'gradient') {
            $this->state['brand_primary_color'] = "linear-gradient({$this->navbar_gradient_direction}, {$this->navbar_gradient_start}, {$this->navbar_gradient_end})";
        } else {
            // Si volvemos a sólido, usamos el color de inicio como color principal
            // Verifica si el color actual es un string de gradiente para no dejar basura
            if (str_starts_with($this->state['brand_primary_color'], 'linear-gradient')) {
                $this->state['brand_primary_color'] = $this->navbar_gradient_start;
            }
        }
    }

    // --- NUEVA FUNCIÓN: RESTAURAR VALORES POR DEFECTO ---
    public function restoreDefaults()
    {
        $this->state['institution_name'] = 'SGA Academic+';
        $this->state['brand_primary_color'] = '#1e3a8a'; // Azul Original
        $this->state['institution_logo'] = null;
        $this->logo = null;

        // Restaurar valores de degradado
        $this->navbar_type = 'solid';
        $this->navbar_gradient_start = '#1e3a8a';
        $this->navbar_gradient_end = '#000000';
        $this->navbar_gradient_direction = 'to right';

        session()->flash('message', 'Valores por defecto restablecidos. Pulsa "Guardar" para aplicar.');
    }

    public function save()
    {
        // Validar color solo si es sólido (hexadecimal). Si es degradado, omitimos la validación regex.
        $colorRule = $this->navbar_type === 'solid' ? 'required|regex:/^#[a-fA-F0-9]{6}$/' : 'required';

        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => $colorRule,
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
            } catch (\Exception $e) {
                session()->flash('error', 'Error al guardar la imagen: ' . $e->getMessage());
                return;
            }
        }

        // Asegurar que el color esté construido correctamente antes de guardar
        $this->buildNavbarColor();

        // Guardar configuraciones estándar
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

        // Guardar configuraciones adicionales de degradado
        try {
            Setting::set('navbar_type', $this->navbar_type, 'general', 'string');
            Setting::set('navbar_gradient_start', $this->navbar_gradient_start, 'general', 'string');
            Setting::set('navbar_gradient_end', $this->navbar_gradient_end, 'general', 'string');
            Setting::set('navbar_gradient_direction', $this->navbar_gradient_direction, 'general', 'string');
            Setting::set('theme_presets', json_encode($this->presets), 'general', 'json');
        } catch (\Exception $e) {
            // Manejar error si es necesario
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

    // --- FUNCIONES DE PRESETS ---

    public function savePreset()
    {
        $this->validate([
            'new_preset_name' => 'required|string|min:3|max:30'
        ]);

        $this->buildNavbarColor();

        $themeData = [
            'name' => $this->new_preset_name,
            'color' => $this->state['brand_primary_color'], // CSS value final
            'type' => $this->navbar_type,
            'gradient_data' => [
                'start' => $this->navbar_gradient_start,
                'end' => $this->navbar_gradient_end,
                'direction' => $this->navbar_gradient_direction,
            ]
        ];

        $this->presets[] = $themeData;
        
        // Guardamos inmediatamente en la propiedad temporal, se persistirá al dar clic en "Guardar Configuraciones"
        // Opcionalmente podemos guardar solo presets ahora:
        try {
            Setting::set('theme_presets', json_encode($this->presets), 'general', 'json');
            Cache::flush(); // Limpiar cache para asegurar que otros componentes lo vean si es necesario
        } catch(\Exception $e) {}

        $this->new_preset_name = '';
        $this->dispatch('notify', 'Preset guardado (No olvides guardar cambios globales).');
    }

    public function loadPreset($index)
    {
        if (isset($this->presets[$index])) {
            $preset = $this->presets[$index];
            
            $this->navbar_type = $preset['type'] ?? 'solid';
            
            if ($this->navbar_type === 'gradient') {
                $this->navbar_gradient_start = $preset['gradient_data']['start'] ?? '#ffffff';
                $this->navbar_gradient_end = $preset['gradient_data']['end'] ?? '#000000';
                $this->navbar_gradient_direction = $preset['gradient_data']['direction'] ?? 'to right';
            } else {
                $this->navbar_gradient_start = $preset['color']; // Si es sólido, el color principal es el start
                $this->state['brand_primary_color'] = $preset['color'];
            }

            $this->buildNavbarColor();
        }
    }

    public function deletePreset($index)
    {
        if (isset($this->presets[$index])) {
            unset($this->presets[$index]);
            $this->presets = array_values($this->presets); // Reindexar
            
            try {
                Setting::set('theme_presets', json_encode($this->presets), 'general', 'json');
                Cache::flush();
            } catch(\Exception $e) {}
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}