<div class="py-12 bg-gray-50 min-h-screen">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-gray-900 leading-tight">
                {{ __('Gestión de Espacios Físicos') }}
            </h2>
            <div class="text-sm text-gray-500">
                {{ $buildings->sum(fn($b) => $b->classrooms->count()) }} Espacios Registrados
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
        
        @foreach($buildings as $building)
            <div class="space-y-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-200 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $building->name }}</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($building->classrooms as $classroom)
                        @php
                            $isOccupied = $classroom->isOccupiedNow();
                            $hasSchedules = $classroom->schedules->isNotEmpty();

                            // Definición de Estilos de Tarjeta Principal
                            if ($isOccupied) {
                                $cardBorder = 'border-red-200';
                                $statusDot = 'bg-red-500';
                                $statusLabel = 'Ocupada';
                                $statusBg = 'bg-red-50 text-red-700';
                            } elseif ($hasSchedules) {
                                $cardBorder = 'border-amber-200';
                                $statusDot = 'bg-amber-500';
                                $statusLabel = 'Programada';
                                $statusBg = 'bg-amber-50 text-amber-700';
                            } else {
                                $cardBorder = 'border-gray-200 hover:border-indigo-300';
                                $statusDot = 'bg-emerald-500';
                                $statusLabel = 'Libre';
                                $statusBg = 'bg-emerald-50 text-emerald-700';
                            }
                        @endphp

                        <button wire:click="showSchedule({{ $classroom->id }})" 
                            class="relative flex flex-col bg-white rounded-2xl border {{ $cardBorder }} shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 text-left group h-full">
                            
                            <!-- Header Tarjeta -->
                            <div class="flex justify-between items-start w-full mb-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg group-hover:text-indigo-600 transition-colors">{{ $classroom->name }}</h4>
                                    <span class="text-xs text-gray-500 font-medium">{{ $classroom->type }}</span>
                                </div>
                                <div class="px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 {{ $statusBg }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                    {{ $statusLabel }}
                                </div>
                            </div>

                            <!-- Info Técnica -->
                            <div class="space-y-2 mb-6 flex-1">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span class="font-medium">{{ $classroom->capacity }}</span> <span class="text-gray-400 ml-1">Capacidad</span>
                                </div>
                                @if($classroom->pc_count > 0)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        <span class="font-medium">{{ $classroom->pc_count }}</span> <span class="text-gray-400 ml-1">Computadoras</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Footer Tarjeta -->
                            <div class="w-full pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-400">Ver calendario</span>
                                <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL REDISEÑADO: AGENDA SEMANAL --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="7xl">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl flex flex-col h-[90vh]">
            @if($selectedClassroom)
                
                {{-- 1. ENCABEZADO DEL MODAL (Panel Informativo) --}}
                <div class="bg-white border-b border-gray-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shrink-0 z-30 shadow-sm">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200">
                            @if($selectedClassroom->type == 'Laboratorio')
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            @else
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $selectedClassroom->name }}</h2>
                            <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ $selectedClassroom->building->name }}
                                </span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span>Capacidad: <strong class="text-gray-700">{{ $selectedClassroom->capacity }}</strong></span>
                                @if($selectedClassroom->pc_count > 0)
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span>PCs: <strong class="text-gray-700">{{ $selectedClassroom->pc_count }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
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

                {{-- 2. CUERPO DEL CALENDARIO (Grid Semanal) --}}
                <div class="flex-1 overflow-auto bg-gray-50 relative custom-scrollbar flex flex-col">
                    
                    {{-- Cabecera de Días --}}
                    <div class="grid grid-cols-8 border-b border-gray-200 min-w-[900px] sticky top-0 z-20 shadow-sm">
                        <div class="col-span-1 bg-gray-50/95 backdrop-blur border-r border-gray-200 py-3 px-2 flex items-center justify-center">
                            <span class="text-xs font-bold text-gray-400 uppercase">Hora</span>
                        </div>
                        @foreach($daysOfWeek as $day)
                            <div class="col-span-1 bg-white py-3 px-2 text-center border-r border-gray-100 last:border-r-0">
                                <span class="block text-xs font-bold text-gray-400 uppercase mb-0.5">{{ substr($day, 0, 3) }}</span>
                                <span class="block text-sm font-bold text-gray-900">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grilla de Horarios --}}
                    <div class="relative min-w-[900px] flex-1">
                        <!-- Filas de Fondo -->
                        @foreach($timeSlots as $time)
                            <div class="grid grid-cols-8 h-20 border-b border-gray-100 group">
                                <!-- Columna Hora -->
                                <div class="col-span-1 bg-gray-50 border-r border-gray-200 text-xs text-gray-400 font-mono flex justify-center pt-2 relative">
                                    <span class="-mt-3 bg-gray-50 px-1">{{ $time }}</span>
                                    <!-- Línea indicadora de hora -->
                                    <div class="absolute top-0 right-0 w-2 h-[1px] bg-gray-300"></div>
                                </div>
                                <!-- Columnas Días (Celdas Vacías) -->
                                @foreach($daysOfWeek as $day)
                                    <div class="col-span-1 border-r border-gray-100/50 bg-white group-hover:bg-gray-50/30 transition-colors"></div>
                                @endforeach
                            </div>
                        @endforeach

                        <!-- CAPA DE EVENTOS (Superpuesta) -->
                        <div class="absolute inset-0 grid grid-cols-8 pointer-events-none">
                            <div class="col-span-1"></div> <!-- Espacio Columna Hora -->
                            
                            @foreach($daysOfWeek as $day)
                                <div class="col-span-1 relative h-full border-r border-transparent">
                                    @foreach($calendarGrid as $timeKey => $dayData)
                                        @if(isset($dayData[$day]))
                                            @php 
                                                $slot = $dayData[$day]; 
                                                // Cálculo de posición y altura
                                                $startHour = (int) substr($slot['start_real'], 0, 2);
                                                $startMin = (int) substr($slot['start_real'], 3, 2);
                                                $gridStartHour = 7; // Hora inicio configurada en backend
                                                
                                                // 80px es la altura de h-20
                                                $topPosition = (($startHour - $gridStartHour) * 80) + (($startMin / 60) * 80);
                                                
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

                                                <div class="mt-1">
                                                    <div class="flex items-center gap-1 text-[10px] opacity-90 truncate mb-1">
                                                        <svg class="w-3 h-3 opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        <span class="truncate">{{ Str::limit($slot['teacher'], 18) }}</span>
                                                    </div>
                                                    
                                                    <div class="inline-block px-1.5 py-0.5 rounded-md bg-white/40 text-[9px] font-mono font-bold tracking-tight">
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

                {{-- 3. FOOTER DEL MODAL --}}
                <div class="bg-gray-50 border-t border-gray-200 p-4 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Curso Regular
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-purple-100 border border-purple-200"></span> Laboratorio
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