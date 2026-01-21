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
                                $statusColor = $isOccupied ? 'bg-red-50 border-red-200 ring-1 ring-red-200' : 'bg-white border-gray-200 hover:border-indigo-300';
                                $statusBadge = $isOccupied ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
                                $statusText = $isOccupied ? 'Ocupada' : 'Disponible';
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

    {{-- MODAL DE HORARIO DETALLADO --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="5xl">
        <div class="p-6">
            @if($selectedClassroom)
                <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            {{ $selectedClassroom->name }}
                            @if($selectedClassroom->type == 'Laboratorio')
                                <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full uppercase tracking-wide">Lab</span>
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $selectedClassroom->building->name }} &bull; Cap: {{ $selectedClassroom->capacity }} &bull; {{ $selectedClassroom->equipment }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- CALENDARIO VISUAL (GRID) --}}
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        {{-- Encabezados Días --}}
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Hora</div>
                            @foreach($daysOfWeek as $day)
                                <div class="text-center text-sm font-bold text-gray-700 bg-gray-50 py-2 rounded">{{ $day }}</div>
                            @endforeach
                        </div>

                        {{-- Filas de Horas --}}
                        <div class="space-y-1">
                            @foreach($timeSlots as $time)
                                <div class="grid grid-cols-7 gap-1 h-12"> <!-- Altura fija por bloque de hora -->
                                    {{-- Columna Hora --}}
                                    <div class="text-center text-xs text-gray-400 py-1 -mt-2 transform translate-y-1/2">
                                        {{ $time }}
                                    </div>

                                    @foreach($daysOfWeek as $day)
                                        <div class="relative border border-gray-100 rounded bg-white hover:bg-gray-50 transition-colors">
                                            @if(isset($calendarGrid[$time][$day]))
                                                @php $slot = $calendarGrid[$time][$day]; @endphp
                                                {{-- Tarjeta de curso (usa absolute y z-index para superponerse si dura mas de 1 hora) --}}
                                                <div class="absolute inset-0 m-0.5 rounded p-1.5 shadow-sm text-xs leading-tight flex flex-col justify-center {{ $slot['color'] }}"
                                                     style="height: calc(100% * {{ $slot['rowspan'] }} - 4px); z-index: 10;">
                                                    <span class="font-bold truncate">{{ $slot['course'] }}</span>
                                                    <span class="opacity-75 truncate text-[10px]">{{ $slot['teacher'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button wire:click="closeModal">
                        Cerrar Calendario
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>