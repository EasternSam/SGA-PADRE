<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6">
    <!-- Banner Superior -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_120%,rgba(99,102,241,0.15),transparent)] pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider rounded-full border border-indigo-500/30">
                    Notificaciones
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-200">
                    Centro de Notificaciones
                </h1>
                <p class="text-slate-400 mt-2 text-sm">
                    Revisa las alertas de sistema, avisos automáticos e interacciones del centro.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2 shrink-0">
                <button wire:click="markAllAsRead" class="inline-flex items-center px-4 py-2 border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs rounded-xl shadow-sm transition cursor-pointer">
                    <i class="fas fa-check-double mr-2"></i> Marcar todo leído
                </button>
                <button wire:click="clearAll" wire:confirm="¿Seguro que deseas vaciar tu historial de notificaciones?" class="inline-flex items-center px-4 py-2 border border-red-500/20 bg-red-500/10 hover:bg-red-500/20 text-red-300 font-semibold text-xs rounded-xl shadow-sm transition cursor-pointer">
                    <i class="fas fa-trash-alt mr-2"></i> Vaciar historial
                </button>
            </div>
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

    <!-- Lista de Notificaciones -->
    <div class="workspace-panel p-0 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($notifications as $notification)
            @php
                $type = $notification->data['type'] ?? 'info';
                $bgColor = match($type) {
                    'success' => 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400',
                    'warning' => 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400',
                    'danger', 'error' => 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400',
                    default => 'bg-indigo-500/10 border-indigo-500/20 text-indigo-600 dark:text-indigo-400',
                };
                $icon = match($type) {
                    'success' => 'check-circle',
                    'warning' => 'exclamation-triangle',
                    'danger', 'error' => 'times-circle',
                    default => 'info-circle',
                };
                $iconName = $notification->data['icon'] ?? $icon;
            @endphp
            
            <div class="p-5 flex gap-4 transition duration-200 {{ $notification->read_at ? 'bg-white' : 'bg-slate-50/50 border-l-4 border-indigo-600' }}">
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
                <div class="inline-flex p-4 rounded-full bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-700 border border-slate-200 dark:border-slate-800">
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
