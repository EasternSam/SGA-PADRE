<div class="py-12">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Aulas y Espacios') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
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
                                // Verificamos si tiene horarios asignados aunque no esté ocupada ahora
                                $hasSchedules = $classroom->schedules->isNotEmpty();

                                if ($isOccupied) {
                                    $statusColor = 'bg-red-50 border-red-200 ring-1 ring-red-200';
                                    $statusBadge = 'bg-red-100 text-red-700';
                                    $statusText = 'Ocupada';
                                } elseif ($hasSchedules) {
                                    // Disponible pero con uso programado -> Ámbar
                                    $statusColor = 'bg-amber-50 border-amber-200 ring-1 ring-amber-200';
                                    $statusBadge = 'bg-amber-100 text-amber-800';
                                    $statusText = 'Disponible';
                                } else {
                                    // Totalmente libre -> Verde
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

    {{-- MODAL DE HORARIO DETALLADO (DISEÑO MEJORADO) --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="7xl">
        <div class="p-6 h-[90vh] flex flex-col bg-white rounded-lg">
            @if($selectedClassroom)
                <!-- Encabezado del Modal -->
                <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $selectedClassroom->name }}</h2>
                            @if($selectedClassroom->type == 'Laboratorio')
                                <span class="bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-full uppercase tracking-wide font-semibold border border-indigo-200">
                                    Laboratorio
                                </span>
                            @endif
                            <span class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full border border-gray-200">
                                {{ $selectedClassroom->building->name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                {{ $selectedClassroom->capacity }} Estudiantes
                            </span>
                            @if($selectedClassroom->pc_count > 0)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    {{ $selectedClassroom->pc_count }} PCs
                                </span>
                            @endif
                            @if($selectedClassroom->equipment)
                                <span class="flex items-center gap-1" title="Equipamiento">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ Str::limit($selectedClassroom->equipment, 50) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- CONTENEDOR DEL CALENDARIO -->
                <div class="flex-1 overflow-hidden flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm relative">
                    <!-- Cabecera de Días (Sticky) -->
                    <div class="grid grid-cols-8 border-b border-gray-200 bg-gray-50 sticky top-0 z-20">
                        <div class="col-span-1 py-3 px-2 text-center text-xs font-bold text-gray-400 uppercase tracking-wider border-r border-gray-200 bg-gray-50">
                            Hora
                        </div>
                        @foreach($daysOfWeek as $day)
                            <div class="col-span-1 py-3 px-2 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 last:border-r-0">
                                {{ $day }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Cuerpo del Calendario (Scrollable) -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative">
                        <div class="grid grid-cols-8 relative min-h-[600px]"> <!-- Grid base -->
                            
                            <!-- Líneas de fondo y columna de horas -->
                            @foreach($timeSlots as $index => $time)
                                <!-- Celda de Hora -->
                                <div class="col-span-1 border-r border-b border-gray-100 bg-gray-50/50 text-xs text-gray-400 flex items-start justify-center pt-2 font-mono h-20 sticky left-0 z-10">
                                    {{ $time }}
                                </div>
                                
                                <!-- Celdas vacías de fondo para la grilla -->
                                @foreach($daysOfWeek as $dayIndex => $day)
                                    <div class="col-span-1 border-r border-b border-gray-100 h-20 relative bg-white hover:bg-gray-50/30 transition-colors">
                                        <!-- Placeholder visual -->
                                    </div>
                                @endforeach
                            @endforeach

                            <!-- CAPA DE EVENTOS (ABSOLUTE POSITIONING) -->
                            <!-- Aquí colocamos los bloques de cursos sobre la grilla -->
                            <div class="absolute inset-0 pointer-events-none grid grid-cols-8">
                                <div class="col-span-1"></div> <!-- Espacio para la columna de hora -->
                                
                                @foreach($daysOfWeek as $dayIndex => $day)
                                    <div class="col-span-1 relative h-full">
                                        @foreach($calendarGrid as $timeKey => $dayData)
                                            @if(isset($dayData[$day]))
                                                @php 
                                                    $slot = $dayData[$day]; 
                                                    // Calculamos la posición top basada en la hora de inicio
                                                    // Asumimos que la grilla empieza a las 7:00 AM y cada hora son 80px (h-20)
                                                    $startHour = (int) substr($slot['start_real'], 0, 2);
                                                    $startMin = (int) substr($slot['start_real'], 3, 2);
                                                    $gridStartHour = 7; // Hora inicio del calendario
                                                    
                                                    // Top en pixeles = (Horas pasadas * 80px) + (Minutos / 60 * 80px)
                                                    $topPosition = (($startHour - $gridStartHour) * 80) + (($startMin / 60) * 80);
                                                    
                                                    // Altura basada en la duración real en horas
                                                    // Duración * 80px
                                                    // Parseamos horas para calcular duración exacta
                                                    $s = \Carbon\Carbon::parse($slot['start_real']);
                                                    $e = \Carbon\Carbon::parse($slot['end_real']);
                                                    $durationHours = $e->diffInMinutes($s) / 60;
                                                    $height = $durationHours * 80;
                                                @endphp

                                                <div class="absolute inset-x-1 rounded-lg p-2 shadow-sm border text-xs leading-tight flex flex-col justify-center overflow-hidden pointer-events-auto transition-transform hover:scale-[1.02] hover:shadow-md z-10 cursor-default {{ $slot['color'] }}"
                                                     style="top: {{ $topPosition }}px; height: {{ $height - 4 }}px;">
                                                    
                                                    <div class="font-bold truncate text-sm mb-0.5" title="{{ $slot['course'] }}">
                                                        {{ $slot['course'] }}
                                                    </div>
                                                    
                                                    @if(!empty($slot['section']))
                                                        <div class="text-[10px] uppercase tracking-wide opacity-80 truncate mb-1">
                                                            Sec: {{ $slot['section'] }}
                                                        </div>
                                                    @endif

                                                    <div class="opacity-90 truncate text-[10px] flex items-center gap-1 mb-auto">
                                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        {{ Str::limit($slot['teacher'], 15) }}
                                                    </div>
                                                    
                                                    <div class="text-[9px] font-mono opacity-100 font-bold bg-white/20 px-1.5 py-0.5 rounded-full self-start inline-block mt-1">
                                                        {{ $slot['start_real'] }} - {{ $slot['end_real'] }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center flex-shrink-0 pt-2 border-t border-gray-100">
                    <div class="text-xs text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span> Horario Libre
                        <span class="w-2 h-2 bg-indigo-500 rounded-full ml-2"></span> Horario Ocupado
                    </div>
                    <x-secondary-button wire:click="closeModal">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>