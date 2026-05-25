<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Entrada de Calificaciones</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Escala MINERD: 0-100 | Aprobación: 70 (Secundaria) / 65 (Primaria)</p>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('message') }}
        </div>
    @endif

    @if(!$activeYear)
        <div class="rounded-xl border-2 border-dashed border-yellow-300 bg-yellow-50 p-8 text-center dark:bg-yellow-900/20 dark:border-yellow-700">
            <svg class="mx-auto h-12 w-12 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
            <p class="mt-2 font-semibold text-yellow-800 dark:text-yellow-400">No hay año escolar activo</p>
            <p class="text-sm text-yellow-600 dark:text-yellow-500">Crea y activa un año escolar primero</p>
        </div>
    @else
        {{-- Selectores --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar sección...</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->gradeLevel?->short_name }} {{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Período</label>
                <select wire:model.live="period_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar período...</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }} (P{{ $period->number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignatura</label>
                <select wire:model.live="section_subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar asignatura...</option>
                    @foreach($sectionSubjects as $ss)
                        <option value="{{ $ss->id }}">{{ $ss->subject?->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Tabla de calificaciones --}}
        @if(count($grades) > 0)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ count($grades) }} estudiantes</span>
                    <div class="flex gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-green-800"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> 89-100 Destacado</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-blue-800"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> 77-88 Logro</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-0.5 text-yellow-800"><span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span> 65-76 En Proceso</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-red-800"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> &lt;65 Insuficiente</span>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estudiante</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-500 w-32">Calificación (0-100)</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-500">Nivel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($grades as $index => $grade)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-2 text-sm text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-6 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $grade['student_name'] }}</td>
                                <td class="px-6 py-2 text-center">
                                    <input type="number" wire:model.lazy="grades.{{ $index }}.score" min="0" max="100" step="0.01"
                                        class="w-24 rounded-lg border-gray-300 text-center text-sm font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="--" />
                                </td>
                                <td class="px-6 py-2 text-center">
                                    @if($grade['score'] !== '' && $grade['score'] !== null)
                                        @php $score = floatval($grade['score']); @endphp
                                        @if($score >= 89)
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-400">Destacado</span>
                                        @elseif($score >= 77)
                                            <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">Logro Evidenciado</span>
                                        @elseif($score >= 65)
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400">En Proceso</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-400">Insuficiente</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 flex justify-end">
                    <button wire:click="saveGrades" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-50 transition">
                        <svg wire:loading.remove class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd" /></svg>
                        <svg wire:loading class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Guardar Calificaciones
                    </button>
                </div>
            </div>
        @elseif($section_id && $period_id && $section_subject_id)
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-sm text-gray-500">No hay estudiantes inscritos en esta sección</p>
            </div>
        @endif
    @endif
</div>
