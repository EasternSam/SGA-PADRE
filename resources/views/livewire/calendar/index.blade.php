<div class="min-h-screen bg-gray-50 pb-12" x-data="{ showDetail: @entangle('selectedDate') }">
    
    {{-- Header del Calendario --}}
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                
                {{-- Controles de Mes --}}
                <div class="flex items-center gap-4">
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                        <span class="text-indigo-600 bg-indigo-50 p-2 rounded-xl shadow-sm border border-indigo-100">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                        <span class="capitalize">{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->locale('es')->monthName }} <span class="text-gray-400 font-medium">{{ $currentYear }}</span></span>
                    </h1>
                    
                    <div class="flex bg-white rounded-xl shadow-sm border border-gray-200 p-1">
                        <button wire:click="previousMonth" class="p-2 hover:bg-gray-50 rounded-lg transition-all text-gray-500 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <div class="w-px bg-gray-200 mx-1"></div>
                        <button wire:click="nextMonth" class="p-2 hover:bg-gray-50 rounded-lg transition-all text-gray-500 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Filtros Rápidos (Estilo Tags) --}}
                <div class="flex flex-wrap items-center gap-3 text-sm font-medium">
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showClasses" class="peer sr-only">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 ring-2 ring-white"></span> Clases
                        </span>
                    </label>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showStartsEnds" class="peer sr-only">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span> Hitos
                        </span>
                    </label>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" wire:model.live="showAdmin" class="peer sr-only">
                        <span class="flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 bg-white text-gray-600 transition-all peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-checked:border-amber-200 hover:bg-gray-50 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white"></span> Admin
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid del Calendario --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-3xl shadow-xl ring-1 ring-black/5 overflow-hidden">
            
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
                @foreach(['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $day)
                    <div class="py-4 text-center">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-400">{{ $day }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 bg-gray-100 gap-px border-b border-gray-200">
                @foreach($calendarDays as $dayData)
                    @if(is_null($dayData))
                        <div class="bg-gray-50/30 min-h-[140px] md:min-h-[160px]"></div>
                    @else
                        <div 
                            wire:click="selectDay({{ $dayData['day'] }})"
                            class="bg-white min-h-[140px] md:min-h-[160px] p-3 hover:bg-gray-50 transition-all duration-200 cursor-pointer group relative flex flex-col justify-between
                            {{ $dayData['isToday'] ? 'bg-indigo-50/30 ring-inset ring-2 ring-indigo-500 z-10' : '' }}"
                        >
                            {{-- Número del día --}}
                            <div class="flex justify-between items-start">
                                <span class="
                                    text-sm font-bold w-8 h-8 flex items-center justify-center rounded-lg transition-all
                                    {{ $dayData['isToday'] 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 scale-110' 
                                        : 'text-gray-700 group-hover:bg-gray-200 group-hover:text-gray-900' 
                                    }}
                                ">
                                    {{ $dayData['day'] }}
                                </span>
                                
                                {{-- Indicador "Hoy" --}}
                                @if($dayData['isToday'])
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full uppercase tracking-wider">Hoy</span>
                                @endif
                            </div>

                            {{-- Contenedor de Indicadores --}}
                            <div class="space-y-1.5 mt-2">
                                {{-- 1. Eventos del Sistema (Hitos) --}}
                                @if($dayData['hasSystem'])
                                    <div class="flex items-center gap-2 px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100/50 group-hover:border-emerald-200 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span class="text-[10px] font-semibold truncate">Hitos Académicos</span>
                                    </div>
                                @endif
                                
                                {{-- 2. Eventos Administrativos --}}
                                @if($dayData['hasEvents'])
                                    <div class="flex items-center gap-2 px-2 py-1 rounded-md bg-amber-50 text-amber-700 border border-amber-100/50 group-hover:border-amber-200 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                                        <span class="text-[10px] font-semibold truncate">Eventos</span>
                                    </div>
                                @endif

                                {{-- 3. Clases (Indicador más sutil si hay muchas) --}}
                                @if($dayData['hasClasses'])
                                    <div class="flex items-center gap-2 px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100/50 group-hover:border-indigo-200 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                        <span class="text-[10px] font-semibold truncate">Clases Programadas</span>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Overlay sutil al hacer hover --}}
                            <div class="absolute inset-0 border-2 border-indigo-500 rounded-lg opacity-0 group-hover:opacity-10 pointer-events-none"></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-400">
            Haz clic en un día para ver el detalle completo de actividades.
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
            x-transition:enter="ease-in-out duration-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-500"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
            @click="showDetail = false; $wire.set('selectedDate', null)"
        ></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    
                    <div 
                        x-show="showDetail"
                        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-md"
                    >
                        <div class="flex h-full flex-col bg-white shadow-2xl">
                            
                            {{-- Header del Panel --}}
                            <div class="bg-indigo-600 px-4 py-6 sm:px-6 relative overflow-hidden">
                                {{-- Decoración de fondo --}}
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-indigo-400 opacity-20 rounded-full blur-xl"></div>

                                <div class="flex items-center justify-between relative z-10">
                                    <h2 class="text-xl font-bold leading-6 text-white tracking-tight" id="slide-over-title">
                                        Resumen del Día
                                    </h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button type="button" class="relative rounded-full bg-indigo-700/50 p-1 text-indigo-200 hover:text-white hover:bg-indigo-500 transition-colors focus:outline-none focus:ring-2 focus:ring-white" @click="showDetail = false; $wire.set('selectedDate', null)">
                                            <span class="absolute -inset-2.5"></span>
                                            <span class="sr-only">Cerrar panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 relative z-10">
                                    <p class="text-sm font-medium text-indigo-100 capitalize">
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
                                            <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Hitos Académicos</h3>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($selectedDayData['system_events'] as $sysEvent)
                                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm border-l-4 {{ str_replace('bg-', 'border-l-', $sysEvent['color']) }} hover:shadow-md transition-shadow">
                                                    <p class="text-sm font-bold text-gray-900">{{ $sysEvent['title'] }}</p>
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
                                            <span class="p-1.5 bg-indigo-100 text-indigo-600 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Clases Programadas</h3>
                                        </div>
                                        
                                        <div class="space-y-3 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
                                            @foreach($selectedDayData['sections'] as $section)
                                                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                                    <!-- Icono Timeline -->
                                                    <div class="absolute left-0 md:static flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-50 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 ml-1">
                                                        <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                                                    </div>
                                                    
                                                    <!-- Tarjeta Clase -->
                                                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all ml-12 md:ml-0">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                                                {{ \Carbon\Carbon::parse($section->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('H:i') }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-400 font-mono">{{ $section->section_name }}</span>
                                                        </div>
                                                        <h4 class="font-bold text-gray-900 text-sm mb-1 leading-tight">{{ $section->module->name }}</h4>
                                                        
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
                                    <div class="text-center py-8 bg-white rounded-xl border-2 border-dashed border-gray-200">
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
                                            <span class="p-1.5 bg-amber-100 text-amber-600 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Agenda Extra</h3>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($selectedDayData['events'] as $event)
                                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                                                    <p class="text-sm font-bold text-gray-900">{{ $event['title'] }}</p>
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