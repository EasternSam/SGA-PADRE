<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">📊 Estadísticas por Asignatura</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Rendimiento académico por materia: promedios, aprobación, mínimos y máximos</p>

    <div class="mb-6">
        <select wire:model.live="period_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            @foreach($periods as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
    </div>

    @if(count($subjectStats) > 0)
        {{-- Summary Cards --}}
        @php
            $bestSubject = collect($subjectStats)->sortByDesc('avg')->first();
            $worstSubject = collect($subjectStats)->sortBy('avg')->first();
            $overallAvg = round(collect($subjectStats)->avg('avg'), 1);
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <p class="text-xs text-green-600 font-medium">🏆 Mejor Asignatura</p>
                <p class="text-lg font-bold text-green-800 dark:text-green-400">{{ $bestSubject['name'] ?? '' }}</p>
                <p class="text-sm text-green-700">Promedio: {{ $bestSubject['avg'] ?? '' }}</p>
            </div>
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                <p class="text-xs text-blue-600 font-medium">📊 Promedio General</p>
                <p class="text-3xl font-bold text-blue-800 dark:text-blue-400">{{ $overallAvg }}</p>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <p class="text-xs text-red-600 font-medium">⚠️ Mayor Dificultad</p>
                <p class="text-lg font-bold text-red-800 dark:text-red-400">{{ $worstSubject['name'] ?? '' }}</p>
                <p class="text-sm text-red-700">Promedio: {{ $worstSubject['avg'] ?? '' }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Asignatura</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Promedio</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Mín</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Máx</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Aprobados</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Reprobados</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">% Aprobación</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Notas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($subjectStats as $i => $ss)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $ss['name'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-bold {{ $ss['avg'] >= 80 ? 'text-green-600' : ($ss['avg'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $ss['avg'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $ss['min'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $ss['max'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-green-600 font-bold">{{ $ss['approved'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-red-600 font-bold">{{ $ss['failed'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 relative">
                                    <div class="h-4 rounded-full {{ $ss['approvalRate'] >= 80 ? 'bg-green-500' : ($ss['approvalRate'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                         style="width: {{ $ss['approvalRate'] }}%"></div>
                                    <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-gray-800 dark:text-gray-200">{{ $ss['approvalRate'] }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $ss['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <p class="text-sm text-gray-400">Sin calificaciones para el período seleccionado</p>
        </div>
    @endif
</div>
