<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cuadro de Honor</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Estudiantes destacados por rendimiento académico</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-4 mb-6 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Período</label>
            <select wire:model.live="period_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white min-w-[200px]">
                <option value="">Seleccionar...</option>
                @foreach($periods as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grado</label>
            <select wire:model.live="grade_level_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white min-w-[200px]">
                <option value="">Todos los grados</option>
                @foreach($gradeLevels as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Promedio mínimo</label>
            <input type="number" wire:model.live.debounce.500ms="minAverage" min="70" max="100" step="1" class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
        </div>
    </div>

    @if(count($honorStudents) > 0)
        {{-- Stats --}}
        <div class="flex gap-4 mb-4">
            <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 px-4 py-2">
                <span class="text-lg font-bold text-yellow-700 dark:text-yellow-400">{{ count($honorStudents) }}</span>
                <span class="text-sm text-yellow-600 ml-1">estudiantes</span>
            </div>
        </div>

        {{-- Podium (Top 3) --}}
        @if(count($honorStudents) >= 3)
            <div class="flex justify-center gap-4 mb-8">
                {{-- 2nd place --}}
                <div class="text-center mt-8">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 mx-auto flex items-center justify-center text-xl font-bold text-white shadow-lg">
                        {{ strtoupper(substr($honorStudents[1]['student']->first_name, 0, 1)) }}{{ strtoupper(substr($honorStudents[1]['student']->last_name, 0, 1)) }}
                    </div>
                    <div class="mt-2">
                        <span class="text-2xl"></span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $honorStudents[1]['student']->full_name }}</p>
                        <p class="text-lg font-bold text-gray-600">{{ $honorStudents[1]['average'] }}</p>
                    </div>
                </div>
                {{-- 1st place --}}
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-yellow-400 to-amber-500 mx-auto flex items-center justify-center text-2xl font-bold text-white shadow-xl ring-4 ring-yellow-300">
                        {{ strtoupper(substr($honorStudents[0]['student']->first_name, 0, 1)) }}{{ strtoupper(substr($honorStudents[0]['student']->last_name, 0, 1)) }}
                    </div>
                    <div class="mt-2">
                        <span class="text-3xl"></span>
                        <p class="text-base font-bold text-gray-900 dark:text-white">{{ $honorStudents[0]['student']->full_name }}</p>
                        <p class="text-xl font-bold text-yellow-600">{{ $honorStudents[0]['average'] }}</p>
                    </div>
                </div>
                {{-- 3rd place --}}
                <div class="text-center mt-12">
                    <div class="w-18 h-18 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 mx-auto flex items-center justify-center text-lg font-bold text-white shadow-lg" style="width: 4.5rem; height: 4.5rem;">
                        {{ strtoupper(substr($honorStudents[2]['student']->first_name, 0, 1)) }}{{ strtoupper(substr($honorStudents[2]['student']->last_name, 0, 1)) }}
                    </div>
                    <div class="mt-2">
                        <span class="text-2xl"></span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $honorStudents[2]['student']->full_name }}</p>
                        <p class="text-lg font-bold text-amber-700">{{ $honorStudents[2]['average'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Full list --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gradient-to-r from-yellow-500 to-amber-500">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Pos.</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Estudiante</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Grado/Sección</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-white">Asignaturas</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-white">Mín.</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-white">Máx.</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-white">Promedio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($honorStudents as $i => $h)
                        <tr class="{{ $i < 3 ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300">
                                @if($i === 0) @elseif($i === 1) @elseif($i === 2) @else {{ $i + 1 }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $h['student']->full_name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $h['section']?->gradeLevel?->short_name ?? '' }} {{ $h['section']?->name ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">{{ $h['grade_count'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">{{ $h['min_score'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">{{ $h['max_score'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold
                                    {{ $h['average'] >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400' }}">
                                    {{ $h['average'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($period_id)
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <p class="text-lg text-gray-400 mb-1">Sin estudiantes con promedio ≥ {{ $minAverage }}</p>
            <p class="text-sm text-gray-400">Intenta bajar el promedio mínimo</p>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
            <p class="text-lg text-gray-400 mb-1">Selecciona un período</p>
            <p class="text-sm text-gray-400">para ver el cuadro de honor</p>
        </div>
    @endif
</div>
