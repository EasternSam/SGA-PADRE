<div class="min-h-screen bg-gray-50/50 pb-12">
    
    {{-- Notificaciones --}}
    <x-action-message on="notify" />

    {{-- Encabezado con Breadcrumb y Stats --}}
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Info Carrera --}}
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('admin.careers.index') }}" class="hover:text-indigo-600 hover:underline" wire:navigate>Carreras</a>
                        <span>/</span>
                        <span>Pensum</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        {{ $career->name }}
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold tracking-wide uppercase">
                            {{ $career->code }}
                        </span>
                    </h1>
                </div>

                {{-- Stats Rápidos --}}
                <div class="flex items-center gap-6 text-sm">
                    <div class="hidden sm:block text-right">
                        <p class="text-gray-500">Créditos Totales</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $career->total_credits }}</p>
                    </div>
                    <div class="hidden sm:block text-right border-l border-gray-200 pl-6">
                        <p class="text-gray-500">Duración</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $career->duration_periods }} Cuatrimestres</p>
                    </div>
                    <div class="pl-2">
                        <button wire:click="openCreateModal(1)" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-md hover:bg-indigo-500 transition-all">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Agregar Materia
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido del Pensum --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">
        
        @if($modulesByPeriod->isEmpty())
            <div class="text-center py-20 bg-white rounded-2xl border border-gray-200 border-dashed">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Pensum Vacío</h3>
                <p class="mt-1 text-sm text-gray-500">Comienza agregando las materias del primer cuatrimestre.</p>
                <div class="mt-6">
                    <button wire:click="openCreateModal(1)" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Agregar Primera Materia
                    </button>
                </div>
            </div>
        @else
            {{-- Loop por Periodos (Cuatrimestres) --}}
            @foreach($modulesByPeriod as $period => $modules)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header del Periodo --}}
                    <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white border border-gray-200 text-sm font-bold text-gray-600 shadow-sm">
                                {{ $period }}
                            </span>
                            Cuatrimestre {{ $period }}
                        </h3>
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-medium text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                                {{ $modules->sum('credits') }} Créditos
                            </span>
                            <button wire:click="openCreateModal({{ $period }})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium hover:underline">
                                + Agregar aquí
                            </button>
                        </div>
                    </div>

                    {{-- Lista de Materias --}}
                    <div class="divide-y divide-gray-100">
                        @foreach($modules as $module)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors group">
                                <div class="flex items-start gap-4">
                                    {{-- Código --}}
                                    <div class="shrink-0 w-24">
                                        <span class="block font-mono text-sm font-bold text-gray-700">{{ $module->code }}</span>
                                        @if($module->is_elective)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800 mt-1">
                                                Electiva
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- Detalles --}}
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $module->name }}</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                {{ $module->credits }} Créditos
                                            </span>
                                            
                                            @if($module->prerequisites->count() > 0)
                                                <div class="flex items-center gap-1.5 text-rose-600 font-medium">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                    Pre-req: 
                                                    @foreach($module->prerequisites as $pre)
                                                        <span class="bg-rose-50 px-1.5 rounded border border-rose-100">{{ $pre->code }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 flex items-center gap-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                                                    Sin requisitos
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="edit({{ $module->id }})" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Editar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button wire:click="delete({{ $module->id }})" wire:confirm="¿Seguro que deseas eliminar esta materia? Esto podría afectar a los estudiantes inscritos." class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Conector Visual entre periodos --}}
                @if(!$loop->last)
                    <div class="flex justify-center h-6">
                        <div class="w-px bg-gray-300 border-l border-dashed border-gray-400"></div>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- Botón para agregar siguiente periodo --}}
        @if($modulesByPeriod->isNotEmpty())
            <div class="flex justify-center pb-8">
                <button wire:click="openCreateModal({{ $modulesByPeriod->keys()->max() + 1 }})" class="group flex flex-col items-center gap-2 text-gray-400 hover:text-indigo-600 transition-colors">
                    <div class="h-10 w-10 rounded-full border-2 border-dashed border-current flex items-center justify-center group-hover:border-solid group-hover:bg-indigo-50">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <span class="text-sm font-medium">Agregar Cuatrimestre {{ $modulesByPeriod->keys()->max() + 1 }}</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Modal Crear/Editar Asignatura --}}
    <x-modal name="module-form-modal" maxWidth="2xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $modalTitle }}</h2>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="save" class="p-6 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Código --}}
                    <div class="md:col-span-1">
                        <x-input-label for="code" value="Clave" />
                        <x-text-input id="code" type="text" class="mt-1 w-full font-mono uppercase" wire:model="code" placeholder="MAT-101" />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    
                    {{-- Nombre --}}
                    <div class="md:col-span-3">
                        <x-input-label for="name" value="Nombre de la Asignatura" />
                        <x-text-input id="name" type="text" class="mt-1 w-full" wire:model="name" placeholder="Ej. Cálculo Diferencial" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Créditos --}}
                    <div>
                        <x-input-label for="credits" value="Créditos" />
                        <x-text-input id="credits" type="number" class="mt-1 w-full" wire:model="credits" />
                        <x-input-error :messages="$errors->get('credits')" class="mt-1" />
                    </div>
                    
                    {{-- Periodo --}}
                    <div>
                        <x-input-label for="period_number" value="Cuatrimestre" />
                        <x-text-input id="period_number" type="number" min="1" class="mt-1 w-full" wire:model="period_number" />
                        <x-input-error :messages="$errors->get('period_number')" class="mt-1" />
                    </div>

                    {{-- Electiva --}}
                    <div class="flex items-center pt-8">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_elective" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Es Electiva</span>
                        </label>
                    </div>
                </div>

                {{-- Prerrequisitos (Multi-Select Nativo Simple) --}}
                <div>
                    <x-input-label for="prerequisites" value="Pre-requisitos (Selecciona con Ctrl/Cmd + Click)" />
                    <div class="mt-1 relative">
                        <select id="prerequisites" wire:model="selectedPrerequisites" multiple 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-32">
                            @foreach($availablePrerequisites as $pre)
                                <option value="{{ $pre->id }}">
                                    [{{ $pre->code }}] {{ $pre->name }} (Cuatrimestre {{ $pre->period_number }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Materias que el estudiante debe aprobar antes de tomar esta.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-secondary-button wire:click="closeModal">Cancelar</x-secondary-button>
                    <x-primary-button>Guardar Asignatura</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>