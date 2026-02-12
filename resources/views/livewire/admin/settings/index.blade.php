<div class="min-h-screen bg-gray-50/50 pb-12">
    
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Configuración del Sistema</h1>
                <p class="mt-1 text-sm text-gray-500">Administra las integraciones, APIs y parámetros globales del sistema.</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8 flex flex-col md:flex-row gap-6">
        
        {{-- Sidebar Pestañas --}}
        <div class="w-full md:w-64 shrink-0">
            <nav class="flex flex-col space-y-1">
                <button wire:click="$set('activeTab', 'apis')" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ $activeTab === 'apis' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="mr-3 h-5 w-5 {{ $activeTab === 'apis' ? 'text-indigo-700' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                    Integraciones API
                </button>
                <button wire:click="$set('activeTab', 'finance')" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ $activeTab === 'finance' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="mr-3 h-5 w-5 {{ $activeTab === 'finance' ? 'text-indigo-700' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    Pasarelas y Finanzas
                </button>
                <button wire:click="$set('activeTab', 'general')" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors {{ $activeTab === 'general' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="mr-3 h-5 w-5 {{ $activeTab === 'general' ? 'text-indigo-700' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                    Datos Generales
                </button>
            </nav>
        </div>

        {{-- Área de Contenido --}}
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <form wire:submit.prevent="save">
                    
                    {{-- Alertas --}}
                    @if (session()->has('message'))
                        <div class="p-4 bg-emerald-50 border-b border-emerald-100 text-emerald-800 text-sm font-medium flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="p-6 sm:p-8 space-y-8">
                        
                        {{-- ================= TAB: APIS ================= --}}
                        <div x-show="$wire.activeTab === 'apis'" class="space-y-8 animate-fade-in">
                            {{-- WordPress --}}
                            <div>
                                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <img src="https://s.w.org/style/images/about/WordPress-logotype-wmark.png" class="h-6 w-auto grayscale opacity-70" alt="WP">
                                    Conexión WordPress (Sitio Web)
                                </h3>
                                <div class="grid grid-cols-1 gap-5 bg-gray-50/50 p-5 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">URL Base de la API</label>
                                        <input type="text" wire:model="state.wp_api_url" placeholder="https://mi-sitio.com/wp-json/" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Ejemplo: https://institucion.edu.do/wp-json/</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Token Secreto / Firma (X-SGA-Signature)</label>
                                        <input type="password" wire:model="state.wp_api_secret" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            {{-- Moodle --}}
                            <div>
                                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="h-6 w-6 text-[#f98012]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.09 14.08h-2.18V11.2h2.18v4.88zm0-6.1h-2.18V7.81h2.18v2.17z"/></svg>
                                    Conexión Aula Virtual (Moodle)
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50/50 p-5 rounded-xl border border-gray-100">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">URL de Moodle</label>
                                        <input type="text" wire:model="state.moodle_url" placeholder="https://virtual.mi-sitio.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Token de Web Services</label>
                                        <input type="password" wire:model="state.moodle_token" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= TAB: FINANZAS ================= --}}
                        <div x-show="$wire.activeTab === 'finance'" style="display: none;" class="space-y-8 animate-fade-in">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 mb-4">Pasarela de Pagos (Cardnet)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50/50 p-5 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Merchant ID</label>
                                        <input type="text" wire:model="state.cardnet_merchant_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Terminal ID</label>
                                        <input type="text" wire:model="state.cardnet_terminal_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-bold text-gray-900 mb-4">Facturación Electrónica (DGII)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50/50 p-5 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">RNC Emisor de la Institución</label>
                                        <input type="text" wire:model="state.ecf_rnc_emisor" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Se utiliza para los links QR de los tickets generados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= TAB: GENERAL ================= --}}
                        <div x-show="$wire.activeTab === 'general'" style="display: none;" class="space-y-8 animate-fade-in">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 mb-4">Información Institucional</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50/50 p-5 rounded-xl border border-gray-100">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la Institución</label>
                                        <input type="text" wire:model="state.school_name" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Correo de Soporte/Contacto</label>
                                        <input type="email" wire:model="state.support_email" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Footer del Formulario --}}
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50">
                            <span wire:loading.remove>Guardar Configuraciones</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>