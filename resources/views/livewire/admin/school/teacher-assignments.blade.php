<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Asignación de Docentes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Asignar profesores a secciones y asignaturas</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Section selector + assignments --}}
        <div class="lg:col-span-2">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                <select wire:model.live="section_id" class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar sección...</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}">{{ $s->gradeLevel?->short_name }} {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(count($assignments) > 0)
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Asignatura</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Docente Asignado</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Titular</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-500">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($assignments as $a)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        📚 {{ $a['subject_name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($a['teacher_id'])
                                            <span class="text-gray-900 dark:text-white font-medium">👩‍🏫 {{ $a['teacher_name'] }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($a['is_homeroom'])
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">TITULAR</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button wire:click="openAssign({{ $a['subject_id'] }})" class="text-sm text-blue-600 hover:text-blue-800 font-medium mr-2">
                                            {{ $a['teacher_id'] ? 'Cambiar' : 'Asignar' }}
                                        </button>
                                        @if($a['teacher_id'])
                                            <button wire:click="removeAssignment({{ $a['subject_id'] }})" wire:confirm="¿Remover asignación?" class="text-sm text-red-500 hover:text-red-700">×</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($section_id)
                <div class="rounded-xl border-2 border-dashed border-yellow-300 bg-yellow-50 p-6 text-center dark:bg-yellow-900/20">
                    <p class="text-sm text-yellow-800 dark:text-yellow-400">Esta sección no tiene asignaturas configuradas.</p>
                    <p class="text-xs text-yellow-600 mt-1">Configura las asignaturas de la sección primero en el módulo de Asignaturas.</p>
                </div>
            @endif
        </div>

        {{-- Right: Teacher workload --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 h-fit">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">📊 Carga Docente</h3>
            @if(count($workloads) > 0)
                <div class="space-y-2">
                    @php $maxLoad = max($workloads) ?: 1; @endphp
                    @foreach($teachers as $t)
                        @if(isset($workloads[$t->id]))
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-700 dark:text-gray-300 w-24 truncate" title="{{ $t->name }}">{{ Str::limit($t->name, 15) }}</span>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                    <div class="h-4 rounded-full flex items-center justify-end pr-1.5 transition-all {{ $workloads[$t->id] > 12 ? 'bg-red-500' : ($workloads[$t->id] > 8 ? 'bg-yellow-500' : 'bg-emerald-500') }}"
                                         style="width: {{ ($workloads[$t->id] / $maxLoad) * 100 }}%">
                                        <span class="text-[9px] font-bold text-white">{{ $workloads[$t->id] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-3 text-[10px] text-gray-400 flex gap-2">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Normal</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500"></span>Alta</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>Sobrecarga</span>
                </div>
            @else
                <p class="text-sm text-gray-400 text-center py-4">Sin asignaciones aún</p>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Asignar Docente</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignatura</label>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ collect($assignments)->firstWhere('subject_id', $editSubjectId)['subject_name'] ?? '' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Docente</label>
                        <select wire:model="editTeacherId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Seleccionar...</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} {{ isset($workloads[$t->id]) ? '(' . $workloads[$t->id] . ' asignaciones)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="editIsHomeroom" class="rounded border-gray-300 text-blue-600">
                        Docente Titular de esta sección
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-4 mt-4 border-t dark:border-gray-700">
                    <button wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                    <button wire:click="saveAssignment" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
