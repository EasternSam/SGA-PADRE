<div class="min-h-screen bg-gray-50/50 pb-12">
    
    <x-action-message on="notify" />

    {{-- 1. ENCABEZADO FIJO (Resumen de Carrera) --}}
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Título y Breadcrumb --}}
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('admin.careers.index') }}" class="hover:text-indigo-600 hover:underline" wire:navigate>
                            <svg class="w-3 h-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Volver a Carreras
                        </a>
                        <span>/</span>
                        <span class="font-medium text-gray-800">Pensum Académico</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                        {{ $career->name }}
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold tracking-wide uppercase border border-indigo-200">
                            {{ $career->code }}
                        </span>
                    </h1>
                </div>

                {{-- Stats Rápidos --}}
                <div class="flex items-center gap-6 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200">
                    <div class="text-center">
                        <p class="text-xs text-gray-500 uppercase font-bold">Créditos</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $career->total_credits }}</p>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500 uppercase font-bold">Duración</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $career->duration_periods }} <span class="text-xs font-normal text-gray-500">Períodos</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. CONTENIDO DEL PENSUM (Lista por Periodos) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">
        
        {{-- Acceso a Computed Property --}}
        @if($this->modulesByPeriod->isEmpty())
            <div class="text-center py-20 bg-white rounded-2xl border-2 border-gray-200 border-dashed">
                <div class="mx-auto h-16 w-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Pensum Vacío</h3>
                <p class="mt-1 text-gray-500 max-w-sm mx-auto">Esta carrera aún no tiene materias configuradas. Comienza agregando el primer cuatrimestre.</p>
                <div class="mt-6">
                    <button wire:click="openCreateModule(1)" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Agregar Materia al Cuatrimestre 1
                    </button>
                </div>
            </div>
        @else
            {{-- Loop sobre Computed Property --}}
            @foreach($this->modulesByPeriod as $period => $modules)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    
                    {{-- Header del Cuatrimestre --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-900 text-white text-sm font-bold shadow-md">
                                {{ $period }}
                            </span>
                            <h3 class="text-lg font-bold text-gray-800">Cuatrimestre {{ $period }}</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $modules->sum('credits') }} Créditos
                            </span>
                            <button wire:click="openCreateModule({{ $period }})" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Agregar Materia
                            </button>
                        </div>
                    </div>

                    {{-- Lista de Materias --}}
                    <div class="divide-y divide-gray-100">
                        @foreach($modules as $module)
                            <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors group">
                                
                                {{-- Info Principal --}}
                                <div class="flex items-start gap-5">
                                    <div class="shrink-0 text-center w-20">
                                        <span class="block font-mono text-sm font-bold text-gray-900 bg-gray-100 rounded px-2 py-1">{{ $module->code }}</span>
                                        @if($module->is_elective)
                                            <span class="block mt-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-1 rounded border border-amber-100">ELECTIVA</span>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $module->name }}</h4>
                                        
                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs">
                                            {{-- Créditos --}}
                                            <span class="flex items-center gap-1.5 text-gray-600 bg-white border border-gray-200 px-2 py-0.5 rounded-md">
                                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                {{ $module->credits }} Créditos
                                            </span>

                                            {{-- Prerrequisitos --}}
                                            @if($module->prerequisites->count() > 0)
                                                <div class="flex items-center gap-1.5 text-rose-700 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-md">
                                                    <span class="font-bold">Pre-req:</span>
                                                    @foreach($module->prerequisites as $pre)
                                                        <span>{{ $pre->code }}</span>{{ !$loop->last ? ',' : '' }}
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 flex items-center gap-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                                                    Sin requisitos
                                                </span>
                                            @endif

                                            {{-- Contador de Horarios/Secciones (Aquí se ve la oferta académica) --}}
                                            @if($module->schedules->count() > 0)
                                                <span class="flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md font-medium">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ $module->schedules->count() }} Secciones Abiertas
                                                </span>
                                            @else
                                                <span class="flex items-center gap-1.5 text-gray-400 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-md">
                                                    Sin secciones
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Botones de Acción --}}
                                <div class="mt-4 sm:mt-0 flex items-center gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                    
                                    {{-- BOTÓN PRINCIPAL: GESTIONAR HORARIOS/SECCIONES --}}
                                    <button wire:click="openScheduleModal({{ $module->id }})" class="inline-flex items-center px-3 py-1.5 border border-indigo-200 text-xs font-bold rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors shadow-sm" title="Asignar Horarios, Maestros y Aulas">
                                        <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Secciones y Horarios
                                    </button>
                                    
                                    <div class="h-6 w-px bg-gray-300 mx-1 hidden sm:block"></div>

                                    <button wire:click="editModule({{ $module->id }})" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-gray-100 rounded-lg transition-colors" title="Editar Materia">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button wire:click="deleteModule({{ $module->id }})" wire:confirm="¿Seguro que deseas eliminar esta materia? Esto podría afectar a los estudiantes inscritos." class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar Materia">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(!$loop->last)
                    <div class="flex justify-center py-4">
                        <div class="h-8 w-px border-l-2 border-dashed border-gray-300"></div>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- Botón Agregar Siguiente Periodo --}}
        @if($this->modulesByPeriod->isNotEmpty())
            <div class="flex justify-center pb-12">
                <button wire:click="openCreateModule({{ $this->modulesByPeriod->keys()->max() + 1 }})" class="group flex flex-col items-center gap-2 text-gray-400 hover:text-indigo-600 transition-colors">
                    <div class="h-12 w-12 rounded-full border-2 border-dashed border-current flex items-center justify-center group-hover:bg-indigo-50 group-hover:border-solid transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <span class="text-sm font-medium">Agregar Cuatrimestre {{ $this->modulesByPeriod->keys()->max() + 1 }}</span>
                </button>
            </div>
        @endif
    </div>

    {{-- MODAL 1: Crear/Editar Materia --}}
    <x-modal name="module-form-modal" maxWidth="2xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $modalModuleTitle }}</h2>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="saveModule" class="p-6 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-1">
                        <x-input-label for="code" value="Clave *" />
                        <x-text-input id="code" type="text" class="mt-1 w-full font-mono uppercase" wire:model="code" placeholder="MAT-101" />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    <div class="md:col-span-3">
                        <x-input-label for="name" value="Nombre Asignatura *" />
                        <x-text-input id="name" type="text" class="mt-1 w-full" wire:model="name" placeholder="Ej. Cálculo Diferencial" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="credits" value="Créditos *" />
                        <x-text-input id="credits" type="number" class="mt-1 w-full" wire:model="credits" />
                    </div>
                    <div>
                        <x-input-label for="period_number" value="Cuatrimestre *" />
                        <x-text-input id="period_number" type="number" min="1" class="mt-1 w-full" wire:model="period_number" />
                    </div>
                    <div class="flex items-center pt-8">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_elective" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Es Electiva</span>
                        </label>
                    </div>
                </div>

                <div>
                    <x-input-label for="prerequisites" value="Pre-requisitos (Materias anteriores)" />
                    <select id="prerequisites" wire:model="selectedPrerequisites" multiple class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-32">
                        @foreach($availablePrerequisites as $pre)
                            <option value="{{ $pre->id }}">[{{ $pre->code }}] {{ $pre->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Usa Ctrl (Win) o Cmd (Mac) para seleccionar varias.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button>Guardar Asignatura</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- MODAL 2: Gestión de Horarios (Secciones) --}}
    <x-modal name="schedule-management-modal" maxWidth="5xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden h-[85vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Gestión de Secciones</h2>
                    <p class="text-sm text-gray-500">Materia: <span class="text-indigo-600 font-bold">{{ $selectedModuleForSchedule?->name }}</span> ({{ $selectedModuleForSchedule?->code }})</p>
                </div>
                <button wire:click="closeScheduleModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                
                {{-- Columna Izq: Lista de Secciones --}}
                <div class="w-full md:w-5/12 border-r border-gray-200 overflow-y-auto p-4 bg-gray-50">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-3 flex justify-between items-center">
                        Secciones Activas
                        <span class="bg-gray-200 text-gray-600 px-1.5 rounded-full text-[10px]">{{ count($moduleSchedules) }}</span>
                    </h3>
                    
                    <div class="space-y-3">
                        @forelse($moduleSchedules as $schedule)
                            {{-- CAMBIO CLAVE AQUÍ: Agregar wire:click en el contenedor principal para que sea cliqueable --}}
                            <div 
                                wire:click="editSchedule({{ $schedule->id }})" 
                                class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition-all relative group cursor-pointer {{ $scheduleId === $schedule->id ? 'border-indigo-500 ring-2 ring-indigo-200 bg-indigo-50' : '' }}" 
                                wire:key="schedule-{{ $schedule->id }}">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded border border-indigo-200">
                                        {{ $schedule->section_name }}
                                    </span>
                                    <div class="flex gap-1">
                                        {{-- Botón eliminar sigue siendo funcional individualmente pero requiere stop propagation --}}
                                        <button wire:click.stop="deleteSchedule({{ $schedule->id }})" wire:confirm="¿Eliminar esta sección?" class="text-red-600 hover:bg-red-50 p-1 rounded" title="Eliminar"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                                
                                <div class="space-y-1">
                                    <p class="font-bold text-sm text-gray-900 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $schedule->day_of_week }} {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                    </p>
                                    <p class="text-xs text-gray-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        {{ $schedule->teacher->name ?? 'Sin Profesor' }}
                                    </p>
                                    <p class="text-xs text-gray-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        {{ $schedule->classroom->name ?? 'Aula Virtual' }}
                                        <span class="ml-auto text-[10px] uppercase font-bold text-gray-400 border px-1 rounded">{{ $schedule->modality }}</span>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-sm text-gray-500 font-medium">No hay horarios definidos.</p>
                                <p class="text-xs text-gray-400">Crea una nueva sección a la derecha.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Columna Der: Formulario --}}
                <div class="w-full md:w-7/12 p-6 overflow-y-auto bg-white">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2 flex items-center gap-2">
                        @if($scheduleId)
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Editar Sección
                            <button wire:click="resetScheduleInput" class="ml-auto text-xs font-medium text-indigo-600 hover:underline">
                                + Nueva
                            </button>
                        @else
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            Nueva Sección
                        @endif
                    </h3>

                    <form wire:submit.prevent="saveSchedule" class="space-y-5">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label value="Nombre Sección (Ej: 01, A, Matutina)" />
                                <x-text-input wire:model="s_section_name" class="w-full mt-1 bg-gray-50" placeholder="01" />
                                <x-input-error :messages="$errors->get('s_section_name')" />
                            </div>
                            <div>
                                <x-input-label value="Modalidad" />
                                <select wire:model="s_modality" class="w-full mt-1 rounded-lg border-gray-300 text-sm bg-gray-50 focus:ring-indigo-500">
                                    <option>Presencial</option>
                                    <option>Virtual</option>
                                    <option>Semi-Presencial</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Profesor Asignado" />
                            <select wire:model="s_teacher_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm bg-gray-50 focus:ring-indigo-500">
                                <option value="">-- Seleccionar Profesor --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('s_teacher_id')" />
                        </div>

                        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                            <h4 class="text-xs font-bold text-indigo-800 uppercase mb-3">Horario Semanal</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="col-span-1">
                                    <x-input-label value="Día" />
                                    <select wire:model="s_day_of_week" class="w-full mt-1 rounded-lg border-indigo-200 text-sm">
                                        <option>Lunes</option><option>Martes</option><option>Miércoles</option>
                                        <option>Jueves</option><option>Viernes</option><option>Sábado</option><option>Domingo</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label value="Hora Inicio" />
                                    <x-text-input type="time" wire:model="s_start_time" class="w-full mt-1 border-indigo-200" />
                                </div>
                                <div>
                                    <x-input-label value="Hora Fin" />
                                    <x-text-input type="time" wire:model="s_end_time" class="w-full mt-1 border-indigo-200" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Aula / Espacio Físico" />
                            <select wire:model="s_classroom_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm bg-gray-50">
                                <option value="">-- Aula Virtual / Sin Asignar --</option>
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} (Capacidad: {{ $c->capacity }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Inicio de Clases" />
                                <x-text-input type="date" wire:model="s_start_date" class="w-full mt-1" />
                            </div>
                            <div>
                                <x-input-label value="Fin de Clases" />
                                <x-text-input type="date" wire:model="s_end_date" class="w-full mt-1" />
                            </div>
                        </div>

                        <div class="pt-6 flex justify-end gap-3">
                            <x-secondary-button wire:click="closeScheduleModal">Cerrar</x-secondary-button>
                            <x-primary-button class="w-full sm:w-auto justify-center">
                                {{ $scheduleId ? 'Actualizar Sección' : 'Crear Sección' }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-modal>

</div>