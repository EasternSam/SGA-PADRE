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
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl h-[90vh] flex flex-col font-sans">
            @if($selectedClassroom)
                
                {{-- 1. ENCABEZADO DEL MODAL (ESTILO CLEAN & MODERN) --}}
                <div class="bg-white border-b border-gray-100 px-6 py-4 flex-none z-40 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] flex justify-between items-center">
                    <div class="flex items-center gap-5">
                        {{-- Icono Grande --}}
                        <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                            @if($selectedClassroom->type == 'Laboratorio')
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            @else
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            @endif
                        </div>
                        
                        {{-- Título y Badges --}}
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $selectedClassroom->name }}</h2>
                                <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold uppercase tracking-wide border border-gray-200">
                                    {{ $selectedClassroom->building->name }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                <div class="flex items-center gap-1.5 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span>Cap: <span class="font-semibold text-gray-700">{{ $selectedClassroom->capacity }}</span></span>
                                </div>
                                
                                @if($selectedClassroom->pc_count > 0)
                                    <div class="flex items-center gap-1.5 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        <span>PCs: <span class="font-bold">{{ $selectedClassroom->pc_count }}</span></span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Equipamiento y Cerrar --}}
                    <div class="flex items-center gap-6">
                        @if($selectedClassroom->equipment)
                            <div class="hidden md:flex flex-col items-end border-r border-gray-200 pr-6 mr-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Equipamiento</span>
                                <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate text-right" title="{{ $selectedClassroom->equipment }}">
                                    {{ $selectedClassroom->equipment }}
                                </p>
                            </div>
                        @endif
                        <button wire:click="closeModal" class="group p-2 rounded-full hover:bg-red-50 text-gray-400 hover:text-red-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-6 h-6 transform group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- 2. CUERPO DEL CALENDARIO (GRID SCROLLABLE) --}}
                <div class="flex-1 overflow-hidden relative bg-slate-50 w-full">
                    <div class="h-full overflow-y-auto custom-scrollbar w-full relative">
                        
                        {{-- Cabecera de Días (Sticky Top con Blur) --}}
                        <div class="grid grid-cols-8 border-b border-gray-200/80 min-w-[800px] sticky top-0 z-30 bg-white/95 backdrop-blur-sm shadow-sm">
                            <div class="col-span-1 py-4 text-center border-r border-gray-100">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Hora</span>
                            </div>
                            @foreach($daysOfWeek as $day)
                                <div class="col-span-1 py-3 px-2 text-center border-r border-gray-100 last:border-r-0">
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5 tracking-wider">{{ substr($day, 0, 3) }}</span>
                                    <span class="block text-sm font-extrabold text-gray-800">{{ $day }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Grilla de Horarios --}}
                        <div class="relative min-w-[800px] pb-10"> 
                            
                            <!-- Grilla Base (Líneas y Horas) -->
                            @foreach($timeSlots as $index => $time)
                                <div class="grid grid-cols-8 h-20 border-b border-gray-200/60 hover:bg-gray-50/50 transition-colors">
                                    <!-- Columna Hora (Sticky Left) -->
                                    <div class="col-span-1 bg-white border-r border-gray-200/60 text-xs text-slate-400 font-medium font-mono flex justify-center pt-2 relative sticky left-0 z-20 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.02)]">
                                        <span class="-mt-3 px-2 py-0.5 bg-white rounded-full text-[11px]">{{ $time }}</span>
                                        <!-- Línea guía visual pequeña -->
                                        <div class="absolute top-0 right-0 w-1.5 h-[1px] bg-gray-300"></div>
                                    </div>
                                    
                                    <!-- Celdas vacías (Columnas) -->
                                    @foreach($daysOfWeek as $dayIndex => $day)
                                        <div class="col-span-1 border-r border-gray-200/40 border-dashed last:border-r-0"></div>
                                    @endforeach
                                </div>
                            @endforeach

                            <!-- CAPA DE EVENTOS (Superpuesta con Posición Absoluta) -->
                            <div class="absolute inset-0 grid grid-cols-8 pointer-events-none z-10">
                                <div class="col-span-1"></div> <!-- Espacio para columna de hora -->
                                
                                @foreach($daysOfWeek as $dayIndex => $day)
                                    <div class="col-span-1 relative h-full">
                                        @foreach($calendarGrid as $timeKey => $dayData)
                                            @if(isset($dayData[$day]))
                                                @php 
                                                    $slot = $dayData[$day]; 
                                                    
                                                    // --- CÁLCULO DE POSICIÓN (INTACTO) ---
                                                    $startHour = (int) substr($slot['start_real'], 0, 2);
                                                    $startMin = (int) substr($slot['start_real'], 3, 2);
                                                    $gridStartHour = 7; 
                                                    
                                                    $headerOffset = 65; // Ajuste por la altura del header sticky
                                                    $topPosition = $headerOffset + (($startHour - $gridStartHour) * 80) + (($startMin / 60) * 80);
                                                    
                                                    $s = \Carbon\Carbon::parse($slot['start_real']);
                                                    $e = \Carbon\Carbon::parse($slot['end_real']);
                                                    $durationHours = $e->diffInMinutes($s) / 60;
                                                    $height = $durationHours * 80;
                                                @endphp

                                                <div class="absolute inset-x-1 rounded-md border-l-[3px] shadow-sm p-2 flex flex-col justify-between overflow-hidden pointer-events-auto transition-all duration-200 hover:scale-[1.02] hover:shadow-lg hover:z-50 ring-1 ring-black/5 {{ $slot['color'] }}"
                                                     style="top: {{ $topPosition }}px; height: {{ $height - 2 }}px;">
                                                    
                                                    {{-- Contenido de la Tarjeta --}}
                                                    <div class="flex flex-col h-full">
                                                        <div class="mb-1">
                                                            <div class="flex justify-between items-start gap-1">
                                                                <span class="font-bold text-[11px] leading-tight text-gray-800 line-clamp-2" title="{{ $slot['course'] }}">
                                                                    {{ $slot['course'] }}
                                                                </span>
                                                            </div>
                                                            @if(!empty($slot['section']))
                                                                <div class="inline-block mt-1 px-1.5 py-0.5 rounded bg-white/60 text-[9px] font-bold text-gray-600 border border-black/5">
                                                                    Sec. {{ $slot['section'] }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div class="mt-auto pt-1 border-t border-black/5">
                                                            <div class="flex items-center gap-1.5 text-[10px] text-gray-700 mb-1" title="Profesor: {{ $slot['teacher'] }}">
                                                                <svg class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                                <span class="truncate font-medium">{{ Str::limit($slot['teacher'], 18) }}</span>
                                                            </div>
                                                            
                                                            <div class="text-[9px] font-mono text-gray-500 font-semibold tracking-tight text-right">
                                                                {{ $slot['start_real'] }} - {{ $slot['end_real'] }}
                                                            </div>
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
                <div class="bg-white border-t border-gray-200 p-4 flex-none z-40 flex justify-between items-center">
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
                        <span class="font-bold text-gray-400 uppercase tracking-wide mr-2">Referencias:</span>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-100 border border-blue-300 ring-1 ring-blue-100"></span> 
                            <span class="text-gray-600 font-medium">Curso Regular</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-purple-100 border border-purple-300 ring-1 ring-purple-100"></span> 
                            <span class="text-gray-600 font-medium">Laboratorio</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-100 border border-green-300 ring-1 ring-green-100"></span> 
                            <span class="text-gray-600 font-medium">Taller/Otros</span>
                        </div>
                    </div>
                    
                    <x-secondary-button wire:click="closeModal" class="!py-2 !px-4">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>