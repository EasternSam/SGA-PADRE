<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🧠 Orientación y Psicología</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Seguimiento de casos, entrevistas y referimientos</p>
        </div>
        <button wire:click="create" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 transition">+ Nuevo Registro</button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">✅ {{ session('message') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 p-4 text-center">
            <div class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ $openCases }}</div>
            <p class="text-xs text-purple-600">📂 Casos Abiertos</p>
        </div>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-center">
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $urgentCases }}</div>
            <p class="text-xs text-red-600">🔴 Urgentes</p>
        </div>
        <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 text-center">
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $followupDue }}</div>
            <p class="text-xs text-yellow-600">📅 Seguimiento Pendiente</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Buscar..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
        <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos estados</option>
            @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
        <select wire:model.live="filterPriority" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas prioridades</option>
            @foreach($priorities as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos tipos</option>
            @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Caso</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Tipo</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Prioridad</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Estado</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Seguimiento</th>
                    <th class="px-3 py-2 text-right text-xs font-bold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($records as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $r->is_confidential ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">
                        <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $r->student?->full_name ?? '—' }}
                            @if($r->is_confidential) <span class="text-[9px] text-yellow-600">🔒</span> @endif
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $r->title }}</td>
                        <td class="px-3 py-2 text-center text-xs">{{ $types[$r->type] ?? $r->type }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ $r->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($r->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($r->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">
                                {{ $priorities[$r->priority] ?? $r->priority }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ $r->status === 'resolved' ? 'bg-green-100 text-green-800' : ($r->status === 'referred' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ $statuses[$r->status] ?? $r->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-xs">
                            @if($r->next_followup)
                                <span class="{{ $r->next_followup->isPast() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                    {{ $r->next_followup->format('d/m') }}
                                    @if($r->next_followup->isPast()) ⚠️ @endif
                                </span>
                            @else —
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <button wire:click="edit({{ $r->id }})" class="text-xs text-blue-600 hover:text-blue-800">✏️</button>
                            @if($r->status !== 'resolved')
                                <button wire:click="resolve({{ $r->id }})" class="text-xs text-green-600 hover:text-green-800">✅</button>
                            @endif
                            <button wire:click="delete({{ $r->id }})" wire:confirm="¿Eliminar?" class="text-xs text-red-400 hover:text-red-600">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Sin registros de orientación</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $records->links() }}</div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $editId ? 'Editar Registro' : 'Nuevo Registro de Orientación' }}</h3>
                <form wire:submit="save" class="space-y-3">
                    @if(!$editId)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        @if($studentResults->count() > 0)
                            <select wire:model="student_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($studentResults as $sr) <option value="{{ $sr->id }}">{{ $sr->full_name }}</option> @endforeach
                            </select>
                        @endif
                    </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select wire:model="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridad</label>
                            <select wire:model="priority" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($priorities as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título del Caso *</label>
                        <input type="text" wire:model="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Dificultad de concentración en clase" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hallazgos</label>
                        <textarea wire:model="findings" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recomendaciones</label>
                        <textarea wire:model="recommendations" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                            <select wire:model="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Próx. Seguimiento</label>
                            <input type="date" wire:model="next_followup" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="is_confidential" class="rounded border-gray-300 text-purple-600" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">🔒 Confidencial</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
