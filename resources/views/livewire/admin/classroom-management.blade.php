<div class="py-12">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Aulas y Espacios') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        {{-- VISTA PRINCIPAL DE TARJETAS (SIN CAMBIOS) --}}
        @foreach($buildings as $building)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        {{ $building->name }}
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($building->classrooms as $classroom)
                            @php
                                $isOccupied = $classroom->isOccupiedNow();
                                $hasSchedules = $classroom->schedules->isNotEmpty();

                                if ($isOccupied) {
                                    $statusColor = 'bg-red-50 border-red-200 ring-1 ring-red-200';
                                    $statusBadge = 'bg-red-100 text-red-700';
                                    $statusText = 'Ocupada';
                                } elseif ($hasSchedules) {
                                    $statusColor = 'bg-amber-50 border-amber-200 ring-1 ring-amber-200';
                                    $statusBadge = 'bg-amber-100 text-amber-800';
                                    $statusText = 'Disponible';
                                } else {
                                    $statusColor = 'bg-white border-gray-200 hover:border-indigo-300';
                                    $statusBadge = 'bg-green-100 text-green-700';
                                    $statusText = 'Libre';
                                }
                            @endphp

                            <button wire:click="showSchedule({{ $classroom->id }})" 
                                class="text-left group relative p-4 border rounded-xl shadow-sm hover:shadow-md transition-all {{ $statusColor }}">
                                
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-bold text-gray-800 text-lg group-hover:text-indigo-600 transition-colors">{{ $classroom->name }}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <div class="text-sm text-gray-600 space-y-1.5">
                                    <div class="flex items-center gap-2" title="Capacidad">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <span>{{ $classroom->capacity }} Estudiantes</span>
                                    </div>
                                    @if($classroom->pc_count > 0)
                                        <div class="flex items-center gap-2" title="Computadoras">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            <span>{{ $classroom->pc_count }} PCs</span>
                                        </div>
                                    @endif
                                    @if($classroom->type == 'Laboratorio')
                                        <div class="flex items-center gap-2" title="Tipo">
                                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                            <span>Laboratorio</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-indigo-600 text-xs font-bold flex items-center">
                                        Ver Horario <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL DE HORARIO DETALLADO (DISEÑO LISTA DE TARJETAS) --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="3xl">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
            @if($selectedClassroom)
                
                {{-- 1. ENCABEZADO DEL MODAL --}}
                <div class="bg-white border-b border-gray-200 p-5 flex items-center justify-between z-30 shadow-sm shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                            @if($selectedClassroom->type == 'Laboratorio')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 tracking-tight">{{ $selectedClassroom->name }}</h2>
                            <div class="flex items-center gap-2 mt-0.5 text-sm text-gray-500">
                                <span>{{ $selectedClassroom->building->name }}</span>
                                <span class="text-gray-300">•</span>
                                <span>Cap: <strong>{{ $selectedClassroom->capacity }}</strong></span>
                                @if($selectedClassroom->pc_count > 0)
                                    <span class="text-gray-300">•</span>
                                    <span>PCs: <strong>{{ $selectedClassroom->pc_count }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- 2. LISTA DE CURSOS PROGRAMADOS --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50 p-6 space-y-4">
                    @php
                        // Verificamos si hay horarios cargados en weekSchedules en lugar de usar calendarGrid
                        // Esto simplifica la lógica y evita el proceso de aplanado que causaba duplicados
                        $schedules = $weekSchedules ?? collect();
                        $hasSchedules = $schedules->isNotEmpty();
                    @endphp

                    @if($hasSchedules)
                        @php
                            // Agrupamos por días de la semana. Un curso puede aparecer en varios días.
                            $groupedByDay = [];
                            $dayOrder = array_flip($daysOfWeek);
                            
                            foreach($schedules as $schedule) {
                                if(is_array($schedule->days_of_week)) {
                                    foreach($schedule->days_of_week as $day) {
                                        // Normalizamos el día para agrupar
                                        $dayKey = ucfirst(strtolower($day));
                                        
                                        // Creamos un objeto simplificado para la vista para evitar conflictos de claves
                                        $groupedByDay[$dayKey][] = (object) [
                                            'id' => $schedule->id, // Útil para claves únicas si fuera necesario
                                            'course' => $schedule->module->course->name ?? 'Curso',
                                            'section' => $schedule->section_name,
                                            'teacher' => $schedule->teacher->name ?? 'Sin profesor',
                                            'start_real' => \Carbon\Carbon::parse($schedule->start_time)->format('g:i A'),
                                            'end_real' => \Carbon\Carbon::parse($schedule->end_time)->format('g:i A'),
                                            'start_timestamp' => $schedule->start_time, // Para ordenar
                                            // Generamos un color consistente basado en el ID del curso
                                            'color' => 'border-indigo-500 bg-indigo-50' 
                                        ];
                                    }
                                }
                            }

                            // Ordenar los días según el orden de la semana
                            uksort($groupedByDay, function($a, $b) use ($dayOrder) {
                                $aIndex = $dayOrder[$a] ?? 99;
                                $bIndex = $dayOrder[$b] ?? 99;
                                return $aIndex - $bIndex;
                            });

                            // Ordenar los horarios dentro de cada día
                            foreach($groupedByDay as $day => &$items) {
                                usort($items, function($a, $b) {
                                    return strcmp($a->start_timestamp, $b->start_timestamp);
                                });
                            }
                        @endphp

                        @foreach($groupedByDay as $dayName => $slots)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 ml-1">{{ $dayName }}</h3>
                                <div class="space-y-3">
                                    @foreach($slots as $slot)
                                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col sm:flex-row gap-4 hover:shadow-md transition-shadow relative overflow-hidden {{ $slot->color ?? '' }}">
                                            <!-- Banda de color lateral -->
                                            <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ str_replace('bg-', 'bg-', $slot->color) }} bg-indigo-500"></div>
                                            
                                            <!-- Hora -->
                                            <div class="flex-shrink-0 min-w-[100px] flex flex-col justify-center border-r border-gray-100 pr-4 sm:mr-0 mr-4">
                                                <span class="text-lg font-bold text-gray-900 font-mono">{{ $slot->start_real }}</span>
                                                <span class="text-xs text-gray-500 font-medium">{{ $slot->end_real }}</span>
                                                <span class="text-[10px] text-gray-400 mt-1 uppercase tracking-wide">Horario</span>
                                            </div>

                                            <!-- Info Curso -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="text-base font-bold text-gray-900 truncate" title="{{ $slot->course }}">
                                                            {{ $slot->course }}
                                                        </h4>
                                                        @if(!empty($slot->section))
                                                            <p class="text-sm text-gray-600 mt-0.5">Sección: <strong class="bg-indigo-100 text-indigo-800 px-1.5 py-0.5 rounded text-xs">{{ $slot->section }}</strong></p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                                    <div class="flex items-center gap-1.5" title="Profesor">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        <span class="truncate max-w-[150px]">{{ $slot->teacher }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex flex-col items-center justify-center h-64 text-center">
                            <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Disponible</h3>
                            <p class="text-gray-500 mt-1 max-w-sm">No hay horarios programados para esta aula actualmente.</p>
                        </div>
                    @endif
                </div>

                {{-- 3. FOOTER DEL MODAL --}}
                <div class="bg-white border-t border-gray-200 p-4 flex justify-between items-center shrink-0">
                    <div class="text-xs text-gray-400">
                        * Mostrando horarios ocupados.
                    </div>
                    <x-secondary-button wire:click="closeModal">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>