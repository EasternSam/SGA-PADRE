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
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">{{ $building->name }}</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($building->classrooms as $classroom)
                            @php
                                $isOccupied = $classroom->isOccupiedNow();
                                $statusColor = $isOccupied ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200';
                                $textColor = $isOccupied ? 'text-red-700' : 'text-green-700';
                                $statusText = $isOccupied ? 'Ocupada' : 'Disponible';
                            @endphp

                            <button wire:click="showSchedule({{ $classroom->id }})" 
                                class="text-left group relative p-4 border rounded-xl hover:shadow-md transition-all {{ $statusColor }}">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-800 text-lg">{{ $classroom->name }}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $isOccupied ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <div class="text-sm text-gray-600 space-y-1">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <span>Cap: {{ $classroom->capacity }}</span>
                                    </div>
                                    @if($classroom->pc_count > 0)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            <span>PCs: {{ $classroom->pc_count }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mt-3 text-xs text-gray-500 border-t border-gray-200/50 pt-2">
                                    Clic para ver horario
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL DE HORARIO DETALLADO --}}
    <x-modal name="schedule-view-modal" :show="$showingScheduleModal" maxWidth="2xl">
        <div class="p-6">
            @if($selectedClassroom)
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $selectedClassroom->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $selectedClassroom->building->name }} - {{ $selectedClassroom->type }}</p>
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <p>Equipamiento:</p>
                        <p class="font-medium">{{ $selectedClassroom->equipment ?: 'Básico' }}</p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-800 mb-3">Cursos Programados (Vigentes)</h3>
                
                <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                    @forelse($weekSchedules as $schedule)
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 flex flex-col sm:flex-row justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-indigo-700">{{ $schedule->module->course->name ?? 'Curso' }}</h4>
                                <p class="text-sm text-gray-700">{{ $schedule->module->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Prof: {{ $schedule->teacher->name ?? 'N/A' }} | Sección: {{ $schedule->section_name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="inline-flex flex-wrap justify-end gap-1 mb-1">
                                    @foreach($schedule->days_of_week as $day)
                                        <span class="px-1.5 py-0.5 rounded bg-white border text-[10px] font-bold text-gray-600">{{ substr($day, 0, 3) }}</span>
                                    @endforeach
                                </div>
                                <p class="text-sm font-mono font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </p>
                                <p class="text-[10px] text-gray-400">
                                    Hasta: {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 italic">
                            No hay cursos programados actualmente en este espacio.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button wire:click="closeModal">
                        Cerrar
                    </x-secondary-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>