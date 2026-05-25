<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Secciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de secciones por grado (Ej: 3ro A, 3ro B)</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
            Nueva Sección
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('message') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="flex gap-4 mb-4">
        <select wire:model.live="filterYear" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los años</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterLevel" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los grados</option>
            @foreach($gradeLevels as $level)
                <option value="{{ $level->id }}">{{ $level->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Grid de secciones --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($sections as $section)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $section->full_name ?: $section->gradeLevel?->short_name . ' ' . $section->name }}</h3>
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">
                        {{ $section->student_count }}/{{ $section->capacity }}
                    </span>
                </div>
                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <p>📅 {{ $section->academicYear?->name ?? 'Sin año' }}</p>
                    <p>🏫 {{ $section->gradeLevel?->name ?? 'Sin grado' }}</p>
                    <p>👩‍🏫 {{ $section->homeroomTeacher?->name ?? 'Sin maestro titular' }}</p>
                </div>
                <div class="mt-3 flex gap-2 border-t pt-3 dark:border-gray-700">
                    <button wire:click="edit({{ $section->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">Editar</button>
                    <button wire:click="delete({{ $section->id }})" wire:confirm="¿Seguro que deseas eliminar esta sección?" class="text-sm text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.584 2.376a.75.75 0 01.832 0l9 6a.75.75 0 01-.832 1.248L12 3.901 3.416 9.624a.75.75 0 01-.832-1.248l9-6z" /><path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 010 1.5H3a.75.75 0 010-1.5h.75v-9.918a.75.75 0 01.634-.74A49.109 49.109 0 0112 9c2.59 0 5.134.202 7.616.592a.75.75 0 01.634.74z" clip-rule="evenodd" /></svg>
                <p class="mt-2 text-sm font-medium">No hay secciones registradas</p>
                <p class="mt-1 text-xs">Crea la primera sección para empezar</p>
            </div>
        @endforelse
    </div>

    {{-- Modal: Crear/Editar Sección --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ $editingId ? 'Editar Sección' : 'Nueva Sección' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Año Escolar</label>
                        <select wire:model="academic_year_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Seleccionar...</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }} {{ $year->status === 'active' ? '(Activo)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grado</label>
                            <select wire:model="grade_level_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($gradeLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre (A, B, C...)</label>
                            <input type="text" wire:model="name" placeholder="A" maxlength="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maestro/a Titular</label>
                        <select wire:model="homeroom_teacher_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Sin asignar</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacidad</label>
                        <input type="number" wire:model="capacity" min="1" max="60" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
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
