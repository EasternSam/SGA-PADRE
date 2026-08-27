<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Centro de Alertas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Monitoreo de asistencia, rendimiento y riesgo de abandono</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="markAllRead" class="rounded-lg bg-gray-200 px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 transition">Marcar leídas</button>
            <button wire:click="scan" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Escanear Ahora</button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 4000)">{{ session('message') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-center">
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['critical'] }}</div>
            <p class="text-xs text-red-600">Críticas</p>
        </div>
        <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 text-center">
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $stats['warning'] }}</div>
            <p class="text-xs text-yellow-600">Advertencias</p>
        </div>
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-center">
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['unread'] }}</div>
            <p class="text-xs text-blue-600">Sin Leer</p>
        </div>
        <div class="rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $stats['total'] }}</div>
            <p class="text-xs text-gray-500">Total Activas</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los tipos</option>
            @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
        <select wire:model.live="filterSeverity" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Toda severidad</option>
            <option value="critical">Crítica</option>
            <option value="warning">Advertencia</option>
            <option value="info">Informativa</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" wire:model.live="showResolved" class="rounded border-gray-300 text-blue-600">
            Mostrar resueltas
        </label>
    </div>

    {{-- Alert List --}}
    <div class="space-y-3">
        @forelse($alerts as $alert)
            <div class="rounded-xl border {{ !$alert->is_read ? 'border-l-4' : '' }} p-4 shadow-sm transition
                {{ $alert->severity === 'critical' ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 
                   ($alert->severity === 'warning' ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/10' : 
                   'border-blue-300 bg-blue-50 dark:bg-blue-900/10') }}
                {{ $alert->is_resolved ? 'opacity-60' : '' }}">
                <div class="flex items-start gap-4">
                    <div class="text-2xl">
                        {{ \App\Models\SchoolAlert::TYPES[$alert->type] ? explode(' ', \App\Models\SchoolAlert::TYPES[$alert->type])[0] : '' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $alert->title }}</h4>
                            @if($alert->is_resolved)
                                <span class="rounded-full bg-green-100 text-green-800 px-2 py-0.5 text-[10px] font-bold">RESUELTA</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $alert->description }}</p>
                        <div class="flex gap-3 mt-1 text-[10px] text-gray-500">
                            <span>{{ $alert->student?->full_name ?? '—' }}</span>
                            <span>{{ $alert->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    @if(!$alert->is_resolved)
                        <div class="flex gap-1">
                            @if(!$alert->is_read)
                                <button wire:click="markRead({{ $alert->id }})" class="rounded px-2 py-1 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200"></button>
                            @endif
                            <button wire:click="openResolve({{ $alert->id }})" class="rounded px-2 py-1 text-xs bg-green-100 text-green-700 hover:bg-green-200">Resolver</button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-lg text-gray-400 mb-1">Sin alertas activas</p>
                <p class="text-sm text-gray-400">Presiona "Escanear Ahora" para revisar todos los estudiantes</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $alerts->links() }}</div>

    {{-- Resolve Modal --}}
    @if($showResolveModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showResolveModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Resolver Alerta</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nota de resolución (opcional)</label>
                        <textarea wire:model="resolveNote" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Se contactó al padre, justificó las ausencias..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button wire:click="$set('showResolveModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button wire:click="resolve" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Resolver</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
