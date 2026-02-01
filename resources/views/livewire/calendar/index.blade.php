<div class="min-h-screen bg-gray-100 flex flex-col" x-data="{ showDetail: @entangle('selectedDate') }">
    
    {{-- Header del Calendario --}}
    <div class="bg-white border-b border-gray-200 sticky top-16 z-20 shadow-sm flex-none">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Controles de Mes y Título --}}
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <span class="text-blue-600 bg-blue-50 p-1.5 rounded-lg border border-blue-100">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                        <span class="capitalize">{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->locale('es')->monthName }} <span class="text-gray-400 font-normal">{{ $currentYear }}</span></span>
                    </h1>
                    
                    <div class="flex bg-white border border-gray-200 rounded-lg p-0.5 shadow-sm">
                        <button wire:click="previousMonth" class="p-1.5 hover:bg-gray-50 rounded-md transition-all text-gray-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <div class="w-px bg-gray-200 my-1"></div>
                        <button wire:click="nextMonth" class="p-1.5 hover:bg-gray-50 rounded-md transition-all text-gray-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Filtros Rápidos --}}
                <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showClasses" class="peer sr-only">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Clases
                        </span>
                    </label>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showStartsEnds" class="peer sr-only">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hitos
                        </span>
                    </label>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showAdmin" class="peer sr-only">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-checked:border-amber-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Admin
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid del Calendario (Estilo Cuadrícula Completa) --}}
    <div class="flex-1 px-4 sm:px-6 lg:px-8 py-6 h-full flex flex-col">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col flex-1">
            
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 flex-none">
                @foreach(['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $day)
                    <div class="py-2 px-2 text-center border-r border-gray-200 last:border-r-0">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $day }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 bg-gray-200 gap-px border-gray-200 flex-1">
                @foreach($calendarDays as $dayData)
                    @if(is_null($dayData))
                        <div class="bg-gray-50 min-h-[120px]"></div>
                    @else
                        <div 
                            wire:click="selectDay({{ $dayData['day'] }})"
                            class="bg-white min-h-[120px] p-2 relative hover:bg-blue-50 transition-colors cursor-pointer group flex flex-col gap-1 overflow-hidden
                            {{ $dayData['isToday'] ? 'bg-blue-50/40' : '' }}"
                        >
                            {{-- Número del día --}}
                            <div class="flex justify-between items-center mb-1">
                                <span class="
                                    text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full
                                    {{ $dayData['isToday'] 
                                        ? 'bg-blue-600 text-white shadow-sm' 
                                        : 'text-gray-700' 
                                    }}
                                ">
                                    {{ $dayData['day'] }}
                                </span>
                            </div>

                            {{-- Contenedor de "Chips" de eventos (visualización compacta) --}}
                            <div class="flex flex-col gap-1 overflow-y-auto no-scrollbar max-h-[100px]">
                                {{-- 1. Eventos del Sistema (Hitos - Verde) --}}
                                @if($dayData['hasSystem'])
                                    <div class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800 truncate border-l-2 border-emerald-500 shadow-sm">
                                        Hitos Académicos
                                    </div>
                                @endif
                                
                                {{-- 2. Eventos Administrativos (Ámbar) --}}
                                @if($dayData['hasEvents'])
                                    <div class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800 truncate border-l-2 border-amber-500 shadow-sm">
                                        Eventos
                                    </div>
                                @endif

                                {{-- 3. Clases (Azul) --}}
                                @if($dayData['hasClasses'])
                                    <div class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 truncate border-l-2 border-blue-500 shadow-sm">
                                        Clases
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Slide-over Panel Lateral (Detalles del Día) --}}
    <div 
        x-show="showDetail" 
        style="display: none;"
        class="relative z-50" 
        aria-labelledby="slide-over-title" 
        role="dialog" 
        aria-modal="true"
    >
        {{-- Backdrop con Blur --}}
        <div 
            x-show="showDetail"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity"
            @click="showDetail = false; $wire.set('selectedDate', null)"
        ></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    
                    <div 
                        x-show="showDetail"
                        x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-md"
                    >
                        <div class="flex h-full flex-col bg-white shadow-xl">
                            
                            {{-- Header del Panel --}}
                            <div class="bg-blue-600 px-4 py-6 sm:px-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-blue-600 mix-blend-multiply" aria-hidden="true"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    <h2 class="text-xl font-semibold leading-6 text-white tracking-tight" id="slide-over-title">
                                        Resumen del Día
                                    </h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button type="button" class="relative rounded-full bg-blue-700/50 p-1 text-blue-200 hover:text-white hover:bg-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-white" @click="showDetail = false; $wire.set('selectedDate', null)">
                                            <span class="absolute -inset-2.5"></span>
                                            <span class="sr-only">Cerrar panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 relative z-10">
                                    <p class="text-sm font-medium text-blue-100 capitalize">
                                        {{ $selectedDayData['date_human'] ?? 'Seleccione un día' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Contenido del Panel --}}
                            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 space-y-8 bg-gray-50">
                                
                                {{-- 1. Eventos del Sistema --}}
                                @if(!empty($selectedDayData['system_events']) && count($selectedDayData['system_events']) > 0)
                                    <section>
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-md">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Hitos Académicos</h3>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($selectedDayData['system_events'] as $sysEvent)
                                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm border-l-4 {{ str_replace('bg-', 'border-l-', $sysEvent['color']) }} hover:shadow-md transition-shadow">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $sysEvent['title'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $sysEvent['description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @endif

                                {{-- 2. Clases / Secciones del Día --}}
                                @if(!empty($selectedDayData['sections']) && count($selectedDayData['sections']) > 0)
                                    <section>
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="p-1.5 bg-blue-100 text-blue-600 rounded-md">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Clases Programadas</h3>
                                        </div>
                                        
                                        <div class="space-y-3 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
                                            @foreach($selectedDayData['sections'] as $section)
                                                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                                    <!-- Icono Timeline -->
                                                    <div class="absolute left-0 md:static flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-50 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 ml-1">
                                                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                                    </div>
                                                    
                                                    <!-- Tarjeta Clase -->
                                                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all ml-12 md:ml-0">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                                {{ \Carbon\Carbon::parse($section->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('H:i') }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-400 font-mono">{{ $section->section_name }}</span>
                                                        </div>
                                                        <h4 class="font-semibold text-gray-900 text-sm mb-1 leading-tight">{{ $section->module->name }}</h4>
                                                        
                                                        <div class="flex flex-col gap-1 text-xs text-gray-500 mt-2 pt-2 border-t border-gray-100">
                                                            <div class="flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                                {{ $section->teacher->name ?? 'Sin Profesor' }}
                                                            </div>
                                                            <div class="flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                                {{ $section->classroom->name ?? 'Virtual' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @elseif($showClasses)
                                    <div class="text-center py-8 bg-white rounded-lg border-2 border-dashed border-gray-200">
                                        <div class="mx-auto h-12 w-12 text-gray-300">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        </div>
                                        <h3 class="mt-2 text-sm font-semibold text-gray-900">Día libre</h3>
                                        <p class="mt-1 text-xs text-gray-500">No hay clases programadas para hoy.</p>
                                    </div>
                                @endif

                                {{-- 3. Eventos Adicionales --}}
                                @if(!empty($selectedDayData['events']) && count($selectedDayData['events']) > 0)
                                    <section>
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="p-1.5 bg-amber-100 text-amber-600 rounded-md">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Agenda Extra</h3>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($selectedDayData['events'] as $event)
                                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $event['title'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $event['description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>