<div>
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Ciclo de Vida Estudiantil</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Seguimiento completo desde la inscripción hasta la graduación</p>
        </div>

        {{-- Buscador --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-8 shadow-sm">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Buscar Estudiante</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Nombre, apellido o matrícula..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <svg class="absolute right-3 top-3.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            @if($searchResults->count())
                <div class="mt-3 border border-gray-200 dark:border-gray-600 rounded-lg divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
                    @foreach($searchResults as $s)
                        <button wire:click="selectStudent({{ $s->id }})" class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center justify-between">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $s->full_name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $s->student_code ?? '' }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $s->gradeLevel?->short_name ?? '' }} {{ $s->section?->name ?? '' }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if($journey && $studentInfo)
            {{-- Info del Estudiante --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-8 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-300">{{ substr($studentInfo['full_name'], 0, 1) }}</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $studentInfo['full_name'] }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $studentInfo['grade'] }} — {{ $studentInfo['section'] }}
                                <span class="mx-2">·</span>
                                Matrícula: {{ $studentInfo['student_code'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Progreso General --}}
                    <div class="text-right">
                        <div class="text-3xl font-black {{ $journey['overall_progress'] >= 80 ? 'text-emerald-600' : ($journey['overall_progress'] >= 40 ? 'text-indigo-600' : 'text-gray-400') }}">
                            {{ $journey['overall_progress'] }}%
                        </div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Progreso General</div>
                    </div>
                </div>

                {{-- Barra de progreso compacta --}}
                <div class="mt-4 flex items-center gap-1">
                    @foreach($journey['stages'] as $i => $stage)
                        <div class="flex-1 h-2 rounded-full transition-all duration-500
                            @if($stage['status'] === 'completed') bg-emerald-500
                            @elseif($stage['status'] === 'in_progress') bg-indigo-500 animate-pulse
                            @elseif($stage['status'] === 'warning') bg-amber-500
                            @elseif($stage['status'] === 'danger') bg-red-500
                            @else bg-gray-200 dark:bg-gray-700
                            @endif
                        "></div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-gray-400 uppercase tracking-wider font-bold">
                    @foreach($journey['stages'] as $stage)
                        <span>{{ $stage['name'] }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Timeline Detallada --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-8 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Etapas del Ciclo Escolar</h3>

                <div class="relative">
                    {{-- Línea vertical --}}
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                    @foreach($journey['stages'] as $i => $stage)
                        <div class="relative flex gap-6 mb-8 last:mb-0" wire:key="stage-{{ $i }}">
                            {{-- Nodo --}}
                            <div class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center shrink-0 transition-all duration-300
                                @if($stage['status'] === 'completed') bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/30
                                @elseif($stage['status'] === 'in_progress') bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-2 ring-indigo-500/30 animate-pulse
                                @elseif($stage['status'] === 'warning') bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 ring-2 ring-amber-500/30
                                @elseif($stage['status'] === 'danger') bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 ring-2 ring-red-500/30
                                @else bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500
                                @endif
                            ">
                                @if($stage['status'] === 'completed')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                @elseif($stage['status'] === 'in_progress')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($stage['status'] === 'warning' || $stage['status'] === 'danger')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                @else
                                    <span class="text-lg font-bold">{{ $i + 1 }}</span>
                                @endif
                            </div>

                            {{-- Contenido --}}
                            <div class="flex-1 pb-2">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-base font-bold
                                        @if($stage['status'] === 'completed') text-emerald-700 dark:text-emerald-400
                                        @elseif($stage['status'] === 'in_progress') text-indigo-700 dark:text-indigo-400
                                        @elseif($stage['status'] === 'warning') text-amber-700 dark:text-amber-400
                                        @elseif($stage['status'] === 'danger') text-red-700 dark:text-red-400
                                        @else text-gray-500 dark:text-gray-400
                                        @endif
                                    ">{{ $stage['name'] }}</h4>

                                    @if($stage['percent'] > 0 && $stage['status'] !== 'completed')
                                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $stage['percent'] }}%</span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $stage['description'] }}</p>

                                {{-- Barra de progreso por etapa --}}
                                @if($stage['status'] !== 'pending' && $stage['status'] !== 'completed')
                                    <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mb-3 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700
                                            @if($stage['status'] === 'warning') bg-amber-500
                                            @elseif($stage['status'] === 'danger') bg-red-500
                                            @else bg-indigo-500
                                            @endif
                                        " style="width: {{ $stage['percent'] }}%"></div>
                                    </div>
                                @endif

                                {{-- Detalles --}}
                                @if(!empty($stage['details']))
                                    <div class="flex flex-wrap gap-x-6 gap-y-1">
                                        @foreach($stage['details'] as $key => $value)
                                            <div class="text-xs">
                                                <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $key }}:</span>
                                                <span class="text-gray-700 dark:text-gray-300 font-semibold ml-1">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Historial Multi-Año --}}
            @if(count($history) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Historial Académico</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Año</th>
                                    <th class="text-left py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Grado</th>
                                    <th class="text-left py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Sección</th>
                                    <th class="text-left py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Estado</th>
                                    <th class="text-left py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Resultado</th>
                                    <th class="text-center py-3 px-4 text-xs uppercase tracking-wider font-bold text-gray-500">Promedio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($history as $record)
                                    <tr class="{{ $record['is_current'] ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : '' }}">
                                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                            {{ $record['year_name'] }}
                                            @if($record['is_current'])
                                                <span class="ml-1 text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 px-2 py-0.5 rounded-full font-bold uppercase">Actual</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ $record['grade'] }}</td>
                                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ $record['section'] }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 rounded-lg text-xs font-bold
                                                {{ $record['status'] === 'enrolled' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}
                                            ">{{ $record['status_label'] }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($record['promotion_label'])
                                                <span class="px-2 py-1 rounded-lg text-xs font-bold
                                                    {{ $record['promotion_result'] === 'promoted' || $record['promotion_result'] === 'graduated' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : '' }}
                                                    {{ $record['promotion_result'] === 'retained' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : '' }}
                                                ">{{ $record['promotion_label'] }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                                            {{ $record['average'] ? number_format($record['average'], 1) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        @elseif(!$journey && !$search)
            {{-- Estado vacío --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-16 text-center shadow-sm">
                <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300 mb-2">Selecciona un estudiante</h3>
                <p class="text-gray-500 dark:text-gray-400">Usa el buscador para ver el ciclo de vida completo de un estudiante.</p>
            </div>
        @endif

    </div>
</div>
