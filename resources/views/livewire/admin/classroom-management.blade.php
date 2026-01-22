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

    {{-- MODAL DE HORARIO DETALLADO (DISEÑO AGENDA DINÁMICA MEJORADO) --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="7xl">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl flex flex-col h-[90vh]">
            @if($selectedClassroom)
                
                {{-- 1. ENCABEZADO DEL MODAL (INFO DEL AULA) --}}
                <div class="bg-white border-b border-gray-200 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 z-30 shadow-sm relative">
                    <div class="flex items-center gap-5">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200">
                            @if($selectedClassroom->type == 'Laboratorio')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 tracking-tight">{{ $selectedClassroom->name }}</h2>
                            <div class="flex flex-wrap items-center gap-3 mt-0.5 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ $selectedClassroom->building->name }}
                                </span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span>Cap: <strong class="text-gray-700">{{ $selectedClassroom->capacity }}</strong></span>
                                @if($selectedClassroom->pc_count > 0)
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span>PCs: <strong class="text-gray-700">{{ $selectedClassroom->pc_count }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($selectedClassroom->equipment)
                            <div class="hidden md:block text-right">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Equipamiento</span>
                                <p class="text-sm font-medium text-gray-700 max-w-xs truncate" title="{{ $selectedClassroom->equipment }}">
                                    {{ $selectedClassroom->equipment }}
                                </p>
                            </div>
                        @endif
                        <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- 2. CUERPO DEL CALENDARIO (GRID DE HORAS Y DÍAS) --}}
                <div class="flex-1 overflow-hidden flex flex-col bg-white relative">
                    
                    {{-- Cabecera de Días (Sticky Top) --}}
                    <div class="grid grid-cols-8 border-b border-gray-200 min-w-[800px] sticky top-0 z-20 shadow-sm bg-gray-50">
                        <div class="col-span-1 py-3 px-2 text-center text-xs font-bold text-gray-400 uppercase tracking-wider border-r border-gray-200 bg-gray-50">
                            Hora
                        </div>
                        @foreach($daysOfWeek as $day)
                            <div class="col-span-1 py-3 px-2 text-center border-r border-gray-200 last:border-r-0">
                                <span class="block text-xs font-bold text-gray-400 uppercase mb-0.5">{{ substr($day, 0, 3) }}</span>
                                <span class="block text-sm font-bold text-gray-800">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grilla de Horarios (Scrollable) --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar relative bg-white">
                        <div class="relative min-w-[800px] min-h-[600px]"> 
                            
                            <!-- Grilla Base (Líneas y Horas) -->
                            @foreach($timeSlots as $index => $time)
                                <div class="grid grid-cols-8 h-20 border-b border-gray-100 group">
                                    <!-- Columna Hora (Sticky Left) -->
                                    <div class="col-span-1 bg-gray-50 border-r border-gray-200 text-xs text-gray-400 font-mono flex justify-center pt-2 relative sticky left-0 z-10">
                                        <span class="-mt-3 bg-gray-50 px-1 rounded">{{ $time }}</span>
                                        <!-- Línea guía visual -->
                                        <div class="absolute top-0 right-0 w-2 h-[1px] bg-gray-300"></div>
                                    </div>
                                    
                                    <!-- Celdas vacías (Fondo) -->
                                    @foreach($daysOfWeek as $dayIndex => $day)
                                        <div class="col-span-1 border-r border-gray-100/50 group-hover:bg-gray-50/30 transition-colors"></div>
                                    @endforeach
                                </div>
                            @endforeach

                            <!-- CAPA DE EVENTOS (Superpuesta con Posición Absoluta) -->
                            <div class="absolute inset-0 grid grid-cols-8 pointer-events-none">
                                <div class="col-span-1"></div> <!-- Espacio para columna de hora -->
                                
                                @foreach($daysOfWeek as $dayIndex => $day)
                                    <div class="col-span-1 relative h-full border-r border-transparent">
                                        @foreach($calendarGrid as $timeKey => $dayData)
                                            @if(isset($dayData[$day]))
                                                @php 
                                                    $slot = $dayData[$day]; 
                                                    
                                                    // --- CÁLCULO DE POSICIÓN ---
                                                    $startHour = (int) substr($slot['start_real'], 0, 2);
                                                    $startMin = (int) substr($slot['start_real'], 3, 2);
                                                    $gridStartHour = 7; // Debe coincidir con $start en mount()
                                                    
                                                    // Top (px) = (Horas desde inicio * 80px) + (Minutos / 60 * 80px)
                                                    // 80px es la altura fija de la fila h-20
                                                    $topPosition = (($startHour - $gridStartHour) * 80) + (($startMin / 60) * 80);
                                                    
                                                    // Height (px) = Duración en horas * 80px
                                                    $s = \Carbon\Carbon::parse($slot['start_real']);
                                                    $e = \Carbon\Carbon::parse($slot['end_real']);
                                                    $durationHours = $e->diffInMinutes($s) / 60;
                                                    $height = $durationHours * 80;
                                                @endphp

                                                <div class="absolute inset-x-1 rounded-lg border-l-4 shadow-sm p-2 flex flex-col justify-between overflow-hidden pointer-events-auto transition-all hover:scale-[1.02] hover:shadow-md hover:z-50 {{ $slot['color'] }}"
                                                     style="top: {{ $topPosition }}px; height: {{ $height - 4 }}px;">
                                                    
                                                    <div>
                                                        <div class="flex justify-between items-start gap-1">
                                                            <span class="font-bold text-xs leading-tight line-clamp-2" title="{{ $slot['course'] }}">
                                                                {{ $slot['course'] }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($slot['section']))
                                                            <div class="text-[10px] font-medium opacity-80 mt-0.5 truncate">
                                                                Sec: {{ $slot['section'] }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="mt-auto pt-1">
                                                        <div class="flex items-center gap-1 text-[10px] opacity-90 truncate mb-1" title="Profesor: {{ $slot['teacher'] }}">
                                                            <svg class="w-3 h-3 opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                            <span class="truncate">{{ Str::limit($slot['teacher'], 15) }}</span>
                                                        </div>
                                                        
                                                        <div class="inline-block px-1.5 py-0.5 rounded-md bg-white/40 text-[9px] font-mono font-bold tracking-tight shadow-sm border border-black/5">
                                                            {{ $slot['start_real'] }} - {{ $slot['end_real'] }}
                                                        </div>
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

                {{-- 3. FOOTER DEL MODAL --}}
                <div class="bg-gray-50 border-t border-gray-200 p-4 flex justify-between items-center shrink-0 rounded-b-xl">
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Curso Regular
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-purple-100 border border-purple-200"></span> Laboratorio
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-green-100 border border-green-200"></span> Taller
                        </div>
                    </div>
                    <x-secondary-button wire:click="closeModal">
                        Cerrar Agenda
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>