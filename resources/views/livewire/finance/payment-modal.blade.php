<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP
        Fondo oscuro con desenfoque (backdrop-blur) para modernidad.
    --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- WRAPPER DE POSICIONAMIENTO --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-y-auto"
    >
        {{-- 
            FLEX CONTAINER
            Aquí aplicamos el padding (p-4 sm:p-6 md:p-10) para que el modal flote
            y no toque los bordes. Centrado vertical y horizontalmente.
        --}}
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6 md:p-10 text-center">
            
            {{-- 
                PANEL DEL MODAL
                - max-w-6xl: Ancho controlado.
                - rounded-2xl: Esquinas más modernas.
                - shadow-2xl: Profundidad.
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
                class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl overflow-hidden text-left transform transition-all border border-gray-100"
            >
                
                {{-- HEADER SIMPLE Y LIMPIO --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-20">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-50 p-2 rounded-lg text-indigo-600">
                            <i class="fas fa-cash-register text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Registrar Pago</h3>
                            <p class="text-xs text-gray-500">Complete los detalles de la transacción</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors focus:outline-none">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- 
                    CONTENIDO PRINCIPAL (FLEXBOX) 
                    Cambiado de Grid a Flex para mejor control de columnas.
                --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    
                    {{-- 
                        COLUMNA 1: SELECCIÓN DE ESTUDIANTE 
                        Ancho: 4/12 (aprox 33%) en desktop.
                        Fondo gris muy suave.
                    --}}
                    <div class="w-full lg:w-4/12 bg-gray-50/50 border-r border-gray-200 flex flex-col">
                        
                        {{-- Buscador --}}
                        <div class="p-5 border-b border-gray-200/60 bg-gray-50/80">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Buscar Cliente</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow shadow-sm placeholder-gray-400"
                                    placeholder="Nombre, Matrícula o Cédula..."
                                >
                            </div>
                        </div>

                        {{-- Lista / Resultados --}}
                        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                            @if($student)
                                {{-- Tarjeta de Estudiante Seleccionado --}}
                                <div class="bg-white p-5 rounded-xl border border-indigo-100 shadow-sm relative group animate-fade-in">
                                    <button 
                                        wire:click="clearStudent" 
                                        class="absolute top-2 right-2 text-gray-300 hover:text-red-500 p-1.5 rounded-full hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                                        title="Quitar estudiante"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">{{ $student->id_number }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 text-xs">
                                        <div class="flex justify-between py-1 border-b border-gray-50">
                                            <span class="text-gray-500">Email</span>
                                            <span class="font-medium text-gray-700 truncate max-w-[150px]" title="{{ $student->email }}">{{ $student->email }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-gray-50">
                                            <span class="text-gray-500">Teléfono</span>
                                            <span class="font-medium text-gray-700">{{ $student->mobile_phone ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between pt-1">
                                            <span class="text-gray-500">Estado</span>
                                            <span class="font-bold text-green-600">Activo</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center py-4">
                                    <p class="text-xs text-gray-400">¿No es este estudiante?</p>
                                    <button wire:click="clearStudent" class="text-xs text-indigo-600 font-bold hover:underline">Buscar otro</button>
                                </div>

                            @elseif(count($student_results) > 0)
                                <div class="px-2 pb-2">
                                    <span class="text-xs font-semibold text-gray-400 uppercase">Resultados</span>
                                </div>
                                @foreach($student_results as $result)
                                    <div 
                                        wire:click="selectStudent({{ $result->id }})"
                                        class="p-3 bg-white rounded-lg border border-gray-100 hover:border-indigo-300 hover:shadow-md cursor-pointer transition-all flex items-center gap-3 group"
                                    >
                                        <div class="h-8 w-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-sm text-gray-800 truncate">{{ $result->first_name }} {{ $result->last_name }}</div>
                                            <div class="text-[10px] text-gray-500 truncate">{{ $result->id_number }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="h-64 flex flex-col items-center justify-center text-gray-400 opacity-60">
                                    <i class="fas fa-users text-3xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">Busque para ver resultados</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: FORMULARIO DE PAGO 
                        Ancho: 8/12 (aprox 67%) en desktop.
                        Fondo blanco.
                    --}}
                    <div class="w-full lg:w-8/12 bg-white flex flex-col relative">
                        
                        {{-- Overlay de Bloqueo --}}
                        <div 
                            x-show="!$wire.student_id"
                            x-transition.opacity
                            class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center text-center backdrop-blur-[1px]"
                        >
                            <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 shadow-sm max-w-xs">
                                <div class="mx-auto h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-arrow-left text-indigo-600"></i>
                                </div>
                                <h4 class="text-gray-900 font-bold mb-1">Seleccione Cliente</h4>
                                <p class="text-sm text-gray-500">Elija un estudiante del panel izquierdo para continuar.</p>
                            </div>
                        </div>

                        {{-- Cuerpo del Formulario --}}
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                            <form wire:submit.prevent="savePayment" class="space-y-8">
                                
                                {{-- 1. Alerta de Deuda Pendiente --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm animate-fade-in-down">
                                        <div class="flex items-center gap-2 text-amber-800">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span class="text-sm font-bold">Pagos pendientes encontrados</span>
                                        </div>
                                        <select 
                                            wire:model.live="enrollment_id" 
                                            class="text-sm border-amber-300 rounded-md focus:ring-amber-500 focus:border-amber-500 bg-white py-1.5 pl-3 pr-8 w-full sm:w-auto"
                                        >
                                            <option value="">-- Seleccionar Deuda --</option>
                                            @foreach($studentEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->name }} (${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- 2. Configuración Financiera (Concepto y Estado) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto de Pago</label>
                                        <select 
                                            wire:model.live="payment_concept_id" 
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 transition-colors {{ $isConceptDisabled ? 'bg-gray-50 text-gray-500' : '' }}"
                                            {{ $isConceptDisabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Seleccione...</option>
                                            @foreach($payment_concepts as $concept)
                                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_concept_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                                        <select 
                                            wire:model="status" 
                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5"
                                        >
                                            <option value="Completado">Completado (Pagado)</option>
                                            <option value="Pendiente">Pendiente (Por Cobrar)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- 3. Área Principal: Monto y Método --}}
                                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                    <div class="flex flex-col md:flex-row gap-8">
                                        
                                        {{-- Input Monto --}}
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Monto a Cobrar</label>
                                            <div class="relative">
                                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-4 text-gray-400 text-2xl font-light">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="amount" 
                                                    class="w-full pl-10 pr-4 py-3 bg-white border-gray-300 rounded-lg text-3xl font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-right shadow-sm placeholder-gray-300 transition-all"
                                                    placeholder="0.00"
                                                    {{ $isAmountDisabled ? 'readonly' : '' }}
                                                >
                                            </div>
                                            @error('amount') <p class="mt-1 text-xs text-red-500 font-medium text-right">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- Selector Método --}}
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Método de Pago</label>
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                                    <button 
                                                        type="button"
                                                        wire:click="$set('gateway', '{{ $method }}')"
                                                        class="py-2.5 px-2 text-xs font-bold rounded-md border transition-all flex items-center justify-center gap-2 {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
                                                    >
                                                        <i class="fas {{ $method === 'Efectivo' ? 'fa-money-bill-wave' : ($method === 'Tarjeta' ? 'fa-credit-card' : ($method === 'Transferencia' ? 'fa-university' : 'fa-receipt')) }}"></i>
                                                        {{ $method }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Detalles Dinámicos (Caja / Referencia) --}}
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        
                                        {{-- CAJA EFECTIVO --}}
                                        <div x-show="$wire.gateway === 'Efectivo'" class="flex flex-col sm:flex-row items-center justify-between gap-6 animate-fade-in">
                                            <div class="w-full sm:w-1/2">
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Efectivo Recibido</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        wire:model.live="cash_received" 
                                                        class="w-full pl-7 pr-4 py-2 border-gray-300 rounded-lg text-lg font-bold text-gray-900 focus:ring-green-500 focus:border-green-500"
                                                        placeholder="0.00"
                                                    >
                                                </div>
                                            </div>
                                            <div class="w-full sm:w-auto text-center sm:text-right bg-white sm:bg-transparent p-3 sm:p-0 rounded-lg border sm:border-0 border-gray-200">
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
                                            <input 
                                                type="text" 
                                                wire:model="transaction_id" 
                                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2"
                                                placeholder="Ingrese código de referencia..."
                                            >
                                            @error('transaction_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- FOOTER (BOTONES) --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 sticky bottom-0 z-10">
                            <button 
                                wire:click="closeModal"
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200"
                            >
                                Cancelar
                            </button>
                            
                            <button 
                                wire:click="savePayment"
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading wire:target="savePayment" class="animate-spin">
                                    <i class="fas fa-circle-notch"></i>
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