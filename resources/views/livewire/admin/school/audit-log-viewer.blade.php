<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">🔍 Log de Auditoría</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Historial de acciones del sistema — cambios, accesos y operaciones</p>

    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Buscar descripción..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
        <select wire:model.live="filterAction" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas acciones</option>
            @foreach($actions as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
        <select wire:model.live="filterUser" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos usuarios</option>
            @foreach($users as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
        </select>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Fecha</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Usuario</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Acción</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Descripción</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Modelo</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-3 py-2 text-xs text-gray-500">
                            {{ $log->created_at->format('d/m H:i') }}
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                            {{ $log->user?->name ?? 'Sistema' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ in_array($log->action, ['created', 'approved']) ? 'bg-green-100 text-green-800' :
                                   (in_array($log->action, ['deleted', 'rejected']) ? 'bg-red-100 text-red-800' :
                                   (in_array($log->action, ['login', 'logout']) ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ $actions[$log->action] ?? $log->action }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">
                            {{ $log->description ?? '—' }}
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-400">
                            {{ $log->model_type ? class_basename($log->model_type) : '' }}
                            {{ $log->model_id ? '#' . $log->model_id : '' }}
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-400 font-mono">
                            {{ $log->ip_address ?? '' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Sin registros de auditoría</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
