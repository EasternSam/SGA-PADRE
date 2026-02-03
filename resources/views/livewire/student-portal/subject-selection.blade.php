<div class="py-6 space-y-6">
    <!-- Encabezado y Resumen -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Selección de Materias</h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Carrera: <span class="font-semibold text-indigo-600">{{ $career->name ?? 'No definida' }}</span>
                    </p>
                </div>
                
                <!-- Resumen Flotante (Sticky en Desktop) -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 flex gap-6 shadow-inner">
                    <div class="text-center">
                        <span class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">Materias</span>
                        <span class="text-2xl font-bold text-indigo-600">{{ count($selectedSchedules) }}</span>
                    </div>
                    <div class="text-center border-l border-gray-300 dark:border-gray-600 pl-6">
                        <span class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">Créditos</span>
                        <span class="text-2xl font-bold text-green-600">{{ $totalCredits }}</span>
                    </div>
                    @if($totalCost > 0)
                    <div class="text-center border-l border-gray-300 dark:border-gray-600 pl-6">
                        <span class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-bold">Total (Est.)</span>
                        <span class="text-2xl font-bold text-gray-800 dark:text-gray-200">${{ number_format($totalCost, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Mensajes de Estado -->
            @if ($errorMessage)
                <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ $errorMessage }}</span>
                </div>
            @endif

            @if ($successMessage)
                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
                    <strong class="font-bold">Éxito!</strong>
                    <span class="block sm:inline">{{ $successMessage }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Lista de Materias por Periodo -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @if(empty($groupedModules))
            <div class="text-center py-10 bg-white rounded shadow">
                <p class="text-gray-500">No se encontró oferta académica disponible para tu perfil.</p>
            </div>
        @else
            @foreach($groupedModules as $period => $modules)
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Cuatrimestre {{ $period }}
                        </h3>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($modules as $module)
                            <div class="p-6 transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <div class="flex flex-col md:flex-row justify-between md:items-start gap-4">
                                    
                                    <!-- Información de la Materia -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-mono bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded text-gray-700 dark:text-gray-200">
                                                {{ $module['code'] }}
                                            </span>
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ $module['name'] }}
                                            </h4>
                                            
                                            <!-- Badges de Estado -->
                                            @if($module['status'] === 'aprobada')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path> Aprobada
                                                </span>
                                            @elseif($module['status'] === 'bloqueada')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Faltan: {{ implode(', ', $module['missing_prereqs']) }}">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path> Bloqueada
                                                </span>
                                            @endif
                                            
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $module['credits'] }} Créditos
                                            </span>
                                        </div>

                                        @if($module['status'] === 'bloqueada')
                                            <p class="text-sm text-red-500 mt-2">
                                                <span class="font-semibold">Requisitos pendientes:</span> {{ implode(', ', $module['missing_prereqs']) }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Selección de Secciones -->
                                    @if($module['status'] === 'disponible')
                                        <div class="w-full md:w-1/2 lg:w-1/3 space-y-2">
                                            @if($module['schedules']->isEmpty())
                                                <div class="text-sm text-orange-500 italic bg-orange-50 p-2 rounded">
                                                    No hay secciones abiertas actualmente.
                                                </div>
                                            @else
                                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Secciones Disponibles</p>
                                                
                                                @foreach($module['schedules'] as $schedule)
                                                    @php
                                                        $isSelected = isset($selectedSchedules[$module['id']]) && $selectedSchedules[$module['id']] == $schedule->id;
                                                        $isFull = $schedule->isFull();
                                                        // Formatear días
                                                        $days = is_array($schedule->days_of_week) ? implode(', ', $schedule->days_of_week) : $schedule->days_of_week;
                                                    @endphp

                                                    <button 
                                                        wire:click="toggleSection({{ $module['id'] }}, {{ $schedule->id }})"
                                                        @if($isFull && !$isSelected) disabled @endif
                                                        class="w-full text-left p-3 rounded-md border text-sm transition relative
                                                            {{ $isSelected 
                                                                ? 'bg-indigo-50 border-indigo-500 ring-1 ring-indigo-500' 
                                                                : ($isFull ? 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed' : 'bg-white border-gray-300 hover:border-indigo-300') 
                                                            }}
                                                        ">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold {{ $isSelected ? 'text-indigo-700' : 'text-gray-700' }}">
                                                                Sección {{ $schedule->section_name }}
                                                            </span>
                                                            @if($isSelected)
                                                                <span class="text-indigo-600">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                </span>
                                                            @elseif($isFull)
                                                                <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded">LLENO</span>
                                                            @else
                                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">
                                                                    {{ $schedule->available_spots }} cupos
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-1 flex justify-between text-xs text-gray-500">
                                                            <span>{{ $days }}</span>
                                                            <span>{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</span>
                                                        </div>
                                                        <div class="mt-1 text-xs text-gray-400">
                                                            Prof: {{ $schedule->teacher->name ?? 'Por asignar' }} | Aula: {{ $schedule->classroom->name ?? 'TBA' }}
                                                        </div>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Barra Inferior Fija para Confirmación -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg p-4 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="hidden sm:block">
                <p class="text-sm text-gray-500">Revisa tu selección antes de confirmar.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right mr-4">
                    <span class="block text-sm text-gray-500">Total a pagar estimado</span>
                    <span class="text-xl font-bold text-indigo-600">${{ number_format($totalCost, 2) }}</span>
                </div>
                <button 
                    wire:click="confirmSelection"
                    wire:loading.attr="disabled"
                    @if(empty($selectedSchedules)) disabled @endif
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-bold rounded-lg shadow transition transform active:scale-95 flex items-center">
                    <span wire:loading.remove wire:target="confirmSelection">Confirmar Selección</span>
                    <span wire:loading wire:target="confirmSelection">Procesando...</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Espaciador para no tapar contenido con la barra fija -->
    <div class="h-24"></div>
</div>