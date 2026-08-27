<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Asignaturas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Áreas curriculares según el diseño curricular MINERD</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
            Nueva Asignatura
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tabla o Estado Vacío --}}
    @if($subjects->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 text-center rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm mt-6">
            <div class="rounded-full bg-blue-50 dark:bg-blue-900/20 p-4 mb-4 text-blue-600 dark:text-blue-400">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No hay asignaturas registradas</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">Comienza registrando las asignaturas según el diseño curricular oficial del MINERD.</p>
            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
                Registrar Primera Asignatura
            </button>
        </div>
    @else
        {{-- Tabla agrupada por área --}}
        @php $groupedSubjects = $subjects->groupBy('area'); @endphp
        
        @foreach($areas as $areaKey => $areaLabel)
            @if(isset($groupedSubjects[$areaKey]) && $groupedSubjects[$areaKey]->count() > 0)
                <div class="mb-6">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        {{ $areaLabel }}
                    </h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Código</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Asignatura</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold uppercase text-gray-500">Horas/Sem</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold uppercase text-gray-500">Tipo</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($groupedSubjects[$areaKey] as $subject)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-2 text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $subject->code }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $subject->name }}</div>
                                            @if($subject->description)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5">{{ $subject->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center text-gray-600 dark:text-gray-300">{{ $subject->weekly_hours }}h</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $subject->is_core ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400' }}">
                                                {{ $subject->is_core ? 'Obligatoria' : 'Electiva' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm">
                                            <button wire:click="edit({{ $subject->id }})" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium mr-2">Editar</button>
                                            <button wire:click="delete({{ $subject->id }})" wire:confirm="¿Eliminar {{ $subject->name }}?" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ $editingId ? 'Editar Asignatura' : 'Nueva Asignatura' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                            <input type="text" wire:model="name" placeholder="Ej: Lengua Española" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" />
                            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Código</label>
                            <input type="text" wire:model="code" placeholder="LE" maxlength="10" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white uppercase focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" />
                            @error('code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Área Curricular</label>
                        <select wire:model="area" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('area') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach($areas as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('area') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horas Semanales</label>
                            <input type="number" wire:model="weekly_hours" min="1" max="10" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('weekly_hours') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" />
                            @error('weekly_hours') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" wire:model="is_core" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Obligatoria
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="3" placeholder="Breve descripción de la asignatura (opcional)..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                        @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            {{ $editingId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
