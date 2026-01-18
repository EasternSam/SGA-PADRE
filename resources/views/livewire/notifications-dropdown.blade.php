<div x-data="{ open: false }" class="relative">
    <!-- Botón de la Campana -->
    <button @click="open = !open" 
            @click.away="open = false"
            class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500 transition-colors focus:outline-none">
        <span class="sr-only">Ver notificaciones</span>
        <i class="far fa-bell text-xl"></i>
        
        <!-- Badge Contador -->
        @if($unreadCount > 0)
            <span class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white animate-pulse"></span>
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
         class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
         style="display: none;">
        
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                    Marcar todo leído
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($this->notifications as $notification)
                <div wire:click="markAsRead('{{ $notification->id }}')" 
                     class="group flex gap-4 p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-50 transition-colors {{ $notification->read_at ? 'opacity-70' : 'bg-indigo-50/30' }}">
                    
                    <!-- Icono -->
                    <div class="flex-shrink-0 mt-1">
                        @php
                            $type = $notification->data['type'] ?? 'info';
                            $colorClass = match($type) {
                                'success' => 'text-green-500 bg-green-50',
                                'warning' => 'text-amber-500 bg-amber-50',
                                'danger' => 'text-red-500 bg-red-50',
                                default => 'text-blue-500 bg-blue-50',
                            };
                        @endphp
                        <div class="h-8 w-8 rounded-full {{ $colorClass }} flex items-center justify-center">
                            <i class="fas fa-{{ $notification->data['icon'] ?? 'bell' }}"></i>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $notification->data['title'] ?? 'Notificación' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    
                    <!-- Indicador No Leído -->
                    @if(is_null($notification->read_at))
                        <div class="flex-shrink-0 self-center">
                            <span class="block h-2 w-2 rounded-full bg-indigo-600"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    <i class="far fa-bell-slash text-2xl mb-2 text-gray-300"></i>
                    <p class="text-sm">No tienes notificaciones nuevas.</p>
                </div>
            @endforelse
        </div>
        
        @if($this->notifications->count() > 0)
            <div class="p-2 border-t border-gray-100 bg-gray-50 rounded-b-md text-center">
                <span class="text-xs text-gray-400">Mostrando últimas 10</span>
            </div>
        @endif
    </div>
</div>