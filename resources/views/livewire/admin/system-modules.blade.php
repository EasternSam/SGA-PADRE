<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Centro de Módulos</h2>
        <p class="text-sm text-gray-500">Instala y gestiona las funcionalidades adicionales incluidas en tu plan.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($availableModules as $module)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition flex flex-col relative overflow-hidden">
                @if($module['is_installed'])
                    <div class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">INSTALADO</div>
                @endif

                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center text-3xl">
                        {{ $module['icon'] }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $module['title'] }}</h3>
                        <span class="text-xs font-mono text-gray-400 bg-gray-100 px-1 rounded">{{ $module['code'] }}</span>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-6 flex-1">{{ $module['description'] }}</p>

                <div class="pt-4 border-t border-gray-100">
                    @if($module['is_installed'])
                        <button wire:click="installModule('{{ $module['code'] }}')" wire:loading.attr="disabled" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <span wire:loading.remove wire:target="installModule('{{ $module['code'] }}')">Reinstalar / Reparar</span>
                            <span wire:loading wire:target="installModule('{{ $module['code'] }}')">Descargando...</span>
                        </button>
                    @else
                        <button wire:click="installModule('{{ $module['code'] }}')" wire:loading.attr="disabled" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                            <span wire:loading.remove wire:target="installModule('{{ $module['code'] }}')">Instalar Ahora</span>
                            <span wire:loading wire:target="installModule('{{ $module['code'] }}')" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Instalando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No hay módulos disponibles</h3>
                <p class="text-gray-500 max-w-sm mx-auto mt-2">Tu licencia actual no tiene addons adicionales asignados. Contacta a soporte para adquirir nuevas funcionalidades.</p>
            </div>
        @endforelse
    </div>
</div>