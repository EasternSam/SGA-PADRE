<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Horario Escolar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Asignación visual de asignaturas y docentes por sección</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            ✅ {{ session('message') }}
        </div>
    @endif

    @if(!$activeYear)
        <div class="rounded-xl border-2 border-dashed border-yellow-300 bg-yellow-50 p-8 text-center dark:bg-yellow-900/20">
            <p class="font-semibold text-yellow-800 dark:text-yellow-400">No hay año escolar activo</p>
        </div>
    @else
        {{-- Controles --}}
        <div class="flex flex-wrap gap-4 mb-6 items-end">
            <div class="flex-1 min-w-[250px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar sección...</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->gradeLevel?->short_name }} {{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(!$hasBlocks)
                <button wire:click="generateDefaultBlocks" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    ⚡ Generar Bloques Horarios
                </button>
            @endif
        </div>

        {{-- Grid --}}
        @if($section_id && count($scheduleGrid) > 0)
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase text-white w-32">Hora</th>
                            @foreach($dayLabels as $key => $label)
                                <th class="px-3 py-3 text-center text-xs font-bold uppercase text-white">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($scheduleGrid as $row)
                            <tr class="{{ $row['type'] !== 'class' ? 'bg-gray-100 dark:bg-gray-900/50' : '' }}">
                                {{-- Bloque horario --}}
                                <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $row['block_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['time_range'] }}</div>
                                    @if($row['type'] !== 'class')
                                        <span class="inline-flex mt-1 rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400">
                                            {{ \App\Models\TimeBlock::TYPES[$row['type']] ?? $row['type'] }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Celdas por día --}}
                                @foreach($days as $day)
                                    <td class="px-1 py-1 text-center border-r border-gray-100 dark:border-gray-700/50">
                                        @if($row['type'] === 'class')
                                            @php $cell = $row['cells'][$day] ?? null; @endphp
                                            <button wire:click="openCell({{ $row['block_id'] }}, '{{ $day }}')"
                                                class="w-full rounded-lg p-2 text-left transition hover:shadow-md min-h-[60px]
                                                {{ $cell && $cell['subject_name'] 
                                                    ? 'bg-blue-50 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/30 dark:border-blue-800' 
                                                    : 'bg-gray-50 border border-dashed border-gray-300 hover:bg-gray-100 hover:border-blue-400 dark:bg-gray-800 dark:border-gray-600' }}">
                                                @if($cell && $cell['subject_name'])
                                                    <div class="text-xs font-bold text-blue-800 dark:text-blue-300 truncate">{{ $cell['subject_name'] }}</div>
                                                    @if($cell['teacher_name'])
                                                        <div class="text-[10px] text-gray-600 dark:text-gray-400 truncate mt-0.5">👩‍🏫 {{ $cell['teacher_name'] }}</div>
                                                    @endif
                                                    @if($cell['classroom'])
                                                        <div class="text-[10px] text-gray-500 truncate">📍 {{ $cell['classroom'] }}</div>
                                                    @endif
                                                @else
                                                    <div class="text-xs text-gray-400 text-center">
                                                        <span class="text-lg">+</span>
                                                    </div>
                                                @endif
                                            </button>
                                        @else
                                            <div class="p-2 text-xs text-gray-400 italic">
                                                {{ \App\Models\TimeBlock::TYPES[$row['type']] ?? '' }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Leyenda --}}
            <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Asignado</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-50 border border-dashed border-gray-300"></span> Vacío (clic para asignar)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-100"></span> Recreo/Almuerzo</span>
            </div>

        @elseif($section_id && !$hasBlocks)
            <div class="rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50 p-8 text-center dark:bg-indigo-900/20">
                <p class="font-semibold text-indigo-800 dark:text-indigo-400 mb-2">No hay bloques horarios configurados</p>
                <p class="text-sm text-indigo-600 dark:text-indigo-500 mb-4">Genera los bloques según la tanda del centro educativo</p>
                <button wire:click="generateDefaultBlocks" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    ⚡ Generar Bloques Automáticamente
                </button>
            </div>
        @endif
    @endif

    {{-- Modal: Editar Celda --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    Asignar Clase
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ \App\Models\SchoolSchedule::DAYS[$editDay] ?? $editDay }}
                </p>

                @if($conflictWarning)
                    <div class="mb-3 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        {{ $conflictWarning }}
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignatura</label>
                        <select wire:model="editSubjectId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">— Vacío —</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Docente</label>
                        <select wire:model.live="editTeacherId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Sin asignar</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aula</label>
                        <input type="text" wire:model="editClassroom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: A-101" />
                    </div>
                </div>

                <div class="flex justify-between gap-3 pt-4 mt-4 border-t dark:border-gray-700">
                    <button wire:click="clearCell" class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400">
                        🗑️ Vaciar
                    </button>
                    <div class="flex gap-2">
                        <button wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button wire:click="saveCell" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
