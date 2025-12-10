<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP
        Fondo oscuro fijo.
        CORRECCIÓN: Se agrega `bg-opacity-75` y estilo inline para asegurar oscurecimiento.
    --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
        style="background-color: rgba(17, 24, 39, 0.8);"
        aria-hidden="true"
    ></div>

    {{-- WRAPPER DE POSICIONAMIENTO --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-y-auto"
    >
        {{-- 
            FLEX CONTAINER
            Centrado vertical/horizontal.
        --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            {{-- 
                PANEL DEL MODAL
                Cambio clave: `h-[90vh]` o `max-h-[90vh]` con `flex flex-col`.
                Esto fuerza al modal a tener una altura máxima y permite scrolls internos.
            --}}
            <div 
                x-show="show"
                x-trap.noscroll="show"
                @click.away="show = false"
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-7xl h-full sm:h-[90vh] bg-white sm:rounded-xl shadow-2xl text-left transform transition-all flex flex-col overflow-hidden border border-gray-300"
            >
                
                {{-- HEADER (Fijo arriba) --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50 shrink-0 z-20">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-md flex-shrink-0">
                            <!-- Icono Caja -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 leading-tight">Terminal de Pagos</h3>
                            <p class="text-xs text-gray-500 font-medium">Nueva transacción</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-colors focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- 
                    CONTENIDO PRINCIPAL (Scrollable)
                    Usamos `flex-1 overflow-hidden` para que este contenedor ocupe el espacio restante
                    pero no haga scroll global. Los hijos harán el scroll.
                --}}
                <div class="flex flex-col lg:flex-row flex-1 min-h-0 overflow-hidden">
                    
                    {{-- 
                        COLUMNA 1: SELECCIÓN DE ESTUDIANTE 
                        `overflow-y-auto` permite que solo esta lista haga scroll.
                    --}}
                    <div class="w-full lg:w-4/12 bg-gray-100 border-b lg:border-b-0 lg:border-r border-gray-300 flex flex-col h-full overflow-hidden">
                        
                        {{-- Buscador (Sticky top dentro de la columna) --}}
                        <div class="p-4 border-b border-gray-300 bg-gray-100 shrink-0 z-10">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Buscar Cliente</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-11 pr-4 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                    placeholder="Nombre, Matrícula o Cédula..."
                                >
                            </div>
                        </div>

                        {{-- Lista / Resultados con Scroll Independiente --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @if($student)
                                {{-- 
                                    DISEÑO REVISADO: TARJETA DE ESTUDIANTE 
                                    Estilo más compacto tipo "Widget" seleccionado.
                                --}}
                                <div class="bg-indigo-50 rounded-xl border border-indigo-200 p-4 relative animate-fade-in shadow-sm">
                                    {{-- Header: Avatar + Nombre + Botón Cerrar --}}
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm shrink-0 ring-2 ring-indigo-200">
                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-gray-900 text-sm leading-tight truncate">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[10px] uppercase font-bold text-indigo-400">ID</span>
                                                    <span class="text-xs text-indigo-700 font-bold truncate">{{ $student->id_number }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="clearStudent" 
                                            class="text-indigo-400 hover:text-red-500 hover:bg-white p-1.5 rounded-lg transition-colors shrink-0"
                                            title="Cambiar estudiante"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Grid de Detalles --}}
                                    <div class="grid grid-cols-1 gap-2 text-xs border-t border-indigo-200/60 pt-3">
                                        <div class="flex items-center justify-between group">
                                            <span class="text-gray-500 font-medium">Email:</span>
                                            <span class="text-gray-900 font-medium truncate max-w-[160px]" title="{{ $student->email }}">{{ $student->email }}</span>
                                        </div>
                                        <div class="flex items-center justify-between group">
                                            <span class="text-gray-500 font-medium">Teléfono:</span>
                                            <span class="text-gray-900 font-medium">{{ $student->mobile_phone ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Botón explícito de acción secundaria --}}
                                    <div class="mt-3 pt-2 border-t border-indigo-200/60 text-center">
                                        <button wire:click="clearStudent" class="text-xs text-indigo-600 font-bold hover:text-indigo-800 hover:underline transition-all">
                                            Seleccionar otro cliente
                                        </button>
                                    </div>
                                </div>

                            @elseif(count($student_results) > 0)
                                <div class="px-1 pb-1">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Resultados encontrados</span>
                                </div>
                                @foreach($student_results as $result)
                                    <div 
                                        wire:click="selectStudent({{ $result->id }})"
                                        class="p-3 bg-white rounded-lg border border-gray-300 hover:border-indigo-500 hover:shadow-md cursor-pointer transition-all flex items-center gap-3 group"
                                    >
                                        <div class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors border border-indigo-200 flex-shrink-0">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-sm text-gray-900 truncate">{{ $result->first_name }} {{ $result->last_name }}</div>
                                            <div class="text-xs text-gray-500 truncate font-medium">ID: {{ $result->id_number }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="h-64 flex flex-col items-center justify-center text-gray-400 opacity-60">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mb-3 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500">Busque para ver resultados</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: FORMULARIO DE PAGO 
                        Flex column para separar el cuerpo (scrollable) del footer (fijo).
                    --}}
                    <div class="w-full lg:w-8/12 bg-white flex flex-col relative h-full overflow-hidden">
                        
                        {{-- Overlay de Bloqueo --}}
                        <div 
                            x-show="!$wire.student_id"
                            x-transition.opacity
                            class="absolute inset-0 bg-white/95 z-30 flex flex-col items-center justify-center text-center backdrop-blur-[2px]"
                        >
                            <div class="bg-white p-8 rounded-2xl border border-gray-200 shadow-xl max-w-xs">
                                <div class="mx-auto h-16 w-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4 border border-indigo-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                    </svg>
                                </div>
                                <h4 class="text-gray-900 font-bold text-lg mb-2">Seleccione Cliente</h4>
                                <p class="text-sm text-gray-500">Elija un estudiante del panel izquierdo para habilitar la caja.</p>
                            </div>
                        </div>

                        {{-- Cuerpo del Formulario (Scrollable) --}}
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-8 custom-scrollbar">
                            <form wire:submit.prevent="savePayment" id="payment-form">
                                
                                {{-- 1. Alerta de Deuda Pendiente --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm animate-fade-in-down mb-6">
                                        <div class="flex items-center gap-3 text-amber-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                            </svg>
                                            <span class="text-sm font-bold">Pagos pendientes detectados</span>
                                        </div>
                                        <select 
                                            wire:model.live="enrollment_id" 
                                            class="text-sm border-amber-300 rounded-md focus:ring-amber-500 focus:border-amber-500 bg-white text-gray-900 font-medium py-2 pl-3 pr-8 w-full sm:w-auto shadow-sm"
                                        >
                                            <option value="">-- Seleccionar Deuda para Pagar --</option>
                                            @foreach($studentEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->name }} (${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- 2. Configuración Financiera --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Concepto de Pago</label>
                                        <div class="relative">
                                            <select 
                                                wire:model.live="payment_concept_id" 
                                                class="w-full h-12 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4 pr-10 text-gray-900 font-medium transition-colors {{ $isConceptDisabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                                {{ $isConceptDisabled ? 'disabled' : '' }}
                                            >
                                                <option value="">Seleccione...</option>
                                                @foreach($payment_concepts as $concept)
                                                    <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('payment_concept_id') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Estado</label>
                                        <div class="relative">
                                            <select 
                                                wire:model="status" 
                                                class="w-full h-12 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4 pr-10 text-gray-900 font-medium"
                                            >
                                                <option value="Completado">Completado (Pagado)</option>
                                                <option value="Pendiente">Pendiente (Por Cobrar)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. Área Principal: Monto y Método --}}
                                <div class="bg-gray-50 rounded-xl p-6 border border-gray-300 shadow-inner">
                                    <div class="flex flex-col md:flex-row gap-8">
                                        
                                        {{-- Input Monto --}}
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Monto a Cobrar (DOP)</label>
                                            <div class="relative group">
                                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-4 text-gray-500 text-3xl font-light group-focus-within:text-indigo-600 transition-colors">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="amount" 
                                                    class="w-full pl-10 pr-4 py-4 bg-white border border-gray-300 rounded-xl text-3xl lg:text-4xl font-black text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-right shadow-sm placeholder-gray-300 transition-all"
                                                    placeholder="0.00"
                                                    {{ $isAmountDisabled ? 'readonly' : '' }}
                                                >
                                            </div>
                                            @error('amount') <p class="mt-2 text-sm text-red-600 font-bold text-right">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- Selector Método --}}
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Método de Pago</label>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                                    <button 
                                                        type="button"
                                                        wire:click="$set('gateway', '{{ $method }}')"
                                                        class="py-3 px-2 text-xs font-bold rounded-lg border transition-all flex items-center justify-center gap-2 {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-1 ring-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400 hover:bg-gray-100' }}"
                                                    >
                                                        @if($method === 'Efectivo')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                        @elseif($method === 'Tarjeta')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                                                        @elseif($method === 'Transferencia')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                                        @endif
                                                        {{ $method }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Detalles Dinámicos (Caja / Referencia) --}}
                                    <div class="mt-6 pt-6 border-t border-gray-300">
                                        
                                        {{-- CAJA EFECTIVO --}}
                                        <div x-show="$wire.gateway === 'Efectivo'" class="flex flex-col sm:flex-row items-center justify-between gap-6 animate-fade-in">
                                            <div class="w-full sm:w-1/2">
                                                <label class="block text-sm font-bold text-gray-700 mb-1">Efectivo Recibido</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold">$</span>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        wire:model.live="cash_received" 
                                                        class="w-full pl-7 pr-4 py-3 border border-gray-300 rounded-lg text-lg font-bold text-gray-900 focus:ring-green-500 focus:border-green-500 shadow-sm"
                                                        placeholder="0.00"
                                                    >
                                                </div>
                                            </div>
                                            <div class="w-full sm:w-auto text-center sm:text-right bg-white sm:bg-transparent p-3 sm:p-0 rounded-lg border sm:border-0 border-gray-300">
                                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Cambio / Devuelta</span>
                                                <span class="text-3xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                                                    ${{ number_format($change_amount, 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- REFERENCIA --}}
                                        <div x-show="$wire.gateway !== 'Efectivo'" class="animate-fade-in">
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                                <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación (Auth Code)' : 'Referencia de Transacción'"></span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                </span>
                                                <input 
                                                    type="text" 
                                                    wire:model="transaction_id" 
                                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                                    placeholder="Ingrese código de referencia..."
                                                >
                                            </div>
                                            @error('transaction_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- FOOTER (BOTONES) (Sticky Bottom estructural) --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 shrink-0 z-20">
                            <button 
                                wire:click="closeModal"
                                class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 shadow-sm transition-colors focus:outline-none"
                            >
                                Cancelar
                            </button>
                            
                            <button 
                                wire:click="savePayment"
                                wire:loading.attr="disabled"
                                form="payment-form"
                                class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading wire:target="savePayment" class="animate-spin">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span>Procesar Cobro</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>