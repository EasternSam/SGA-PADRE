<div>
    <style>
        /* ==========================================================================
           ESTILOS PUROS PARA EL MÓDULO DE AJUSTES GLOBALES
           (Encapsulados para evitar conflictos con Tailwind u otros frameworks)
           ========================================================================== */
        
        .sys-settings-wrapper {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
            min-height: 100vh;
            padding-bottom: 3rem;
            color: #111827;
            box-sizing: border-box;
        }

        .sys-settings-wrapper * {
            box-sizing: inherit;
        }

        /* Cabecera */
        .sys-header-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        @media (min-width: 640px) {
            .sys-header-container {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .sys-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: #111827;
            letter-spacing: -0.025em;
        }
        .sys-subtitle {
            margin: 0.25rem 0 0 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Layout Principal */
        .sys-layout {
            max-width: 98%;
            margin: 2rem auto 0 auto;
            padding: 0 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .sys-layout {
                flex-direction: row;
                padding: 0 1.5rem;
            }
        }

        /* Menú Lateral (Pestañas) */
        .sys-sidebar {
            width: 100%;
            flex-shrink: 0;
        }
        @media (min-width: 768px) {
            .sys-sidebar {
                width: 16rem; /* 256px */
            }
        }
        .sys-nav {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .sys-tab {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.75rem;
            border: none;
            background: transparent;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-align: left;
        }
        .sys-tab:hover {
            background-color: #f3f4f6;
            color: #111827;
        }
        .sys-tab.active {
            background-color: #eef2ff;
            color: #4338ca;
        }
        .sys-tab-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            color: #9ca3af;
            transition: color 0.2s;
        }
        .sys-tab:hover .sys-tab-icon {
            color: #6b7280;
        }
        .sys-tab.active .sys-tab-icon {
            color: #4338ca;
        }

        /* Contenedor Principal de Formularios */
        .sys-content {
            flex: 1;
            background-color: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Alertas */
        .sys-alert-success {
            padding: 1rem 1.5rem;
            background-color: #ecfdf5;
            border-bottom: 1px solid #a7f3d0;
            color: #065f46;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .sys-alert-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.5rem;
            color: #10b981;
        }

        /* Secciones del Formulario */
        .sys-form-body {
            padding: 1.5rem;
        }
        @media (min-width: 640px) {
            .sys-form-body {
                padding: 2rem;
            }
        }
        .sys-section {
            margin-bottom: 2rem;
        }
        .sys-section:last-child {
            margin-bottom: 0;
        }
        .sys-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sys-section-icon {
            width: 1.5rem;
            height: 1.5rem;
        }
        .sys-wp-logo {
            height: 1.5rem;
            width: auto;
            filter: grayscale(100%);
            opacity: 0.7;
        }

        /* Grid de Inputs */
        .sys-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            background-color: #f9fafb;
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #f3f4f6;
        }
        @media (min-width: 768px) {
            .sys-grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .sys-col-full {
            grid-column: 1 / -1;
        }

        /* Inputs y Labels */
        .sys-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        .sys-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }
        .sys-input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            color: #111827;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            outline: none;
        }
        .sys-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        .sys-input::placeholder {
            color: #9ca3af;
        }
        .sys-help-text {
            font-size: 0.75rem;
            color: #6b7280;
            margin: 0;
        }

        /* Footer y Botones */
        .sys-footer {
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
        }
        .sys-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #ffffff;
            background-color: #4f46e5;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        .sys-btn-primary:hover {
            background-color: #4338ca;
        }
        .sys-btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4);
        }
        .sys-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Utilidades */
        .sys-divider {
            border: 0;
            border-top: 1px solid #f3f4f6;
            margin: 2rem 0;
        }
        .sys-fade-in {
            animation: sysFadeIn 0.3s ease-in-out;
        }
        @keyframes sysFadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .sys-spin {
            animation: sysSpin 1s linear infinite;
        }
        @keyframes sysSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
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
            
            {{-- Sidebar Pestañas --}}
            <div class="sys-sidebar">
                <nav class="sys-nav">
                    <button type="button" wire:click="$set('activeTab', 'apis')" class="sys-tab {{ $activeTab === 'apis' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                        Integraciones API
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'finance')" class="sys-tab {{ $activeTab === 'finance' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        Pasarelas y Finanzas
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'general')" class="sys-tab {{ $activeTab === 'general' ? 'active' : '' }}">
                        <svg class="sys-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                        Datos Generales
                    </button>
                </nav>
            </div>

            {{-- Área de Contenido --}}
            <div class="sys-content">
                <form wire:submit.prevent="save">
                    
                    {{-- Alertas --}}
                    @if (session()->has('message'))
                        <div class="sys-alert-success">
                            <svg class="sys-alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="sys-form-body">
                        
                        {{-- ================= TAB: APIS ================= --}}
                        <div x-show="$wire.activeTab === 'apis'" class="sys-fade-in" style="display: none;">
                            
                            {{-- WordPress --}}
                            <div class="sys-section">
                                <h3 class="sys-section-title">
                                    <img src="https://s.w.org/style/images/about/WordPress-logotype-wmark.png" class="sys-wp-logo" alt="WP">
                                    Conexión WordPress (Sitio Web)
                                </h3>
                                <div class="sys-grid">
                                    <div class="sys-form-group">
                                        <label class="sys-label">URL Base de la API</label>
                                        <input type="text" wire:model="state.wp_api_url" placeholder="https://mi-sitio.com/wp-json/" class="sys-input">
                                        <p class="sys-help-text">Ejemplo: https://institucion.edu.do/wp-json/</p>
                                    </div>
                                    <div class="sys-form-group">
                                        <label class="sys-label">Token Secreto / Firma (X-SGA-Signature)</label>
                                        <input type="password" wire:model="state.wp_api_secret" class="sys-input">
                                    </div>
                                </div>
                            </div>

                            <hr class="sys-divider">

                            {{-- Moodle --}}
                            <div class="sys-section">
                                <h3 class="sys-section-title">
                                    <svg class="sys-section-icon" style="color: #f98012;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.09 14.08h-2.18V11.2h2.18v4.88zm0-6.1h-2.18V7.81h2.18v2.17z"/></svg>
                                    Conexión Aula Virtual (Moodle)
                                </h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group sys-col-full">
                                        <label class="sys-label">URL de Moodle</label>
                                        <input type="text" wire:model="state.moodle_url" placeholder="https://virtual.mi-sitio.com" class="sys-input">
                                    </div>
                                    <div class="sys-form-group sys-col-full">
                                        <label class="sys-label">Token de Web Services</label>
                                        <input type="password" wire:model="state.moodle_token" class="sys-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= TAB: FINANZAS ================= --}}
                        <div x-show="$wire.activeTab === 'finance'" class="sys-fade-in" style="display: none;">
                            <div class="sys-section">
                                <h3 class="sys-section-title">Pasarela de Pagos (Cardnet)</h3>
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
                                <h3 class="sys-section-title">Facturación Electrónica (DGII)</h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group">
                                        <label class="sys-label">RNC Emisor de la Institución</label>
                                        <input type="text" wire:model="state.ecf_rnc_emisor" class="sys-input">
                                        <p class="sys-help-text">Se utiliza para los links QR de los tickets generados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= TAB: GENERAL ================= --}}
                        <div x-show="$wire.activeTab === 'general'" class="sys-fade-in" style="display: none;">
                            <div class="sys-section">
                                <h3 class="sys-section-title">Información Institucional</h3>
                                <div class="sys-grid sys-grid-2">
                                    <div class="sys-form-group">
                                        <label class="sys-label">Nombre de la Institución</label>
                                        <input type="text" wire:model="state.school_name" class="sys-input">
                                    </div>
                                    <div class="sys-form-group">
                                        <label class="sys-label">Correo de Soporte/Contacto</label>
                                        <input type="email" wire:model="state.support_email" class="sys-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Footer del Formulario --}}
                    <div class="sys-footer">
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