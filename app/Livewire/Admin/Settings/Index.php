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
use App\Services\WordpressApiService;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithFileUploads;

    public $activeTab = 'general'; 

    public $dirtyKeys = [];

    public $logo;
    public $favicon;
    public $app_icon;

    public $state = [];

    // Propiedades para la gestión de degradados
    public $navbar_type = 'solid'; // 'solid' o 'gradient'
    public $navbar_gradient_start = '#1e3a8a';
    public $navbar_gradient_end = '#000000';
    public $navbar_gradient_direction = 'to right';

    // Propiedades para Presets
    public $presets = [];
    public $new_preset_name = '';

    // Propiedades para Enlace Rápido WordPress
    public $pairingCode;
    public $pairingCodeExpiresAt;

    public function switchTab($tabName)
    {
        // Si hay cambios sucios, guardarlos automáticamente antes de cambiar de pestaña
        if (!empty($this->dirtyKeys)) {
            $this->save(false);
        }
        $this->activeTab = $tabName;
    }

    public function mount()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Verificar código de emparejamiento activo
        $pairingDataJson = $settings['wp_pairing_data'] ?? null;
        if (!empty($pairingDataJson)) {
            $pairingData = json_decode($pairingDataJson, true);
            if (is_array($pairingData) && isset($pairingData['code']) && now()->timestamp <= $pairingData['expires_at']) {
                $this->pairingCode = $pairingData['code'];
                $this->pairingCodeExpiresAt = $pairingData['expires_at'];
            }
        }

        $this->state = [
            'institution_name'    => $settings['institution_name'] ?? config('app.name'),
            'institution_logo'    => $settings['institution_logo'] ?? null,
            'favicon'             => $settings['favicon'] ?? null,
            'app_icon'            => $settings['app_icon'] ?? null,
            'brand_primary_color' => $settings['brand_primary_color'] ?? '#1e3a8a',
            'support_email'       => $settings['support_email'] ?? 'soporte@institucion.edu',
            'contact_phone'       => $settings['contact_phone'] ?? '',
            'contact_address'     => $settings['contact_address'] ?? '',
            'website_url'         => $settings['website_url'] ?? '',
            'social_facebook'     => $settings['social_facebook'] ?? '',
            'social_instagram'    => $settings['social_instagram'] ?? '',
            'enable_careers'      => $settings['enable_careers'] ?? 'true',
            'smtp_host'           => !empty($settings['smtp_host']) ? $settings['smtp_host'] : env('MAIL_HOST', '127.0.0.1'),
            'smtp_port'           => !empty($settings['smtp_port']) ? $settings['smtp_port'] : env('MAIL_PORT', 2525),
            'smtp_username'       => !empty($settings['smtp_username']) ? $settings['smtp_username'] : env('MAIL_USERNAME', ''),
            'smtp_password'       => !empty($settings['smtp_password']) ? $settings['smtp_password'] : env('MAIL_PASSWORD', ''),
            'smtp_encryption'     => !empty($settings['smtp_encryption']) ? $settings['smtp_encryption'] : env('MAIL_ENCRYPTION', 'tls'),
            'smtp_from_address'   => !empty($settings['smtp_from_address']) ? $settings['smtp_from_address'] : env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'wp_api_url'    => $settings['wp_api_url'] ?? '',
            'wp_api_secret' => $settings['wp_api_secret'] ?? '',
            'moodle_url'    => $settings['moodle_url'] ?? '',
            'moodle_token'  => $settings['moodle_token'] ?? '',
            'cardnet_merchant_id' => $settings['cardnet_merchant_id'] ?? '',
            'cardnet_terminal_id' => $settings['cardnet_terminal_id'] ?? '',
            'ecf_rnc_emisor'      => $settings['ecf_rnc_emisor'] ?? '101000000',
            'enable_electronic_billing' => ($settings['enable_electronic_billing'] ?? 'true') === 'true',
            'enable_bills_invoicing'    => ($settings['enable_bills_invoicing'] ?? 'false') === 'true',
            'bills_api_url'             => $settings['bills_api_url'] ?? '',
            'bills_api_token'           => $settings['bills_api_token'] ?? '',
            'bills_default_tax_rate'    => $settings['bills_default_tax_rate'] ?? '0',
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
        // Registrar cambios en el array state
        if (str_starts_with($propertyName, 'state.')) {
            $key = str_replace('state.', '', $propertyName);
            $this->dirtyKeys[$key] = true;
        }

        // Registrar cambios en archivos y marcar correspondientes como sucios
        if (in_array($propertyName, ['logo', 'favicon', 'app_icon'])) {
            $this->dirtyKeys[$propertyName] = true;
        }

        // Construir el color en tiempo real si cambian las propiedades del degradado
        if (in_array($propertyName, ['navbar_type', 'navbar_gradient_start', 'navbar_gradient_end', 'navbar_gradient_direction'])) {
            $this->dirtyKeys[$propertyName] = true;
            $this->buildNavbarColor();
        }
        
        // Si el usuario cambia el color en modo sólido directamente
        if ($propertyName === 'state.brand_primary_color' && $this->navbar_type === 'solid') {
            $this->navbar_gradient_start = $this->state['brand_primary_color'];
            $this->dirtyKeys['navbar_gradient_start'] = true;
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
        $this->state['favicon'] = null;
        $this->state['app_icon'] = null;
        $this->state['support_email'] = 'soporte@institucion.edu';
        $this->state['contact_phone'] = '';
        $this->state['contact_address'] = '';
        $this->state['website_url'] = '';
        $this->state['social_facebook'] = '';
        $this->state['social_instagram'] = '';
        $this->state['enable_careers'] = 'true';
        $this->state['smtp_host'] = '';
        $this->state['smtp_port'] = '587';
        $this->state['smtp_username'] = '';
        $this->state['smtp_password'] = '';
        $this->state['smtp_encryption'] = 'tls';
        $this->state['smtp_from_address'] = '';
        $this->state['enable_bills_invoicing'] = 'false';
        $this->state['bills_api_url'] = '';
        $this->state['bills_api_token'] = '';
        $this->state['bills_default_tax_rate'] = '0';
        $this->logo = null;
        $this->favicon = null;
        $this->app_icon = null;

        // Restaurar valores de degradado
        $this->navbar_type = 'solid';
        $this->navbar_gradient_start = '#1e3a8a';
        $this->navbar_gradient_end = '#000000';
        $this->navbar_gradient_direction = 'to right';

        // Marcar todas las claves como modificadas (sucias)
        foreach ($this->state as $key => $val) {
            $this->dirtyKeys[$key] = true;
        }
        $this->dirtyKeys['navbar_type'] = true;
        $this->dirtyKeys['navbar_gradient_start'] = true;
        $this->dirtyKeys['navbar_gradient_end'] = true;
        $this->dirtyKeys['navbar_gradient_direction'] = true;

        return $this->save();
    }

    public function removeLogo()
    {
        $this->state['institution_logo'] = null;
        $this->logo = null;
        
        try {
            Setting::set('institution_logo', null, 'general', 'image');
            Cache::flush();
            session()->flash('message', 'Logotipo eliminado correctamente. Se utilizará el logo por defecto o formato de texto.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el logotipo: ' . $e->getMessage());
        }
    }

    public function removeFavicon()
    {
        $this->state['favicon'] = null;
        $this->favicon = null;
        
        try {
            Setting::set('favicon', null, 'general', 'image');
            Cache::flush();
            session()->flash('message', 'Favicon eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el favicon: ' . $e->getMessage());
        }
    }

    public function removeAppIcon()
    {
        $this->state['app_icon'] = null;
        $this->app_icon = null;
        
        try {
            Setting::set('app_icon', null, 'general', 'image');
            Cache::flush();
            session()->flash('message', 'Icono de app eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el icono: ' . $e->getMessage());
        }
    }

    public function save($shouldRedirect = true)
    {
        // Si no hay nada modificado, omitir proceso y retornar temprano
        if (empty($this->dirtyKeys)) {
            session()->flash('message', 'No hay cambios para guardar.');
            if ($shouldRedirect) {
                return redirect()->route('admin.settings.index');
            }
            return;
        }

        // NOTA: Si los archivos no se suben, verifica estos límites en php.ini:
        // - upload_max_filesize (mínimo 2MB)
        // - post_max_size (mínimo 8MB)
        // - max_file_uploads (mínimo 20)
        // También revisa los permisos del directorio public/branding (755)

        // Validar color solo si es sólido (hexadecimal). Si es degradado, omitimos la validación regex.
        $colorRule = $this->navbar_type === 'solid' ? 'required|regex:/^#[a-fA-F0-9]{6}$/' : 'required';

        $this->validate([
            'state.institution_name'    => 'required|string|max:100',
            'state.brand_primary_color' => $colorRule,
            'state.support_email'       => 'nullable|email',
            'state.contact_phone'       => 'nullable|string|max:50',
            'state.contact_address'     => 'nullable|string|max:255',
            'state.website_url'         => 'nullable|url|max:255',
            'state.social_facebook'     => 'nullable|url|max:255',
            'state.social_instagram'    => 'nullable|url|max:255',
            'state.enable_careers'      => 'in:true,false',
            'state.smtp_host'           => 'nullable|string|max:255',
            'state.smtp_port'           => 'nullable|numeric',
            'state.smtp_username'       => 'nullable|string|max:255',
            'state.smtp_password'       => 'nullable|string|max:255',
            'state.smtp_encryption'     => 'nullable|string|max:50',
            'state.smtp_from_address'   => 'nullable|email|max:255',
            'logo'                      => 'nullable|image|max:2048',
            'favicon'                   => 'nullable|image|mimes:png,ico,webp|max:1024',
            'app_icon'                  => 'nullable|image|mimes:png,webp|max:1024',
            'state.wp_api_url'          => 'nullable|url',
            'state.moodle_url'          => 'nullable|url',
            'state.enable_electronic_billing' => 'boolean',
            'state.enable_bills_invoicing' => 'boolean',
            'state.bills_api_url'       => 'nullable|url',
            'state.bills_api_token'     => 'nullable|string',
            'state.bills_default_tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($this->logo) {
            try {
                // Asegurar que el directorio existe
                $brandingPath = public_path('branding');
                if (!file_exists($brandingPath)) {
                    mkdir($brandingPath, 0755, true);
                }
                
                $filename = 'logo_' . time() . '.' . $this->logo->getClientOriginalExtension();
                $this->logo->storeAs('branding', $filename, 'hosting_public');
                $url = "/branding/" . $filename;
                $this->state['institution_logo'] = $url;
                $this->dirtyKeys['institution_logo'] = true;
            } catch (\Exception $e) {
                session()->flash('error', 'Error al guardar la imagen: ' . $e->getMessage());
                return;
            }
        }

        if ($this->favicon) {
            try {
                // Asegurar que el directorio existe
                $brandingPath = public_path('branding');
                if (!file_exists($brandingPath)) {
                    mkdir($brandingPath, 0755, true);
                }
                
                Log::info('Guardando favicon', [
                    'original_name' => $this->favicon->getClientOriginalName(),
                    'extension' => $this->favicon->getClientOriginalExtension(),
                    'size' => $this->favicon->getSize()
                ]);
                
                $filename = 'favicon_' . time() . '.' . $this->favicon->getClientOriginalExtension();
                $this->favicon->storeAs('branding', $filename, 'hosting_public');
                $url = "/branding/" . $filename;
                $this->state['favicon'] = $url;
                $this->dirtyKeys['favicon'] = true;
                
                Log::info('Favicon guardado exitosamente', ['url' => $url]);
            } catch (\Exception $e) {
                Log::error('Error guardando favicon', ['error' => $e->getMessage()]);
                session()->flash('error', 'Error al guardar el favicon: ' . $e->getMessage());
                return;
            }
        }

        if ($this->app_icon) {
            try {
                // Asegurar que el directorio existe
                $brandingPath = public_path('branding');
                if (!file_exists($brandingPath)) {
                    mkdir($brandingPath, 0755, true);
                }
                
                $filename = 'app_icon_' . time() . '.' . $this->app_icon->getClientOriginalExtension();
                $this->app_icon->storeAs('branding', $filename, 'hosting_public');
                $url = "/branding/" . $filename;
                $this->state['app_icon'] = $url;
                $this->dirtyKeys['app_icon'] = true;
            } catch (\Exception $e) {
                session()->flash('error', 'Error al guardar el icono de app: ' . $e->getMessage());
                return;
            }
        }

        // Asegurar que el color esté construido correctamente antes de guardar
        $this->buildNavbarColor();

        // Guardar configuraciones estándar modificadas
        foreach ($this->state as $key => $value) {
            if (!isset($this->dirtyKeys[$key])) {
                continue; // Solo guardar lo modificado
            }

            // Evitar sobreescribir credenciales con vacío debido a pestañas desactualizadas
            if (in_array($key, ['wp_api_url', 'wp_api_secret', 'moodle_url', 'moodle_token', 'bills_api_url', 'bills_api_token'])) {
                if (empty($value) && !empty(Setting::val($key))) {
                    continue;
                }
            }

            // Normalizar booleano a string 'true'/'false'
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            
            $type = str_contains($key, 'secret') || str_contains($key, 'token') || str_contains($key, 'api_token') ? 'password' : 'string';
            if (in_array($key, ['institution_logo', 'favicon', 'app_icon'])) $type = 'image';

            $group = 'general';
            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_') || str_starts_with($key, 'bills_') || $key === 'enable_bills_invoicing') $group = 'apis';
            if (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_')) $group = 'finance';

            try {
                Setting::set($key, $value, $group, $type);
            } catch (\Exception $e) {
                // Log::error("Error guardando $key: " . $e->getMessage());
            }
        }

        // Guardar configuraciones adicionales de degradado
        try {
            if (isset($this->dirtyKeys['navbar_type'])) {
                Setting::set('navbar_type', $this->navbar_type, 'general', 'string');
            }
            if (isset($this->dirtyKeys['navbar_gradient_start'])) {
                Setting::set('navbar_gradient_start', $this->navbar_gradient_start, 'general', 'string');
            }
            if (isset($this->dirtyKeys['navbar_gradient_end'])) {
                Setting::set('navbar_gradient_end', $this->navbar_gradient_end, 'general', 'string');
            }
            if (isset($this->dirtyKeys['navbar_gradient_direction'])) {
                Setting::set('navbar_gradient_direction', $this->navbar_gradient_direction, 'general', 'string');
            }
        } catch (\Exception $e) {
            // Manejar error si es necesario
        }

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Configuración del Sistema',
                'description' => 'Actualizó campos de configuración específicos: ' . implode(', ', array_keys($this->dirtyKeys)),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        // === WEBHOOK: Sincronizar branding con WordPress en tiempo real ===
        // Solo disparar si se modificó algún parámetro relacionado con colores/branding
        $colorKeys = ['brand_primary_color', 'navbar_type', 'navbar_gradient_start', 'navbar_gradient_end', 'navbar_gradient_direction'];
        $brandingModified = false;
        foreach ($colorKeys as $ck) {
            if (isset($this->dirtyKeys[$ck])) {
                $brandingModified = true;
                break;
            }
        }

        if ($brandingModified) {
            try {
                $wpApiUrl = $this->state['wp_api_url'] ?? Setting::val('wp_api_url');
                $wpApiSecret = $this->state['wp_api_secret'] ?? Setting::val('wp_api_secret');

                if (!empty($wpApiUrl) && !empty($wpApiSecret)) {
                    $brandingEndpoint = rtrim($wpApiUrl, '/');
                    // Normalizar: si termina en /wp-json/ usamos esa base, sino construimos
                    if (str_contains($brandingEndpoint, '/wp-json')) {
                        $brandingEndpoint = preg_replace('#/wp-json/.*$#', '/wp-json/sga/v1/update-branding/', $brandingEndpoint);
                    } else {
                        $brandingEndpoint .= '/wp-json/sga/v1/update-branding/';
                    }

                    $brandingData = [
                        'branding' => [
                            'brand_primary_color'       => $this->state['brand_primary_color'],
                            'navbar_type'               => $this->navbar_type,
                            'navbar_gradient_start'      => $this->navbar_gradient_start,
                            'navbar_gradient_end'        => $this->navbar_gradient_end,
                            'navbar_gradient_direction'   => $this->navbar_gradient_direction,
                        ]
                    ];

                    \Illuminate\Support\Facades\Http::withHeaders([
                        'X-SGA-Signature' => $wpApiSecret,
                        'Content-Type' => 'application/json',
                    ])->timeout(5)->post($brandingEndpoint, $brandingData);
                }
            } catch (\Exception $e) {
                // Silencioso: no bloquear el guardado si WP no responde
                \Illuminate\Support\Facades\Log::warning('Webhook branding a WP falló: ' . $e->getMessage());
            }
        }
        // === FIN WEBHOOK ===

        Cache::flush();

        // Limpiar llaves modificadas ya guardadas
        $this->dirtyKeys = [];

        // Limpiar propiedades temporales de archivos
        $this->logo = null;
        $this->favicon = null;
        $this->app_icon = null;

        session()->flash('message', 'Configuración guardada correctamente.');
        
        if ($shouldRedirect) {
            return redirect()->route('admin.settings.index');
        }
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

    public function generatePairingCode()
    {
        // 1. Generar código único aleatorio de 6 caracteres
        $code = 'SGA-' . strtoupper(\Illuminate\Support\Str::random(6));

        // 2. Obtener/crear token de Sanctum para el usuario actual o primer administrador
        $user = Auth::user();
        if (!$user) {
            $user = \App\Models\User::role('administrator')->first() ?? \App\Models\User::first();
        }

        if (!$user) {
            session()->flash('error', 'No se pudo generar el token: no hay usuarios registrados.');
            return;
        }

        // Eliminar tokens previos con el mismo nombre para no acumular basura
        $tokenName = 'WordPress Catalog Integration';
        $user->tokens()->where('name', $tokenName)->delete();

        // Crear nuevo token
        $token = $user->createToken($tokenName)->plainTextToken;

        // 3. Generar secreto de firma
        $wp_api_secret = \Illuminate\Support\Str::random(40);

        // 4. Guardar datos de emparejamiento con validez de 15 minutos
        $expiresAt = now()->addMinutes(15);
        $pairingData = [
            'code' => $code,
            'api_token' => $token,
            'wp_api_secret' => $wp_api_secret,
            'expires_at' => $expiresAt->timestamp
        ];

        Setting::set('wp_pairing_data', json_encode($pairingData), 'apis', 'json');

        $this->pairingCode = $code;
        $this->pairingCodeExpiresAt = $expiresAt->timestamp;

        session()->flash('message', '¡Código de enlace generado con éxito! Úsalo en WordPress antes de que expire.');
    }

    /**
     * Sincroniza manualmente los estudiantes de WordPress en Laravel sin enviar ningún correo electrónico.
     */
    public function syncWordPressStudents(WordpressApiService $wpService)
    {
        @set_time_limit(300);
        try {
            $wpStudents = $wpService->getSgaStudents();
            if (empty($wpStudents)) {
                session()->flash('wp_sync_error', 'No se encontraron estudiantes en WordPress o la conexión falló.');
                return;
            }

            // Pre-calcular el hash de la contraseña por defecto ('123456') para ahorrar CPU en el bucle
            $defaultPasswordHash = \Illuminate\Support\Facades\Hash::make('123456');
            $syncedCount = 0;

            // Desactivar temporalmente los eventos en los modelos críticos
            \App\Models\User::withoutEvents(function () use ($wpStudents, &$syncedCount, $defaultPasswordHash) {
                \App\Models\Student::withoutEvents(function () use ($wpStudents, &$syncedCount, $defaultPasswordHash) {
                    \App\Models\Enrollment::withoutEvents(function () use ($wpStudents, &$syncedCount, $defaultPasswordHash) {
                        \App\Models\Payment::withoutEvents(function () use ($wpStudents, &$syncedCount, $defaultPasswordHash) {
                            
                            foreach ($wpStudents as $wpStudent) {
                                $cedula = trim($wpStudent['cedula'] ?? '');
                                $email = trim($wpStudent['email'] ?? '');
                                if (empty($cedula) || empty($email)) {
                                    continue;
                                }

                                $cleanCedula = preg_replace('/[^0-9]/', '', $cedula);

                                // 1. Buscar o Crear Usuario
                                $user = \App\Models\User::where('email', $email)->first();
                                if (!$user) {
                                    $user = \App\Models\User::create([
                                        'name' => $wpStudent['nombre'] ?? 'Estudiante WP',
                                        'email' => $email,
                                        'password' => $defaultPasswordHash,
                                        'email_verified_at' => now(),
                                    ]);
                                    $user->assignRole('Estudiante');
                                }

                                // 2. Buscar o Crear Estudiante
                                $student = \App\Models\Student::where('cedula', $cedula)
                                    ->orWhere('cedula', $cleanCedula)
                                    ->orWhere('user_id', $user->id)
                                    ->first();

                                // Separar primer nombre y apellido
                                $parts = explode(' ', trim($wpStudent['nombre'] ?? ''));
                                $first_name = $parts[0] ?? 'Estudiante';
                                $last_name = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'WP';

                                if (!$student) {
                                    $student = \App\Models\Student::create([
                                        'user_id' => $user->id,
                                        'first_name' => $first_name,
                                        'last_name' => $last_name,
                                        'cedula' => $cedula,
                                        'email' => $email,
                                        'mobile_phone' => $wpStudent['telefono'] ?? null,
                                        'status' => 'Activa',
                                    ]);
                                }

                                // 3. Si tiene datos de curso, intentar matricularlo
                                if (!empty($wpStudent['curso']['nombre'])) {
                                    $wpCourseName = $wpStudent['curso']['nombre'];
                                    
                                    // Buscar curso en Laravel
                                    // A: Por mapeo de nombre
                                    $mapping = \App\Models\CourseMapping::where('wp_course_name', $wpCourseName)->first();
                                    $laravelCourse = null;
                                    if ($mapping) {
                                        $laravelCourse = $mapping->course;
                                    } else {
                                        // B: Por nombre exacto
                                        $laravelCourse = \App\Models\Course::where('name', $wpCourseName)->first();
                                    }

                                    if ($laravelCourse) {
                                        // Obtener primer módulo del curso
                                        $module = $laravelCourse->modules()->first();
                                        if ($module) {
                                            // Buscar o crear sección por defecto
                                            $schedule = $module->schedules()->first();
                                            if (!$schedule) {
                                                $schedule = \App\Models\CourseSchedule::create([
                                                    'module_id' => $module->id,
                                                    'teacher_id' => \App\Models\User::role('Profesor')->first()->id ?? 1,
                                                    'days_of_week' => ['Lunes'],
                                                    'section_name' => 'Sección Sincronizada',
                                                    'modality' => 'Presencial',
                                                    'start_time' => '18:00',
                                                    'end_time' => '21:00',
                                                    'start_date' => now()->format('Y-m-d'),
                                                    'end_date' => now()->addMonths(3)->format('Y-m-d'),
                                                ]);
                                            }

                                            // Crear inscripción si no existe
                                            $wpStatus = $wpStudent['curso']['estado'] ?? '';
                                            $laravelStatus = ($wpStatus === 'Matriculado' || $wpStatus === 'pagado') ? 'Cursando' : 'Pendiente';

                                            $enrollment = \App\Models\Enrollment::where('student_id', $student->id)
                                                ->where('course_schedule_id', $schedule->id)
                                                ->first();

                                            if (!$enrollment) {
                                                $enrollment = \App\Models\Enrollment::create([
                                                    'student_id' => $student->id,
                                                    'course_id' => $laravelCourse->id,
                                                    'course_schedule_id' => $schedule->id,
                                                    'status' => $laravelStatus,
                                                    'enrollment_date' => now(),
                                                ]);

                                                // Crear cargos contables asociados si es necesario (sin eventos de disparo)
                                                $amount = $laravelCourse->registration_fee ?? 0;
                                                $concept = \App\Models\PaymentConcept::firstOrCreate(['name' => 'Inscripción']);
                                                
                                                \App\Models\Payment::create([
                                                    'student_id' => $student->id,
                                                    'enrollment_id' => $enrollment->id,
                                                    'payment_concept_id' => $concept->id,
                                                    'amount' => $amount,
                                                    'currency' => 'DOP',
                                                    'status' => ($laravelStatus === 'Cursando') ? 'Completado' : 'Pendiente',
                                                    'gateway' => 'Por Pagar',
                                                    'due_date' => now()->addDays(3),
                                                ]);
                                                
                                                try {
                                                    app(\App\Services\AccountingEngine::class)->registerStudentDebt($enrollment, $amount);
                                                } catch (\Exception $e) {
                                                    Log::error("Accounting Engine Error during background sync: " . $e->getMessage());
                                                }
                                            }
                                        }
                                    }
                                }

                                $syncedCount++;
                            }
                        });
                    });
                });
            });

            // Log de la actividad
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Sincronización manual',
                'description' => "Se sincronizaron manualmente {$syncedCount} estudiantes desde WordPress a Laravel sin envío de correos electrónicos.",
                'ip_address' => request()->ip()
            ]);

            session()->flash('wp_sync_success', "¡Sincronización completada! Se procesaron {$syncedCount} estudiantes de WordPress correctamente (sin notificaciones de correo).");

        } catch (\Exception $e) {
            Log::error('Error al sincronizar estudiantes de WordPress: ' . $e->getMessage());
            session()->flash('wp_sync_error', 'Ocurrió un error inesperado al sincronizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}