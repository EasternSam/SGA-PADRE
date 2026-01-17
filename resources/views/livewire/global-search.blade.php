<div class="relative w-full max-w-md" x-data="{ focused: false }">
    <!-- Input de Búsqueda -->
    <div class="relative w-full text-gray-500 focus-within:text-sga-primary">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <!-- Icono Lupa (se oculta al cargar) -->
            <i class="fas fa-search text-gray-400 transition-opacity duration-200" 
               wire:loading.remove wire:target="search"></i>
            
            <!-- Icono Spinner (se muestra al cargar) -->
            <i class="fas fa-circle-notch fa-spin text-sga-primary transition-opacity duration-200" 
               wire:loading wire:target="search"></i>
        </div>

        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            @focus="focused = true"
            @blur="setTimeout(() => focused = false, 200)"
            @keydown.escape="$wire.set('search', ''); $wire.set('isOpen', false)"
            class="block w-full rounded-full border-0 bg-gray-100/50 py-1.5 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-sga-primary sm:text-sm sm:leading-6 transition-all shadow-sm focus:bg-white"
            placeholder="Buscar estudiantes, páginas... (Ctrl+K)"
        >

        <!-- Botón Limpiar -->
        @if(!empty($search))
            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="fas fa-times-circle"></i>
            </button>
        @endif
    </div>

    <!-- Dropdown de Resultados -->
    @if($isOpen && !empty($search))
        <div 
            x-show="focused || $wire.isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            class="absolute z-50 mt-2 w-full origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
        >
            <div class="py-1" role="none">
                @forelse($results as $result)
                    <a href="{{ $result['url'] }}" 
                       class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0"
                       wire:click="selectResult">
                        
                        <!-- Icono / Tipo -->
                        <div class="flex-shrink-0 mr-4 flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                            <i class="{{ $result['icon'] ?? 'fas fa-search' }} text-sm"></i>
                        </div>

                        <!-- Contenido -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $result['title'] }}
                            </p>
                            <div class="flex items-center gap-2">
                                <!-- Badge del Tipo -->
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    {{ $result['type'] }}
                                </span>
                                <!-- Subtítulo -->
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $result['subtitle'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Flecha -->
                        <div class="ml-auto pl-3">
                            <i class="fas fa-chevron-right text-gray-300 text-xs group-hover:text-gray-400"></i>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                        <p>No se encontraron resultados para "<span class="font-semibold">{{ $search }}</span>"</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Footer del Dropdown -->
            @if($results->count() > 0)
                <div class="bg-gray-50 px-4 py-2 text-xs text-gray-400 border-t border-gray-100 flex justify-between">
                    <span>{{ $results->count() }} resultados encontrados</span>
                    <span>Presiona <kbd class="font-sans border border-gray-300 rounded px-1 bg-white">Enter</kbd> para ir al primero</span>
                </div>
            @endif
        </div>
    @endif
</div>