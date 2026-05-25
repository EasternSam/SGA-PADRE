<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Conducta y Disciplina</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Registro de incidencias disciplinarias según Reglamento Estudiantil</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
            Registrar Incidencia
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('message') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 text-center border border-gray-200 dark:border-gray-700">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
            <p class="text-xs text-gray-500 mt-1">Total Año</p>
        </div>
        <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4 text-center border border-yellow-200 dark:border-yellow-800">
            <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $stats['leve'] }}</span>
            <p class="text-xs text-yellow-600 mt-1">Leves</p>
        </div>
        <div class="rounded-xl bg-orange-50 dark:bg-orange-900/20 p-4 text-center border border-orange-200 dark:border-orange-800">
            <span class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ $stats['grave'] }}</span>
            <p class="text-xs text-orange-600 mt-1">Graves</p>
        </div>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 text-center border border-red-200 dark:border-red-800">
            <span class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['muy_grave'] }}</span>
            <p class="text-xs text-red-600 mt-1">Muy Graves</p>
        </div>
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center border border-blue-200 dark:border-blue-800">
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['this_month'] }}</span>
            <p class="text-xs text-blue-600 mt-1">Este Mes</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <select wire:model.live="filterSeverity" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas las severidades</option>
            @foreach($severities as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterCategory" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas las categorías</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterSection" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas las secciones</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}">{{ $section->gradeLevel?->short_name }} {{ $section->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estudiante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Categoría</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Severidad</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Descripción</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Padre</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $record->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $record->student?->full_name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $categories[$record->category] ?? $record->category }}</td>
                        <td class="px-4 py-2 text-center">
                            @switch($record->severity)
                                @case('leve')
                                    <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400">🟡 Leve</span>
                                    @break
                                @case('grave')
                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/40 dark:text-orange-400">🟠 Grave</span>
                                    @break
                                @case('muy_grave')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-400">🔴 Muy Grave</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">{{ $record->description }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($record->parent_notified)
                                <span class="text-green-500" title="Padre notificado">✅</span>
                            @else
                                <span class="text-gray-300" title="Sin notificar">⬜</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-sm">
                            <button wire:click="edit({{ $record->id }})" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium mr-2">Editar</button>
                            <button wire:click="delete({{ $record->id }})" wire:confirm="¿Eliminar este registro?" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <p class="text-sm font-medium">No hay incidencias registradas</p>
                            <p class="text-xs mt-1">¡Buen comportamiento! 🎉</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
            {{ $records->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ $editingId ? 'Editar Incidencia' : 'Registrar Incidencia Disciplinaria' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    {{-- Búsqueda de estudiante --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar por nombre..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        @if($searchStudents->count() > 0 && !$student_id)
                            <div class="mt-1 rounded-lg border border-gray-200 bg-white shadow-lg max-h-40 overflow-y-auto dark:bg-gray-700 dark:border-gray-600">
                                @foreach($searchStudents as $st)
                                    <button type="button" wire:click="$set('student_id', {{ $st->id }}); $set('studentSearch', '{{ addslashes($st->full_name) }}')" class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                        {{ $st->full_name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        @error('student_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                            <input type="date" wire:model="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Severidad *</label>
                            <select wire:model="severity" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($severities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría *</label>
                            <select wire:model="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción de la Incidencia *</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Describa detalladamente lo ocurrido..."></textarea>
                        @error('description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sanción / Acción Tomada</label>
                        <textarea wire:model="action_taken" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Llamada al padre, suspensión 1 día, etc."></textarea>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="parent_notified" class="rounded border-gray-300 text-blue-600">
                            Padre/Tutor notificado
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seguimiento</label>
                        <textarea wire:model="follow_up" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Notas de seguimiento..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                            {{ $editingId ? 'Actualizar' : 'Registrar Incidencia' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
