<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Principal -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Selección de Materias</h1>
            <p class="mt-2 text-gray-600">
                Planifica tu próximo ciclo académico. Selecciona las materias disponibles para tu carrera:
                <span class="font-semibold text-indigo-600">{{ $career->name ?? 'Carrera General' }}</span>
            </p>
        </div>

        <!-- Mensajes de Estado -->
        <div class="space-y-4 mb-6">
            @if ($errorMessage)
                <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-md shadow-sm animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ $errorMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($successMessage)
                <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-md shadow-sm animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ $successMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($debugMessage)
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded-r-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold">Diagnóstico del Sistema:</p>
                            <p class="text-sm">{{ $debugMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Lista de Materias por Cuatrimestre -->
        @if(empty($groupedModules))
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Sin oferta académica visible</h3>
                <p class="mt-1 text-gray-500 max-w-sm mx-auto">No encontramos materias disponibles para seleccionar en este momento para tu perfil.</p>
            </div>
        @else
            <div class="space-y-8 pb-32">
                @foreach($groupedModules as $period => $modules)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Cabecera de Cuatrimestre -->
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="bg-indigo-600 text-white text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                                    Periodo {{ $period }}
                                </span>
                                <h3 class="text-lg font-semibold text-gray-800">Materias del Nivel</h3>
                            </div>
                            <span class="text-sm text-gray-500">{{ count($modules) }} asignaturas</span>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach($modules as $module)
                                <div class="p-6 transition hover:bg-gray-50 {{ $module['status'] === 'aprobada' ? 'bg-green-50/30' : '' }}">
                                    <div class="flex flex-col lg:flex-row gap-6">
                                        
                                        <!-- Columna Izquierda: Info de la Materia -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                                            {{ $module['code'] }}
                                                        </span>
                                                        @if($module['status'] === 'aprobada')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path> Aprobada
                                                            </span>
                                                        @elseif($module['status'] === 'bloqueada')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path> Prerrequisito Pendiente
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <h4 class="text-lg font-bold text-gray-900 leading-tight">
                                                        {{ $module['name'] }}
                                                    </h4>
                                                    
                                                    <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                                                        <span class="flex items-center gap-1" title="Créditos Académicos">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                                            {{ $module['credits'] }} Créditos
                                                        </span>
                                                    </div>

                                                    @if($module['status'] === 'bloqueada')
                                                        <div class="mt-3 text-sm bg-red-50 text-red-600 p-2 rounded border border-red-100 inline-block">
                                                            <span class="font-semibold">Debes aprobar primero:</span> 
                                                            {{ implode(', ', $module['missing_prereqs']) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna Derecha: Secciones -->
                                        @if($module['status'] === 'disponible')
                                            <div class="lg:w-[450px] w-full">
                                                @if($module['schedules']->isEmpty())
                                                    <div class="h-full flex items-center justify-center p-4 bg-gray-50 rounded border border-dashed border-gray-300 text-gray-400 text-sm italic">
                                                        No hay secciones abiertas
                                                    </div>
                                                @else
                                                    <div class="space-y-2">
                                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 pl-1">Elige una sección:</p>
                                                        
                                                        @foreach($module['schedules'] as $schedule)
                                                            @php
                                                                $isSelected = isset($selectedSchedules[$module['id']]) && $selectedSchedules[$module['id']] == $schedule->id;
                                                                $isFull = $schedule->isFull();
                                                                $days = is_array($schedule->days_of_week) ? implode(', ', $schedule->days_of_week) : $schedule->days_of_week;
                                                            @endphp

                                                            <button 
                                                                wire:click="toggleSection({{ $module['id'] }}, {{ $schedule->id }})"
                                                                @if($isFull && !$isSelected) disabled @endif
                                                                class="group w-full relative flex items-center justify-between p-3 rounded-lg border text-left transition-all duration-200
                                                                    {{ $isSelected 
                                                                        ? 'bg-indigo-50 border-indigo-500 ring-1 ring-indigo-500 shadow-sm z-10' 
                                                                        : ($isFull 
                                                                            ? 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed' 
                                                                            : 'bg-white border-gray-200 hover:border-indigo-300 hover:shadow-sm') 
                                                                    }}
                                                                ">
                                                                
                                                                <!-- Indicador de Selección -->
                                                                @if($isSelected)
                                                                    <div class="absolute -left-1 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-indigo-500 rounded-r"></div>
                                                                @endif

                                                                <div class="flex-1">
                                                                    <div class="flex items-center justify-between mb-1">
                                                                        <span class="font-bold text-sm {{ $isSelected ? 'text-indigo-900' : 'text-gray-800' }}">
                                                                            Sección {{ $schedule->section_name }}
                                                                        </span>
                                                                        
                                                                        <!-- Badges de Disponibilidad -->
                                                                        @if($isFull && !$isSelected)
                                                                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">Agotada</span>
                                                                        @elseif($isSelected)
                                                                            <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase flex items-center gap-1">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                                Seleccionada
                                                                            </span>
                                                                        @else
                                                                            <span class="bg-green-50 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-green-100">
                                                                                {{ $schedule->available_spots }} cupos
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    <!-- Detalles de Horario y Profesor -->
                                                                    <div class="text-xs text-gray-500 space-y-0.5">
                                                                        <div class="flex items-center gap-1">
                                                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                            <span class="font-medium text-gray-700">{{ $days }}</span>
                                                                            <span>•</span>
                                                                            <span>{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</span>
                                                                        </div>
                                                                        <div class="flex items-center gap-1">
                                                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                                            <span>{{ Str::limit($schedule->teacher->name ?? 'Profesor por asignar', 25) }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Barra de Confirmación Flotante -->
    <div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] transition-transform duration-300 transform {{ empty($selectedSchedules) ? 'translate-y-full' : 'translate-y-0' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <div class="flex items-center gap-6">
                    <div class="hidden sm:block">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wide">Resumen de Selección</p>
                        <p class="text-sm text-gray-600">Revisa tus horarios antes de confirmar.</p>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="block text-xs text-gray-500 uppercase">Materias</span>
                            <span class="block text-xl font-bold text-gray-900 leading-none">{{ count($selectedSchedules) }}</span>
                        </div>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="block text-xs text-gray-500 uppercase">Créditos</span>
                            <span class="block text-xl font-bold text-indigo-600 leading-none">{{ $totalCredits }}</span>
                        </div>
                        @if($totalCost > 0)
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="block text-xs text-gray-500 uppercase">Total</span>
                            <span class="block text-xl font-bold text-gray-900 leading-none">${{ number_format($totalCost, 0) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <button 
                    wire:click="confirmSelection"
                    wire:loading.attr="disabled"
                    class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                    <svg wire:loading.remove wire:target="confirmSelection" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg wire:loading wire:target="confirmSelection" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Confirmar Inscripción</span>
                </button>
            </div>
        </div>
    </div>
</div>