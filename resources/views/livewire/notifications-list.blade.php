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
                <button wire:click="markAllAsRead" class="inline-flex items-center px-4 py-2 border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs rounded-xl shadow-sm transition">
                    <i class="fas fa-check-double mr-2"></i> Marcar todo leído
                </button>
                <button wire:click="clearAll" wire:confirm="¿Seguro que deseas vaciar tu historial de notificaciones?" class="inline-flex items-center px-4 py-2 border border-red-500/20 bg-red-500/10 hover:bg-red-500/20 text-red-300 font-semibold text-xs rounded-xl shadow-sm transition">
                    <i class="fas fa-trash-alt mr-2"></i> Vaciar historial
                </button>
            </div>
        </div>
    </div>

    <!-- Mensajes de Sesión -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filtros -->
    <div class="flex items-center gap-2 border-b border-slate-100 pb-1">
        <button wire:click="$set('filter', 'all')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $filter === 'all' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            Todas
        </button>
        <button wire:click="$set('filter', 'unread')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $filter === 'unread' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            No leídas
        </button>
        <button wire:click="$set('filter', 'read')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $filter === 'read' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            Leídas
        </button>
    </div>

    <!-- Lista de Notificaciones -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse ($notifications as $notification)
            @php
                $type = $notification->data['type'] ?? 'info';
                $bgColor = match($type) {
                    'success' => 'bg-green-50 border-green-200 text-green-700 dark:bg-green-950/20 dark:border-green-800/30',
                    'warning' => 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-950/20 dark:border-amber-800/30',
                    'danger', 'error' => 'bg-red-50 border-red-200 text-red-700 dark:bg-red-950/20 dark:border-red-800/30',
                    default => 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-950/20 dark:border-indigo-800/30',
                };
                $icon = match($type) {
                    'success' => 'check-circle',
                    'warning' => 'exclamation-triangle',
                    'danger', 'error' => 'times-circle',
                    default => 'info-circle',
                };
                $iconName = $notification->data['icon'] ?? $icon;
            @endphp
            
            <div class="p-5 flex gap-4 transition duration-200 {{ $notification->read_at ? 'opacity-70 bg-white' : 'bg-indigo-50/10 border-l-4 border-indigo-500' }}">
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
                            <h3 class="text-sm font-bold text-slate-900">
                                {{ $notification->data['title'] ?? 'Notificación' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $notification->created_at->format('d/m/Y h:i A') }} · {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        
                        <!-- Acciones -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if(is_null($notification->read_at))
                                <button wire:click="markAsRead('{{ $notification->id }}')" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition" title="Marcar como leída">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            @else
                                <button wire:click="markAsUnread('{{ $notification->id }}')" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition" title="Marcar como no leída">
                                    <i class="fas fa-envelope-open text-xs"></i>
                                </button>
                            @endif
                            <button wire:click="deleteNotification('{{ $notification->id }}')" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-50 transition" title="Eliminar">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-2 text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                        {{ $notification->data['message'] ?? '' }}
                    </div>

                    <!-- Enlace / Acción -->
                    @if (isset($notification->data['url']) && $notification->data['url'])
                        <div class="mt-3">
                            <button wire:click="readAndRedirect('{{ $notification->id }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-lg transition">
                                Ver Detalles <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-slate-400 space-y-3">
                <div class="inline-flex p-4 rounded-full bg-slate-50 text-slate-300">
                    <i class="fas fa-bell-slash text-2xl"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">No hay notificaciones</h3>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
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
