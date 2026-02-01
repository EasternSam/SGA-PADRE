<div class="min-h-screen bg-gray-50 pb-12" x-data="{ showDetail: @entangle('selectedDate') }">
    
    {{-- Header del Calendario --}}
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Controles de Mes --}}
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span class="text-indigo-600 bg-indigo-50 p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                        {{ ucfirst(\Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->locale('es')->monthName) }} {{ $currentYear }}
                    </h1>
                    
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button wire:click="previousMonth" class="p-1 hover:bg-white rounded-md shadow-sm transition-all text-gray-600 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button wire:click="nextMonth" class="p-1 hover:bg-white rounded-md shadow-sm transition-all text-gray-600 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Filtros Rápidos --}}
                <div class="flex items-center gap-4 text-sm">
                    <label class="flex items-center cursor-pointer gap-2 bg-white px-3 py-1.5 rounded-full border border-gray-200 shadow-sm hover:bg-gray-50 transition">
                        <input type="checkbox" wire:model.live="showClasses" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Clases
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer gap-2 bg-white px-3 py-1.5 rounded-full border border-gray-200 shadow-sm hover:bg-gray-50 transition">
                        <input type="checkbox" wire:model.live="showStartsEnds" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Inicios/Fines
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer gap-2 bg-white px-3 py-1.5 rounded-full border border-gray-200 shadow-sm hover:bg-gray-50 transition">
                        <input type="checkbox" wire:model.live="showAdmin" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Admin
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid del Calendario --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-black/5 overflow-hidden">
            
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                @foreach(['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $day)
                    <div class="py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-500">
                        <span class="hidden md:inline">{{ $day }}</span>
                        <span class="md:hidden">{{ substr($day, 0, 3) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 bg-gray-200 gap-px border-b border-gray-200">
                @foreach($calendarDays as $dayData)
                    @if(is_null($dayData))
                        <div class="bg-white min-h-[120px] md:min-h-[160px] opacity-50"></div>
                    @else
                        <div 
                            wire:click="selectDay({{ $dayData['day'] }})"
                            class="bg-white min-h-[120px] md:min-h-[160px] p-2 hover:bg-indigo-50/50 transition-colors cursor-pointer group relative flex flex-col {{ $dayData['isToday'] ? 'bg-indigo-50/30' : '' }}"
                        >
                            {{-- Número del día --}}
                            <div class="flex justify-between items-start">
                                <span class="
                                    text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full
                                    {{ $dayData['isToday'] ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-700 group-hover:bg-gray-100' }}
                                ">
                                    {{ $dayData['day'] }}
                                </span>
                            </div>

                            {{-- Indicadores visuales --}}
                            <div class="mt-auto space-y-1">
                                @if($dayData['hasSystem'])
                                    <div class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 truncate border border-emerald-200 shadow-sm">
                                        Evento Sistema
                                    </div>
                                @endif
                                
                                @if($dayData['hasEvents'])
                                    <div class="px-2 py-1 rounded text-[10px] font-bold bg-amber-100 text-amber-700 truncate border border-amber-200 shadow-sm">
                                        Evento Extra
                                    </div>
                                @endif

                                @if($dayData['hasClasses'])
                                    <div class="flex items-center gap-1 px-1">
                                        <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                                        <span class="text-[10px] text-gray-500 font-medium hidden md:inline">Clases hoy</span>
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
        {{-- Backdrop --}}
        <div 
            x-show="showDetail"
            x-transition:enter="ease-in-out duration-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-500"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"
            @click="showDetail = false; $wire.set('selectedDate', null)"
        ></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    
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
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl">
                            
                            {{-- Header del Panel --}}
                            <div class="bg-indigo-700 px-4 py-6 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-xl font-semibold leading-6 text-white" id="slide-over-title">
                                        Detalles del Día
                                    </h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button type="button" class="relative rounded-md bg-indigo-700 text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="showDetail = false; $wire.set('selectedDate', null)">
                                            <span class="absolute -inset-2.5"></span>
                                            <span class="sr-only">Cerrar panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-indigo-200">
                                        {{ $selectedDayData['date_human'] ?? 'Seleccione un día' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Contenido del Panel --}}
                            <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-8">
                                
                                {{-- 1. Eventos del Sistema (Inicios/Fines/Pagos) --}}
                                @if(!empty($selectedDayData['system_events']) && count($selectedDayData['system_events']) > 0)
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b border-gray-100 pb-1">
                                            Eventos Administrativos
                                        </h3>
                                        <div class="space-y-3">
                                            @foreach($selectedDayData['system_events'] as $sysEvent)
                                                <div class="flex gap-3 p-3 rounded-lg border bg-opacity-50 {{ $sysEvent['color'] }}">
                                                    <div class="shrink-0 pt-0.5">
                                                        @if($sysEvent['type'] === 'start') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                        @elseif($sysEvent['type'] === 'end') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        @else <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold">{{ $sysEvent['title'] }}</p>
                                                        <p class="text-xs opacity-80">{{ $sysEvent['description'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- 2. Clases / Secciones del Día --}}
                                @if(!empty($selectedDayData['sections']) && count($selectedDayData['sections']) > 0)
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b border-gray-100 pb-1">
                                            Clases Programadas
                                        </h3>
                                        <div class="space-y-4">
                                            @foreach($selectedDayData['sections'] as $section)
                                                <div class="relative pl-4 border-l-2 border-indigo-200 hover:border-indigo-500 transition-colors">
                                                    <p class="text-sm font-bold text-gray-900">
                                                        {{ $section->module->name }}
                                                        <span class="text-xs font-normal text-gray-500">({{ $section->section_name }})</span>
                                                    </p>
                                                    <p class="text-xs text-indigo-600 font-medium mb-1">
                                                        {{ \Carbon\Carbon::parse($section->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('H:i') }}
                                                    </p>
                                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                                        <div class="flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                            {{ $section->teacher->name ?? 'Sin Profesor' }}
                                                        </div>
                                                        <div class="flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                            {{ $section->classroom->name ?? 'Virtual' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($showClasses)
                                    <div class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                        <p class="text-sm text-gray-500">No hay clases programadas para hoy.</p>
                                    </div>
                                @endif

                                {{-- 3. Eventos Adicionales (Feriados, etc.) --}}
                                @if(!empty($selectedDayData['events']) && count($selectedDayData['events']) > 0)
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b border-gray-100 pb-1">
                                            Agenda Extra
                                        </h3>
                                        <div class="space-y-2">
                                            @foreach($selectedDayData['events'] as $event)
                                                <div class="bg-white p-3 rounded-lg border shadow-sm">
                                                    <p class="text-sm font-bold text-gray-900">{{ $event['title'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $event['description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>