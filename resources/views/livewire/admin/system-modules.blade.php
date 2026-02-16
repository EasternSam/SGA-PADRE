<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Módulos del Sistema</h2>
        <p class="text-sm text-gray-500">Instala o actualiza las funcionalidades incluidas en tu plan.</p>
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
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-2xl">
                        {{ $module['icon'] }}
                    </div>
                    @if($module['is_installed'])
                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Instalado
                        </span>
                    @else
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
                            Disponible
                        </span>
                    @endif
                </div>

                <h3 class="text-lg font-bold text-gray-900">{{ $module['title'] }}</h3>
                <p class="text-sm text-gray-500 mt-1 flex-1">{{ $module['description'] }}</p>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    @if($module['is_installed'])
                        <button wire:click="installModule('{{ $module['code'] }}')" wire:loading.attr="disabled" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <span wire:loading.remove wire:target="installModule('{{ $module['code'] }}')">Reinstalar / Actualizar</span>
                            <span wire:loading wire:target="installModule('{{ $module['code'] }}')">Descargando...</span>
                        </button>
                    @else
                        <button wire:click="installModule('{{ $module['code'] }}')" wire:loading.attr="disabled" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                            <span wire:loading.remove wire:target="installModule('{{ $module['code'] }}')">Instalar Módulo</span>
                            <span wire:loading wire:target="installModule('{{ $module['code'] }}')" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Instalando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <p class="text-gray-500">No tienes módulos adicionales disponibles en tu licencia actual.</p>
                <p class="text-sm text-indigo-600 mt-2 cursor-pointer">Contactar a soporte para adquirir más.</p>
            </div>
        @endforelse
    </div>
</div>