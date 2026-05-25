<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📢 Comunicaciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Envío de circulares y mensajes masivos</p>
        </div>
        <button wire:click="openSend" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">+ Nuevo Comunicado</button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">✅ {{ session('message') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 text-center">
            <div class="text-2xl font-bold text-indigo-700 dark:text-indigo-400">{{ $totalSent }}</div>
            <p class="text-xs text-indigo-600">📨 Total Enviados</p>
        </div>
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-center">
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $thisMonth }}</div>
            <p class="text-xs text-blue-600">📅 Este Mes</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex gap-3 mb-4">
        <select wire:model.live="filterChannel" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos canales</option>
            @foreach($channels as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
    </div>

    {{-- History --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Fecha</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Canal</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Asunto</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Tipo</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Destinos</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Por</th>
                    <th class="px-3 py-2 text-right text-xs font-bold uppercase text-gray-500">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-3 py-2 text-xs text-gray-500">{{ $log->sent_at?->format('d/m H:i') ?? $log->created_at->format('d/m H:i') }}</td>
                        <td class="px-3 py-2 text-center text-xs">{{ $channels[$log->channel] ?? $log->channel }}</td>
                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-white max-w-xs truncate">{{ $log->subject }}</td>
                        <td class="px-3 py-2 text-center text-xs">{{ $types[$log->type] ?? $log->type }}</td>
                        <td class="px-3 py-2 text-center text-sm font-bold text-indigo-600">{{ $log->recipients_count }}</td>
                        <td class="px-3 py-2 text-xs text-gray-500">{{ $log->sender?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-right">
                            <button wire:click="delete({{ $log->id }})" wire:confirm="¿Eliminar?" class="text-xs text-red-400 hover:text-red-600">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Sin comunicados enviados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>

    {{-- Send Modal --}}
    @if($showSendModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showSendModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📢 Nuevo Comunicado</h3>
                <form wire:submit="send" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Canal</label>
                            <select wire:model="channel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($channels as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destinatarios</label>
                            <select wire:model.live="sendType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    @if($sendType === 'section')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                            <select wire:model="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($sections as $s) <option value="{{ $s->id }}">{{ $s->gradeLevel?->short_name }} {{ $s->name }}</option> @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asunto *</label>
                        <input type="text" wire:model="subject" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Reunión de padres" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mensaje *</label>
                        <textarea wire:model="body" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Escriba el contenido del comunicado..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showSendModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">📤 Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
