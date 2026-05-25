<div class="p-4 lg:p-6">
    {{-- Page Header --}}
    <div class="gb-page-header">
        <div>
            <h1 class="gb-page-title">Log de Auditoría</h1>
            <p class="gb-page-subtitle">Historial de acciones del sistema — cambios, accesos y operaciones</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="gb-table-outer">
        <div class="gb-table-toolbar">
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar descripción..." class="gb-input pl-9 w-52" />
                </div>
                <select wire:model.live="filterAction" class="gb-input w-auto">
                    <option value="">Todas acciones</option>
                    @foreach($actions as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
                <select wire:model.live="filterUser" class="gb-input w-auto">
                    <option value="">Todos usuarios</option>
                    @foreach($users as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                </select>
            </div>
            <div class="text-xs text-gray-400 font-medium">{{ $logs->total() }} registros</div>
        </div>

        <div class="overflow-x-auto">
            <table class="gb-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th class="text-center">Acción</th>
                        <th>Descripción</th>
                        <th>Modelo</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-400 flex-shrink-0">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="user-name text-sm">{{ $log->user?->name ?? 'Sistema' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $actionClass = match(true) {
                                        in_array($log->action, ['created', 'approved']) => 'gb-badge-success',
                                        in_array($log->action, ['deleted', 'rejected']) => 'gb-badge-danger',
                                        in_array($log->action, ['login', 'logout']) => 'gb-badge-info',
                                        default => 'gb-badge-warning',
                                    };
                                @endphp
                                <span class="gb-badge {{ $actionClass }}">{{ $actions[$log->action] ?? $log->action }}</span>
                            </td>
                            <td class="max-w-xs truncate text-sm text-gray-700 dark:text-gray-300">{{ $log->description ?? '—' }}</td>
                            <td>
                                @if($log->model_type)
                                    <span class="gb-link-id text-xs">{{ class_basename($log->model_type) }}{{ $log->model_id ? ' #'.$log->model_id : '' }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="gb-empty-state">
                                    <div class="gb-empty-icon">📋</div>
                                    <div class="gb-empty-title">Sin registros de auditoría</div>
                                    <div class="gb-empty-desc">Las acciones del sistema aparecerán aquí</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700/50">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
