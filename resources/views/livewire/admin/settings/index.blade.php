<div>
    <style>
        /* ... (Estilos CSS anteriores se mantienen igual) ... */
        .sys-settings-wrapper { font-family: system-ui, sans-serif; background-color: #f9fafb; min-height: 100vh; padding-bottom: 3rem; color: #111827; }
        .sys-header-container { display: flex; flex-direction: column; gap: 0.5rem; }
        @media (min-width: 640px) { .sys-header-container { flex-direction: row; align-items: center; justify-content: space-between; } }
        .sys-title { font-size: 1.5rem; font-weight: 700; margin: 0; color: #111827; letter-spacing: -0.025em; }
        .sys-subtitle { margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280; }
        .sys-layout { max-width: 98%; margin: 2rem auto 0 auto; padding: 0 1rem; display: flex; flex-direction: column; gap: 1.5rem; }
        @media (min-width: 768px) { .sys-layout { flex-direction: row; padding: 0 1.5rem; } }
        .sys-sidebar { width: 100%; flex-shrink: 0; }
        @media (min-width: 768px) { .sys-sidebar { width: 16rem; } }
        .sys-nav { display: flex; flex-direction: column; gap: 0.25rem; }
        .sys-tab { display: flex; align-items: center; width: 100%; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.75rem; border: none; background: transparent; color: #4b5563; cursor: pointer; transition: all 0.2s ease-in-out; text-align: left; }
        .sys-tab:hover { background-color: #f3f4f6; color: #111827; }
        .sys-tab.active { background-color: #eef2ff; color: #4338ca; }
        .sys-tab-icon { width: 1.25rem; height: 1.25rem; margin-right: 0.75rem; color: #9ca3af; transition: color 0.2s; }
        .sys-tab:hover .sys-tab-icon { color: #6b7280; }
        .sys-tab.active .sys-tab-icon { color: #4338ca; }
        .sys-content { flex: 1; background-color: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); overflow: hidden; }
        .sys-alert-success { padding: 1rem 1.5rem; background-color: #ecfdf5; border-bottom: 1px solid #a7f3d0; color: #065f46; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; }
        .sys-alert-icon { width: 1.25rem; height: 1.25rem; margin-right: 0.5rem; color: #10b981; }
        .sys-error-text { color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.25rem 0 0 0; }
        .sys-input-error { border-color: #fca5a5 !important; background-color: #fef2f2; }
        .sys-input-error:focus { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important; }
        .sys-form-body { padding: 1.5rem; }
        @media (min-width: 640px) { .sys-form-body { padding: 2rem; } }
        .sys-section { margin-bottom: 2rem; }
        .sys-section:last-child { margin-bottom: 0; }
        .sys-section-title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem; }
        .sys-section-icon { width: 1.5rem; height: 1.5rem; }
        .sys-wp-logo { height: 1.5rem; width: auto; filter: grayscale(100%); opacity: 0.7; }
        .sys-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; background-color: #f9fafb; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #f3f4f6; }
        @media (min-width: 768px) { .sys-grid-2 { grid-template-columns: repeat(2, 1fr); } }
        .sys-col-full { grid-column: 1 / -1; }
        .sys-form-group { display: flex; flex-direction: column; gap: 0.375rem; }
        .sys-label { font-size: 0.875rem; font-weight: 600; color: #374151; margin: 0; }
        .sys-input { width: 100%; padding: 0.625rem 0.75rem; font-size: 0.875rem; color: #111827; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 0.5rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background-color 0.15s; outline: none; }
        .sys-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
        .sys-input::placeholder { color: #9ca3af; }
        .sys-help-text { font-size: 0.75rem; color: #6b7280; margin: 0; }
        .sys-color-wrapper { display: flex; align-items: center; gap: 1rem; }
        .sys-color-input { height: 3rem; width: 4rem; padding: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; background: #fff; }
        .sys-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 1rem; }
        .sys-btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.5rem; font-size: 0.875rem; font-weight: 700; color: #ffffff; background-color: #4f46e5; border: 1px solid transparent; border-radius: 0.75rem; cursor: pointer; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s; }
        .sys-btn-primary:hover { background-color: #4338ca; }
        .sys-btn-primary:focus { outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4); }
        .sys-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .sys-btn-secondary { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.5rem; font-size: 0.875rem; font-weight: 600; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 0.75rem; cursor: pointer; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s; }
        .sys-btn-secondary:hover { background-color: #f3f4f6; color: #111827; }
        .sys-fade-in { animation: sysFadeIn 0.3s ease-in-out; }
        @keyframes sysFadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .sys-spin { animation: sysSpin 1s linear infinite; }
        @keyframes sysSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    <div class="sys-settings-wrapper">
        
        <x-slot name="header">
            <div class="sys-header-container">
                <div>
                    <h1 class="sys-title">Configuración del Sistema</h1>
                    <p class="sys-subtitle">Administra las integraciones, APIs y parámetros globales del sistema.</p>
                </div>
            </div>
        </x-slot>

        <div class="sys-layout">
            
            <div class="sys-sidebar">
                <nav class="sys-nav">
                    <button type="button" wire:click="$set('activeTab', 'general')" class="sys-tab {{ $activeTab === 'general' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                        Personalización e Identidad
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'apis')" class="sys-tab {{ $activeTab === 'apis' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                        Integraciones API
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'finance')" class="sys-tab {{ $activeTab === 'finance' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        Pasarelas y Finanzas
                    </button>
                </nav>
            </div>

            <div class="sys-content">
                <form wire:submit.prevent="save">
                    @if (session()->has('message'))
                        <div class="sys-alert-success">
                            <svg class="sys-alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="sys-form-body">
                        
                        <div x-show="$wire.activeTab === 'general'" class="sys-fade-in" style="display: none;">
                            <div class="sys-section">
                                <h3 class="sys-section-title">
                                    <svg class="sys-section-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    Identidad de la Institución
                                </h3>
                                <div class="sys-grid">
                                    <div class="sys-form-group">
                                        <label class="sys-label">Nombre del Sistema / Colegio</label>
                                        <input type="text" wire:model="state.institution_name" class="sys-input @error('state.institution_name') sys-input-error @enderror">
                                        @error('state.institution_name') <p class="sys-error-text">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sys-form-group">
                                        <label class="sys-label">Correo de Soporte/Contacto</label>
                                        <input type="email" wire:model="state.support_email" class="sys-input @error('state.support_email') sys-input-error @enderror">
                                        @error('state.support_email') <p class="sys-error-text">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- APARIENCIA DEL MENÚ -->
                                    <div class="sys-form-group sys-col-full p-4 border rounded bg-white">
                                        <h4 class="font-semibold text-gray-700 mb-3">Estilo del Menú Lateral (Sidebar)</h4>
                                        
                                        <div class="flex flex-col gap-4">
                                            <div class="flex items-center gap-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="navbar_type" value="solid" class="form-radio text-indigo-600">
                                                    <span class="ml-2 text-sm text-gray-700">Color Sólido</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="navbar_type" value="gradient" class="form-radio text-indigo-600">
                                                    <span class="ml-2 text-sm text-gray-700">Degradado</span>
                                                </label>
                                            </div>

                                            @if($navbar_type === 'solid')
                                                <div class="sys-form-group">
                                                    <label class="sys-label">Color Institucional</label>
                                                    <div class="sys-color-wrapper">
                                                        <input type="color" wire:model.live="state.brand_primary_color" class="sys-color-input">
                                                        <input type="text" wire:model.live="state.brand_primary_color" class="sys-input uppercase w-32" placeholder="#1E3A8A">
                                                    </div>
                                                    @error('state.brand_primary_color') <p class="sys-error-text">{{ $message }}</p> @enderror
                                                </div>
                                            @else
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div class="sys-form-group">
                                                        <label class="sys-label">Color Inicio</label>
                                                        <div class="sys-color-wrapper">
                                                            <input type="color" wire:model.live="navbar_gradient_start" class="sys-color-input">
                                                        </div>
                                                    </div>
                                                    <div class="sys-form-group">
                                                        <label class="sys-label">Color Fin</label>
                                                        <div class="sys-color-wrapper">
                                                            <input type="color" wire:model.live="navbar_gradient_end" class="sys-color-input">
                                                        </div>
                                                    </div>
                                                    <div class="sys-form-group">
                                                        <label class="sys-label">Dirección</label>
                                                        <select wire:model.live="navbar_gradient_direction" class="sys-input">
                                                            <option value="to right">Izquierda a Derecha (&rarr;)</option>
                                                            <option value="to left">Derecha a Izquierda (&larr;)</option>
                                                            <option value="to bottom">Arriba a Abajo (&darr;)</option>
                                                            <option value="to top">Abajo a Arriba (&uarr;)</option>
                                                            <option value="to bottom right">Diagonal (&searr;)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- VISTA PREVIA -->
                                            <div class="mt-2">
                                                <label class="sys-label block mb-1">Vista Previa:</label>
                                                <div class="h-12 w-full rounded-lg shadow-sm flex items-center px-4 text-white font-bold" 
                                                     style="background: {{ $navbar_type === 'solid' ? $state['brand_primary_color'] : 'linear-gradient('.$navbar_gradient_direction.', '.$navbar_gradient_start.', '.$navbar_gradient_end.')' }};">
                                                    SGA Academic+
                                                </div>
                                            </div>

                                            <!-- GESTIÓN DE PRESETS -->
                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                <label class="sys-label mb-2 block">Presets / Temas Guardados</label>
                                                <div class="flex gap-2 mb-3">
                                                    <input type="text" wire:model="new_preset_name" placeholder="Nombre para guardar actual..." class="sys-input text-sm h-9">
                                                    <button type="button" wire:click="savePreset" class="sys-btn-secondary text-xs h-9 whitespace-nowrap">Guardar Preset</button>
                                                </div>
                                                
                                                <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto">
                                                    @forelse($presets as $index => $preset)
                                                        <div class="flex items-center bg-gray-100 rounded-lg p-1 pr-2 border border-gray-300">
                                                            <div class="w-6 h-6 rounded mr-2 border border-white shadow-sm" style="background: {{ $preset['color'] }}"></div>
                                                            <span class="text-xs font-medium text-gray-700 mr-2">{{ $preset['name'] }}</span>
                                                            <button type="button" wire:click="loadPreset({{ $index }})" class="text-blue-600 hover:text-blue-800 p-1" title="Cargar">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                            </button>
                                                            <button type="button" wire:click="deletePreset({{ $index }})" wire:confirm="¿Borrar preset?" class="text-red-500 hover:text-red-700 p-1" title="Eliminar">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </div>
                                                    @empty
                                                        <span class="text-xs text-gray-400 italic">No hay presets guardados.</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sys-form-group sys-col-full">
                                        <label class="sys-label">Logotipo</label>
                                        <div class="flex items-center gap-4 p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                                            <div class="shrink-0">
                                                @if ($logo)
                                                    <img src="{{ $logo->temporaryUrl() }}" class="h-16 w-auto object-contain rounded p-1 shadow-sm">
                                                @elseif (!empty($state['institution_logo']))
                                                    <img src="{{ $state['institution_logo'] }}" class="h-16 w-auto object-contain rounded p-1 shadow-sm">
                                                @else
                                                    <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center text-gray-400 font-bold text-xs">Sin Logo</div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <input type="file" wire:model="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                                <div wire:loading wire:target="logo" class="text-xs text-indigo-500 mt-1 font-semibold">Subiendo imagen...</div>
                                                <p class="sys-help-text mt-1">Recomendado: PNG Transparente. Máx 2MB.</p>
                                            </div>
                                        </div>
                                        @error('logo') <p class="sys-error-text">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="$wire.activeTab === 'apis'" class="sys-fade-in" style="display: none;">
                            <div class="sys-section">
                                <h3 class="sys-section-title">
                                    <img src="https://s.w.org/style/images/about/WordPress-logotype-wmark.png" class="sys-wp-logo" alt="WP">
                                    Conexión WordPress
                                </h3>
                                <div class="sys-grid">
                                    <div class="sys-form-group">
                                        <label class="sys-label">URL Base de la API</label>
                                        <input type="text" wire:model="state.wp_api_url" class="sys-input">
                                    </div>
                                    <div class="sys-form-group">
                                        <label class="sys-label">Token Secreto</label>
                                        <input type="password" wire:model="state.wp_api_secret" class="sys-input">
                                    </div>
                                </div>
                            </div>
                            <hr class="sys-divider">
                            <div class="sys-section">
                                <h3 class="sys-section-title">
                                    <svg class="sys-section-icon" style="color: #f98012;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.09 14.08h-2.18V11.2h2.18v4.88zm0-6.1h-2.18V7.81h2.18v2.17z"/></svg>
                                    Conexión Moodle
                                </h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group sys-col-full">
                                        <label class="sys-label">URL de Moodle</label>
                                        <input type="text" wire:model="state.moodle_url" class="sys-input">
                                    </div>
                                    <div class="sys-form-group sys-col-full">
                                        <label class="sys-label">Token</label>
                                        <input type="password" wire:model="state.moodle_token" class="sys-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="$wire.activeTab === 'finance'" class="sys-fade-in" style="display: none;">
                            <div class="sys-section">
                                <h3 class="sys-section-title">Cardnet</h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group">
                                        <label class="sys-label">Merchant ID</label>
                                        <input type="text" wire:model="state.cardnet_merchant_id" class="sys-input">
                                    </div>
                                    <div class="sys-form-group">
                                        <label class="sys-label">Terminal ID</label>
                                        <input type="text" wire:model="state.cardnet_terminal_id" class="sys-input">
                                    </div>
                                </div>
                            </div>
                            <div class="sys-section">
                                <h3 class="sys-section-title">DGII</h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group">
                                        <label class="sys-label">RNC Emisor</label>
                                        <input type="text" wire:model="state.ecf_rnc_emisor" class="sys-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sys-footer">
                        {{-- Botón para restaurar valores por defecto --}}
                        <button type="button" wire:click="restoreDefaults" wire:confirm="¿Estás seguro de que quieres restablecer los colores y el logo a los valores originales del sistema?" class="sys-btn-secondary">
                            Restaurar Por Defecto
                        </button>

                        <button type="submit" wire:loading.attr="disabled" class="sys-btn-primary">
                            <span wire:loading.remove>Guardar Configuraciones</span>
                            <span wire:loading style="display: none; align-items: center; gap: 0.5rem;">
                                <svg class="sys-spin" style="height: 1rem; width: 1rem; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Guardando...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>