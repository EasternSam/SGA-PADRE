<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6">
    <!-- Banner Superior (workspace-panel) -->
    <div class="workspace-panel flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
            <span class="px-3 py-1 bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs font-semibold uppercase tracking-wider rounded-full border border-indigo-500/20">
                Notificaciones
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-slate-800 dark:text-slate-100">
                Centro de Notificaciones
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">
                Revisa las alertas de sistema, avisos automáticos e interacciones del centro.
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2 shrink-0">
            <button wire:click="markAllAsRead" class="btn btn-secondary text-xs">
                <i class="fas fa-check-double mr-2"></i> Marcar todo leído
            </button>
            <button wire:click="clearAll" wire:confirm="¿Seguro que deseas vaciar tu historial de notificaciones?" class="btn btn-secondary text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs">
                <i class="fas fa-trash-alt mr-2"></i> Vaciar historial
            </button>
        </div>
    </div>

    <!-- Mensajes de Sesión -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filtros: Segmented Control -->
    <div class="flex">
        <div class="segmented-control">
            <div wire:click="$set('filter', 'all')" class="segment-item {{ $filter === 'all' ? 'active' : '' }}">
                Todas
            </div>
            <div wire:click="$set('filter', 'unread')" class="segment-item {{ $filter === 'unread' ? 'active' : '' }}">
                No leídas
            </div>
            <div wire:click="$set('filter', 'read')" class="segment-item {{ $filter === 'read' ? 'active' : '' }}">
                Leídas
            </div>
        </div>
    </div>

    <!-- Lista de Notificaciones (workspace-panel p-0) -->
    <div class="workspace-panel p-0 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($notifications as $notification)
            @php
                $type = $notification->data['type'] ?? 'info';
                $bgColor = match($type) {
                    'success' => 'bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-800/30 text-green-700 dark:text-green-400',
                    'warning' => 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/30 text-amber-700 dark:text-amber-400',
                    'danger', 'error' => 'bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800/30 text-red-700 dark:text-red-400',
                    default => 'bg-indigo-50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800/30 text-indigo-700 dark:text-indigo-400',
                };
                $icon = match($type) {
                    'success' => 'check-circle',
                    'warning' => 'exclamation-triangle',
                    'danger', 'error' => 'times-circle',
                    default => 'info-circle',
                };
                $iconName = $notification->data['icon'] ?? $icon;
            @endphp
            
            <div class="p-5 flex gap-4 transition duration-200 {{ $notification->read_at ? 'bg-white dark:bg-[#161D30]/30' : 'bg-indigo-50/5 dark:bg-indigo-950/5 border-l-4 border-indigo-500 dark:border-indigo-600' }}">
                <!-- Icono -->
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full border flex items-center justify-center {{ $bgColor }} shadow-sm">
                        <i class="fas fa-{{ $iconName }} text-base"></i>
                    </div>
                </div>

                <!-- Contenido -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $notification->data['title'] ?? 'Notificación' }}
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ $notification->created_at->format('d/m/Y h:i A') }} · {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        
                        <!-- Acciones -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if(is_null($notification->read_at))
                                <button wire:click="markAsRead('{{ $notification->id }}')" class="btn-icon p-1.5 text-xs" title="Marcar como leída">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            @else
                                <button wire:click="markAsUnread('{{ $notification->id }}')" class="btn-icon p-1.5 text-xs" title="Marcar como no leída">
                                    <i class="fas fa-envelope-open text-xs"></i>
                                </button>
                            @endif
                            <button wire:click="deleteNotification('{{ $notification->id }}')" class="btn-icon p-1.5 text-xs hover:text-red-600 dark:hover:text-red-400" title="Eliminar">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                        {{ $notification->data['message'] ?? '' }}
                    </div>

                    <!-- Enlace / Acción -->
                    @if (isset($notification->data['url']) && $notification->data['url'])
                        <div class="mt-3">
                            <button wire:click="readAndRedirect('{{ $notification->id }}')" class="btn btn-secondary text-xs py-1.5 px-3">
                                Ver Detalles <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-slate-400 dark:text-slate-600 space-y-3">
                <div class="inline-flex p-4 rounded-full bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-700">
                    <i class="fas fa-bell-slash text-2xl"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">No hay notificaciones</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 max-w-xs mx-auto">
                    {{ $filter === 'all' ? 'Tu historial de notificaciones está vacío.' : 'No se encontraron notificaciones en esta categoría.' }}
                </p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if ($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
