<div x-data="{ open: false }" class="relative">
    <!-- Botón de la Campana -->
    <button @click="open = !open" 
            @click.away="open = false"
            class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500 transition-colors focus:outline-none">
        <span class="sr-only">Ver notificaciones</span>
        <!-- Icono Campana (FontAwesome o SVG) -->
        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        
        <!-- Badge Contador -->
        @if($unreadCount > 0)
            <span class="absolute top-2 right-2 flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
            </span>
        @endif
    </button>

    <!-- Menú Desplegable -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none max-h-[90vh] flex flex-col"
         style="display: none;">
        
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-md">
            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                    Marcar todo leído
                </button>
            @endif
        </div>

        <div class="overflow-y-auto flex-1 p-1">
            @forelse($this->notifications as $notification)
                <div wire:click="markAsRead('{{ $notification->id }}')" 
                     class="group flex gap-4 p-3 hover:bg-gray-50 cursor-pointer rounded-md transition-colors mb-1 {{ $notification->read_at ? 'opacity-70' : 'bg-blue-50/50 border-l-2 border-blue-500' }}">
                    
                    <!-- Icono Dinámico -->
                    <div class="flex-shrink-0 mt-1">
                        @php
                            $type = $notification->data['type'] ?? 'info';
                            // Colores según tipo
                            $bgColor = match($type) {
                                'success' => 'bg-green-100 text-green-600',
                                'warning' => 'bg-amber-100 text-amber-600',
                                'danger', 'error' => 'bg-red-100 text-red-600',
                                default => 'bg-blue-100 text-blue-600',
                            };
                            $iconName = $notification->data['icon'] ?? 'info-circle';
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $bgColor }} flex items-center justify-center">
                            {{-- Mapeo simple de iconos FontAwesome a SVG si no usas FA --}}
                            <i class="fas fa-{{ $iconName }} text-sm"></i>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $notification->data['title'] ?? 'Notificación' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-1.5">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    
                    <!-- Indicador No Leído -->
                    @if(is_null($notification->read_at))
                        <div class="flex-shrink-0 self-center">
                            <span class="block h-2 w-2 rounded-full bg-blue-600"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-gray-500">
                    <p class="text-sm">No tienes notificaciones nuevas.</p>
                </div>
            @endforelse
        </div>
        
        <div class="p-2 border-t border-gray-100 bg-gray-50 rounded-b-md text-center">
            <a href="#" class="text-xs text-gray-500 hover:text-gray-700">Ver historial completo</a>
        </div>
    </div>
</div>