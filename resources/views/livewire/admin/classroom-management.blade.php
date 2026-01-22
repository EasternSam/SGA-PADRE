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
                    @if (session()->has('message'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('message') }}
                        </div>
                    @endif
                    
                    @php
                        // Usamos data_get para acceder a weekSchedules de forma segura (objeto o array)
                        // Aseguramos que sea una colección iterable
                        $schedules = is_array($weekSchedules) ? collect($weekSchedules) : ($weekSchedules ?? collect());
                        $hasSchedules = $schedules->isNotEmpty();
                    @endphp

                    @if($hasSchedules)
                        @php
                            // Agrupamos por días de la semana
                            $groupedByDay = [];
                            // Días definidos en el componente para ordenamiento
                            $dayOrder = array_flip(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
                            
                            foreach($schedules as $schedule) {
                                // Usamos data_get para asegurar acceso a propiedades ya sea objeto o array
                                $days = data_get($schedule, 'days_of_week');
                                
                                if(is_array($days)) {
                                    foreach($days as $day) {
                                        $dayKey = ucfirst(strtolower($day));
                                        
                                        // Extraer datos usando data_get para ser robustos
                                        $moduleId = data_get($schedule, 'module_id');
                                        $courseName = data_get($schedule, 'module.course.name') ?? 'Curso';
                                        $moduleName = data_get($schedule, 'module.name');
                                        $teacherName = data_get($schedule, 'teacher.name') ?? 'Sin profesor';
                                        $sectionName = data_get($schedule, 'section_name');
                                        
                                        $startTime = data_get($schedule, 'start_time');
                                        $endTime = data_get($schedule, 'end_time');
                                        
                                        $startFormatted = $startTime ? \Carbon\Carbon::parse($startTime)->format('g:i A') : 'N/A';
                                        $endFormatted = $endTime ? \Carbon\Carbon::parse($endTime)->format('g:i A') : 'N/A';
                                        
                                        $groupedByDay[$dayKey][] = [
                                            'id' => data_get($schedule, 'id'),
                                            'course' => $courseName,
                                            'module' => $moduleName,
                                            'section' => $sectionName,
                                            'teacher' => $teacherName,
                                            'start_real' => $startFormatted,
                                            'end_real' => $endFormatted,
                                            'start_timestamp' => $startTime,
                                            'color' => 'border-indigo-500' 
                                        ];
                                    }
                                }
                            }

                            // Ordenar días según el orden de la semana
                            uksort($groupedByDay, function($a, $b) use ($dayOrder) {
                                $aIndex = $dayOrder[$a] ?? 99;
                                $bIndex = $dayOrder[$b] ?? 99;
                                return $aIndex - $bIndex;
                            });

                            // Ordenar horas dentro de cada día
                            foreach($groupedByDay as $day => &$items) {
                                usort($items, function($a, $b) {
                                    return strcmp($a['start_timestamp'], $b['start_timestamp']);
                                });
                            }
                        @endphp

                        @foreach($groupedByDay as $dayName => $slots)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 ml-1">{{ $dayName }}</h3>
                                <div class="space-y-3">
                                    @foreach($slots as $slot)
                                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col sm:flex-row gap-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                                            <!-- Banda lateral color -->
                                            <div class="w-1.5 bg-indigo-500 absolute left-0 top-0 bottom-0"></div>
                                            
                                            <!-- Hora -->
                                            <div class="flex-shrink-0 min-w-[100px] flex flex-col justify-center border-r border-gray-100 pr-4 sm:mr-0 mr-4">
                                                <span class="text-lg font-bold text-gray-900 font-mono leading-none">{{ $slot['start_real'] }}</span>
                                                <span class="text-xs text-gray-400 font-medium my-1">a</span>
                                                <span class="text-sm text-gray-600 font-medium">{{ $slot['end_real'] }}</span>
                                            </div>

                                            <!-- Info Curso -->
                                            <div class="flex-1 p-4 min-w-0 flex flex-col justify-center">
                                                <div class="flex justify-between items-start mb-1">
                                                    <div>
                                                        <h4 class="text-base font-bold text-gray-900 truncate pr-2" title="{{ $slot['course'] }}">
                                                            {{ $slot['course'] }}
                                                        </h4>
                                                        @if(!empty($slot['section']))
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 whitespace-nowrap">
                                                                Sec: {{ $slot['section'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <!-- Botón Desvincular -->
                                                    <button wire:click="detachClassroom({{ $slot['id'] }})" wire:confirm="¿Seguro que deseas liberar esta aula de esta sección?" class="text-gray-400 hover:text-red-600 transition-colors p-1" title="Desvincular Aula">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                                
                                                <p class="text-xs text-gray-500 mb-2 truncate">{{ $slot['module'] }}</p>

                                                <div class="flex items-center gap-4 text-sm text-gray-600 pt-2 border-t border-gray-50 mt-auto">
                                                    <div class="flex items-center gap-1.5 min-w-0" title="Profesor">
                                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        <span class="truncate font-medium">{{ $slot['teacher'] }}</span>
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
                            <div class="h-16 w-16 rounded-full bg-green-50 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">¡Aula Disponible!</h3>
                            <p class="text-gray-500 mt-2 max-w-sm">No hay horarios programados para este espacio en las fechas actuales. Está libre para asignación.</p>
                        </div>
                    @endif
                </div>

                {{-- 3. FOOTER DEL MODAL --}}
                <div class="bg-white border-t border-gray-200 p-4 flex justify-between items-center shrink-0">
                    <div class="text-xs text-gray-400">
                        * Mostrando agenda activa.
                    </div>
                    <x-secondary-button wire:click="closeModal">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>