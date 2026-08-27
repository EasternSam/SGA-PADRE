<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Boletines de Notas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestionar observaciones y generar boletines PDF formato MINERD</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('message') }}
        </div>
    @endif

    @if(!$activeYear)
        <div class="rounded-xl border-2 border-dashed border-yellow-300 bg-yellow-50 p-8 text-center dark:bg-yellow-900/20">
            <p class="font-semibold text-yellow-800 dark:text-yellow-400">No hay año escolar activo</p>
        </div>
    @else
        {{-- Controles --}}
        <div class="flex flex-wrap gap-4 mb-6 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar sección...</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->gradeLevel?->short_name }} {{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Período</label>
                <select wire:model.live="period_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar...</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($section_id && $period_id && count($students) > 0)
                <a href="{{ route('reports.report-cards.batch', ['section' => $section_id, 'period' => $period_id]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.875 1.5C6.839 1.5 6 2.34 6 3.375v2.99c-.426.053-.851.11-1.274.174-1.454.218-2.476 1.483-2.476 2.917v6.294a3 3 0 0 0 3 3h.27l-.155 1.705A1.875 1.875 0 0 0 7.232 22.5h9.536a1.875 1.875 0 0 0 1.867-2.045l-.155-1.705h.27a3 3 0 0 0 3-3V9.456c0-1.434-1.022-2.7-2.476-2.917A48.716 48.716 0 0 0 18 6.366V3.375c0-1.036-.84-1.875-1.875-1.875h-8.25ZM16.5 6.205v-2.83A.375.375 0 0 0 16.125 3h-8.25a.375.375 0 0 0-.375.375v2.83a49.353 49.353 0 0 1 9 0Zm-.217 8.265c.178.018.317.16.333.337l.526 5.784a.375.375 0 0 1-.374.413H7.232a.375.375 0 0 1-.374-.413l.526-5.784a.373.373 0 0 1 .333-.337 41.741 41.741 0 0 1 8.566 0Zm.967-3.97a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H18a.75.75 0 0 1-.75-.75V10.5Z" clip-rule="evenodd" /></svg>
                    Imprimir Todos ({{ count($students) }})
                </a>
            @endif
        </div>

        {{-- Lista de estudiantes --}}
        @if(count($students) > 0)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 w-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estudiante</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Observaciones</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Conducta</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($students as $index => $student)
                            @php $card = $reportCards[$student['id']] ?? null; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 text-sm text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $student['first_name'] }} {{ $student['last_name'] }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($card && $card['teacher_comments'])
                                        <span class="text-green-500 text-xs font-medium">Tiene</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin observaciones</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center text-sm">
                                    {{ $card['conduct_grade'] ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <button wire:click="openNotes({{ $student['id'] }})" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">
                                        Observaciones
                                    </button>
                                    <a href="{{ route('reports.report-card', ['student' => $student['id'], 'period' => $period_id]) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($section_id && $period_id)
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-sm text-gray-500">No hay estudiantes en esta sección</p>
            </div>
        @endif
    @endif

    {{-- Modal: Observaciones --}}
    @if($showNotesModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showNotesModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Observaciones del Boletín</h3>
                <p class="text-sm text-gray-500 mb-4">{{ $editStudentName }}</p>
                <form wire:submit="saveNotes" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones del Docente</label>
                        <textarea wire:model="teacher_comments" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: El/la estudiante muestra buen desempeño en..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones del Orientador/a</label>
                        <textarea wire:model="counselor_comments" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Calificación de Conducta</label>
                        <select wire:model="conduct_grade" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Seleccionar...</option>
                            <option value="A">A - Excelente</option>
                            <option value="B">B - Buena</option>
                            <option value="C">C - Regular</option>
                            <option value="D">D - Deficiente</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showNotesModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
