<div class="py-12">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Aulas y Espacios') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        {{-- CONTROLES SUPERIORES --}}
        <div class="flex justify-between items-center">
            <div>
                {{-- Espacio para filtros futuros --}}
            </div>
            <div class="flex gap-2">
                <x-secondary-button wire:click="openBuildingModal" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Nuevo Edificio
                </x-secondary-button>

                <x-primary-button wire:click="openClassroomModal" class="bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Registrar Nueva Aula
                </x-primary-button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        {{-- VISTA PRINCIPAL DE TARJETAS --}}
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
                                // Para mostrar qué lo ocupa
                                $occupantLabel = $classroom->getCurrentOccupantLabel(); 
                                $hasSchedules = $classroom->schedules->isNotEmpty() || $classroom->reservations->isNotEmpty();
                                
                                // Verificar si tiene TV
                                $hasTV = false;
                                if($classroom->equipment) {
                                    $eq = json_decode($classroom->equipment, true);
                                    if(is_array($eq) && in_array('TV', $eq)) {
                                        $hasTV = true;
                                    }
                                }

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

                            <div class="relative group border rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col h-full {{ $statusColor }}">
                                
                                {{-- BOTÓN ELIMINAR (FLOTANTE ARRIBA DERECHA) --}}
                                <button wire:click="deleteClassroom({{ $classroom->id }})" 
                                        wire:confirm="¿Estás seguro de eliminar el aula {{ $classroom->name }}? Esta acción no se puede deshacer."
                                        class="absolute top-2 right-2 z-20 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1 bg-white rounded-full shadow-sm"
                                        title="Eliminar Aula">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>

                                {{-- AREA CLICKEABLE PRINCIPAL (Para ver horario) --}}
                                <div class="flex-1 p-4 cursor-pointer" wire:click="showSchedule({{ $classroom->id }})">
                                    <div class="flex justify-between items-start mb-3">
                                        <h4 class="font-bold text-gray-800 text-lg group-hover:text-indigo-600 transition-colors truncate pr-6" title="{{ $classroom->name }}">
                                            {{ $classroom->name }}
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold shrink-0 {{ $statusBadge }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>

                                    @if($isOccupied)
                                        <div class="text-xs text-red-600 font-bold mb-2 truncate" title="{{ $occupantLabel }}">
                                            {{ $occupantLabel }}
                                        </div>
                                    @endif

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
                                        @if($hasTV)
                                            <div class="flex items-center gap-2" title="Equipamiento: TV/Proyector">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                <span>TV/Proyector</span>
                                            </div>
                                        @endif
                                        @if($classroom->type == 'Laboratorio')
                                            <div class="flex items-center gap-2" title="Tipo">
                                                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                                <span>Laboratorio</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- FOOTER DE LA TARJETA CON BOTONES --}}
                                <div class="px-4 pb-[11px] pt-[8px] mt-auto border-t border-gray-100 bg-gray-50/50 rounded-b-xl flex justify-between items-center" style="padding-top: 8px; padding-bottom: 11px;">
                                    <button wire:click="showSchedule({{ $classroom->id }})" class="text-indigo-600 text-xs font-bold flex items-center hover:underline">
                                        Ver Agenda <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>

                                    <button wire:click.stop="openReservationModal({{ $classroom->id }})" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition-colors flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Reservar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL DE HORARIO DETALLADO --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="3xl">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
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
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                         <button wire:click.stop="openReservationModal({{ $selectedClassroom->id }})" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Nueva Reserva
                        </button>
                        <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50 p-6 space-y-6">
                    @if (session()->has('message'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200 shadow-sm">
                            {{ session('message') }}
                        </div>
                    @endif
                    
                    {{-- SECCIÓN A: RESERVAS PUNTUALES --}}
                    @if($upcomingReservations && $upcomingReservations->isNotEmpty())
                        <div class="bg-amber-50 rounded-xl border border-amber-200 p-5 mb-6">
                            <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Reservas Especiales Próximas
                            </h3>
                            <div class="space-y-3">
                                @foreach($upcomingReservations as $reservation)
                                    <div class="bg-white rounded-lg border border-amber-100 shadow-sm p-3 flex justify-between items-center">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900">{{ $reservation->title }}</span>
                                                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                                                    {{ \Carbon\Carbon::parse($reservation->reserved_date)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}
                                                @if($reservation->description)
                                                    <span class="mx-1">•</span> {{ $reservation->description }}
                                                @endif
                                            </div>
                                        </div>
                                        <button wire:click="deleteReservation({{ $reservation->id }})" wire:confirm="¿Eliminar esta reserva? El horario habitual será restaurado." class="text-gray-400 hover:text-red-500 p-1">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-amber-700 mt-3 italic">* Estas reservas tienen prioridad sobre las clases regulares.</p>
                        </div>
                    @endif

                    {{-- SECCIÓN B: HORARIO RECURRENTE --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Horario Académico Recurrente</h3>
                        
                        @php
                            $schedules = is_array($weekSchedules) ? collect($weekSchedules) : ($weekSchedules ?? collect());
                            $hasSchedules = $schedules->isNotEmpty();
                        @endphp

                        @if($hasSchedules)
                            @php
                                // Agrupamos por días de la semana
                                $groupedByDay = [];
                                $dayOrder = array_flip(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
                                
                                foreach($schedules as $schedule) {
                                    $days = data_get($schedule, 'days_of_week');
                                    if(is_array($days)) {
                                        foreach($days as $day) {
                                            $dayKey = ucfirst(strtolower($day));
                                            
                                            $startTime = data_get($schedule, 'start_time');
                                            $endTime = data_get($schedule, 'end_time');
                                            $startFormatted = $startTime ? \Carbon\Carbon::parse($startTime)->format('g:i A') : 'N/A';
                                            $endFormatted = $endTime ? \Carbon\Carbon::parse($endTime)->format('g:i A') : 'N/A';
                                            
                                            $groupedByDay[$dayKey][] = [
                                                'id' => data_get($schedule, 'id'),
                                                'course' => data_get($schedule, 'module.course.name') ?? 'Curso',
                                                'module' => data_get($schedule, 'module.name'),
                                                'section' => data_get($schedule, 'section_name'),
                                                'teacher' => data_get($schedule, 'teacher.name') ?? 'Sin profesor',
                                                'start_real' => $startFormatted,
                                                'end_real' => $endFormatted,
                                                'start_timestamp' => $startTime
                                            ];
                                        }
                                    }
                                }

                                uksort($groupedByDay, function($a, $b) use ($dayOrder) {
                                    $aIndex = $dayOrder[$a] ?? 99;
                                    $bIndex = $dayOrder[$b] ?? 99;
                                    return $aIndex - $bIndex;
                                });

                                foreach($groupedByDay as $day => &$items) {
                                    usort($items, function($a, $b) {
                                        return strcmp($a['start_timestamp'], $b['start_timestamp']);
                                    });
                                }
                            @endphp

                            @foreach($groupedByDay as $dayName => $slots)
                                <div class="mb-6 last:mb-0">
                                    <h3 class="text-sm font-bold text-indigo-600 mb-2 ml-1">{{ $dayName }}</h3>
                                    <div class="space-y-3">
                                        @foreach($slots as $slot)
                                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col sm:flex-row gap-4 relative overflow-hidden group">
                                                <div class="w-1 bg-indigo-500 absolute left-0 top-0 bottom-0"></div>
                                                
                                                <div class="flex-shrink-0 min-w-[90px] flex flex-col justify-center border-r border-gray-100 pr-4 sm:mr-0 mr-4">
                                                    <span class="text-base font-bold text-gray-900 font-mono">{{ $slot['start_real'] }}</span>
                                                    <span class="text-xs text-gray-400">a {{ $slot['end_real'] }}</span>
                                                </div>

                                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                                    <div class="flex justify-between items-start">
                                                        <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $slot['course'] }}">{{ $slot['course'] }}</h4>
                                                        <button wire:click="detachClassroom({{ $slot['id'] }})" wire:confirm="¿Seguro que deseas liberar esta aula de esta sección?" class="text-gray-300 hover:text-red-600 p-1" title="Desvincular Aula">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        @if(!empty($slot['section']))
                                                            <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">Sec: {{ $slot['section'] }}</span>
                                                        @endif
                                                        <span class="text-xs text-gray-500 truncate">{{ $slot['teacher'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex flex-col items-center justify-center h-32 text-center bg-white rounded-xl border border-dashed border-gray-300">
                                <span class="text-gray-400 text-sm">No hay clases recurrentes asignadas.</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 3. FOOTER DEL MODAL --}}
                <div class="bg-white border-t border-gray-200 p-4 flex justify-end shrink-0">
                    <x-secondary-button wire:click="closeModal">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>

    {{-- MODAL DE CREAR RESERVA (NUEVO) --}}
    <x-modal name="reservation-modal" :show="$showingReservationModal" maxWidth="md">
        <form wire:submit.prevent="createReservation" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                Nueva Reserva de Aula
            </h2>
            
            <p class="text-sm text-gray-500 mb-6">
                Esta reserva ocupará el aula en la fecha y hora seleccionada. Si hay una clase programada, la reserva tendrá prioridad.
            </p>

            <div class="space-y-4">
                <div>
                    <x-input-label for="reservation_title" :value="__('Título de la Actividad')" />
                    <x-text-input id="reservation_title" type="text" class="mt-1 block w-full" wire:model="reservation_title" placeholder="Ej: Conferencia, Examen, Mantenimiento" />
                    <x-input-error :messages="$errors->get('reservation_title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="reservation_date" :value="__('Fecha')" />
                    <x-text-input id="reservation_date" type="date" class="mt-1 block w-full" wire:model="reservation_date" />
                    <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="reservation_start_time" :value="__('Inicio')" />
                        <x-text-input id="reservation_start_time" type="time" class="mt-1 block w-full" wire:model="reservation_start_time" />
                        <x-input-error :messages="$errors->get('reservation_start_time')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="reservation_end_time" :value="__('Fin')" />
                        <x-text-input id="reservation_end_time" type="time" class="mt-1 block w-full" wire:model="reservation_end_time" />
                        <x-input-error :messages="$errors->get('reservation_end_time')" class="mt-2" />
                    </div>
                </div>
                
                <div>
                    <x-input-label for="reservation_description" :value="__('Descripción (Opcional)')" />
                    <textarea id="reservation_description" wire:model="reservation_description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" rows="3"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button wire:click="closeModal">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Crear Reserva
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL DE CREAR AULA (NUEVO) --}}
    <x-modal name="classroom-modal" :show="$showingClassroomModal" maxWidth="md">
        <form wire:submit.prevent="storeClassroom" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                Registrar Nueva Aula
            </h2>
            
            <div class="space-y-4">
                <div>
                    <x-input-label for="classroom_name" :value="__('Nombre del Aula')" />
                    <x-text-input id="classroom_name" type="text" class="mt-1 block w-full" wire:model="classroom_name" placeholder="Ej: Laboratorio 101" />
                    <x-input-error :messages="$errors->get('classroom_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="classroom_building_id" :value="__('Edificio')" />
                    <select id="classroom_building_id" wire:model="classroom_building_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('classroom_building_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="classroom_capacity" :value="__('Capacidad')" />
                        <x-text-input id="classroom_capacity" type="number" class="mt-1 block w-full" wire:model="classroom_capacity" min="1" />
                        <x-input-error :messages="$errors->get('classroom_capacity')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="classroom_pc_count" :value="__('Cantidad PCs')" />
                        <x-text-input id="classroom_pc_count" type="number" class="mt-1 block w-full" wire:model="classroom_pc_count" min="0" />
                        <x-input-error :messages="$errors->get('classroom_pc_count')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="classroom_type" :value="__('Tipo de Espacio')" />
                    <select id="classroom_type" wire:model="classroom_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                        <option value="Aula">Aula Normal</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Auditorio">Auditorio</option>
                    </select>
                    <x-input-error :messages="$errors->get('classroom_type')" class="mt-2" />
                </div>

                <div class="block mt-4">
                    <label for="classroom_has_tv" class="inline-flex items-center cursor-pointer">
                        <input id="classroom_has_tv" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" wire:model="classroom_has_tv">
                        <span class="ms-2 text-sm text-gray-700">{{ __('Tiene TV / Proyector') }}</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button wire:click="closeModal">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Guardar Aula
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL DE CREAR EDIFICIO (NUEVO) --}}
    <x-modal name="building-modal" :show="$showingBuildingModal" maxWidth="sm">
        <form wire:submit.prevent="storeBuilding" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                Registrar Nuevo Edificio
            </h2>
            
            <div class="space-y-4">
                <div>
                    <x-input-label for="new_building_name" :value="__('Nombre del Edificio')" />
                    <x-text-input id="new_building_name" type="text" class="mt-1 block w-full" wire:model="new_building_name" placeholder="Ej: Edificio C" />
                    <x-input-error :messages="$errors->get('new_building_name')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button wire:click="closeModal">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Guardar Edificio
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>