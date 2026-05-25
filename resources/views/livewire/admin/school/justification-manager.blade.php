<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Justificaciones de Ausencia</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Registro y aprobación de excusas médicas y familiares</p>
        </div>
        <button wire:click="create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">+ Nueva Justificación</button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">{{ session('message') }}</div>
    @endif

    <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white mb-4">
        <option value="">Todos los estados</option>
        @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
    </select>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Fechas</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Motivo</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Doc</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($justifications as $j)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $j->student?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $j->date_from->format('d/m') }} — {{ $j->date_to->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $reasons[$j->reason] ?? $j->reason }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold
                                {{ $j->status === 'approved' ? 'bg-green-100 text-green-800' : ($j->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $statuses[$j->status] ?? $j->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($j->document_path)
                                <a href="{{ asset('storage/' . $j->document_path) }}" target="_blank" class="text-blue-600 text-sm"></a>
                            @else
                                <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($j->status === 'pending')
                                <button wire:click="approve({{ $j->id }})" class="text-xs text-green-600 hover:text-green-800 font-medium mr-1">Aprobar</button>
                                <button wire:click="reject({{ $j->id }})" class="text-xs text-red-500 hover:text-red-700 font-medium mr-1">Rechazar</button>
                            @endif
                            <button wire:click="delete({{ $j->id }})" wire:confirm="¿Eliminar?" class="text-xs text-gray-400 hover:text-red-500"></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Sin justificaciones registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $justifications->links() }}</div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Nueva Justificación</h3>
                <form wire:submit="save" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        @if($studentResults->count() > 0)
                            <select wire:model="student_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($studentResults as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->full_name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desde *</label>
                            <input type="date" wire:model="date_from" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta *</label>
                            <input type="date" wire:model="date_to" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo</label>
                        <select wire:model="reason" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach($reasons as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Documento (opcional)</label>
                        <input type="file" wire:model="document" class="w-full text-sm" />
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
