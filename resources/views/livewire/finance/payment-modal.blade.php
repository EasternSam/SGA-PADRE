<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP (Fondo Oscuro)
        Separado totalmente del contenido para asegurar que cubra toda la pantalla
        y su opacidad funcione independientemente.
    --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- 
        CONTENEDOR DEL MODAL (Wrapper de Posicionamiento)
        Este div maneja el scroll y el centrado.
    --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-y-auto"
    >
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            
            {{-- 
                PANEL DEL MODAL
                Diseño ancho (max-w-7xl) con altura controlada.
            --}}
            <div 
                x-show="show"
                x-trap.noscroll="show"
                @click.away="show = false"
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl"
            >
                
                {{-- LAYOUT PRINCIPAL: 2 COLUMNAS (Grid) --}}
                <div class="grid grid-cols-12 min-h-[600px]">
                    
                    {{-- 
                        COLUMNA IZQUIERDA: CONTEXTO (ESTUDIANTE) 
                        Ancho: 4/12 (33%) en pantallas grandes.
                        Fondo: Gris muy suave para diferenciar.
                    --}}
                    <div class="col-span-12 lg:col-span-4 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200 flex flex-col">
                        
                        {{-- Encabezado Columna Izquierda --}}
                        <div class="p-6 border-b border-gray-200/60">
                            <h3 class="text-lg font-bold text-gray-900">Cliente</h3>
                            <p class="text-sm text-gray-500">Seleccione a quién se le va a cobrar.</p>
                        </div>

                        {{-- Cuerpo Columna Izquierda --}}
                        <div class="p-6 flex-1 flex flex-col gap-6 overflow-y-auto">
                            
                            {{-- Buscador --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Buscar Estudiante</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="search_query"
                                        class="block w-full pl-10 pr-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Nombre, Matrícula o ID..."
                                    >
                                    
                                    {{-- Resultados de Búsqueda --}}
                                    @if(count($student_results) > 0 && !$student)
                                        <div class="absolute top-full left-0 w-full mt-2 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden">
                                            <ul class="max-h-60 overflow-y-auto">
                                                @foreach($student_results as $result)
                                                    <li 
                                                        wire:click="selectStudent({{ $result->id }})"
                                                        class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-0 border-gray-50 transition-colors flex items-center gap-3"
                                                    >
                                                        <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                            {{ substr($result->first_name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-semibold text-gray-900">{{ $result->first_name }} {{ $result->last_name }}</div>
                                                            <div class="text-xs text-gray-500">ID: {{ $result->id_number }}</div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Tarjeta de Estudiante Seleccionado --}}
                            @if($student)
                                <div class="flex-1 bg-white rounded-xl border border-gray-200 p-6 flex flex-col items-center shadow-sm relative group animate-fade-in-up">
                                    <button 
                                        wire:click="clearStudent"
                                        class="absolute top-3 right-3 text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100"
                                        title="Desvincular Cliente"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <div class="h-20 w-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold mb-4">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    
                                    <h2 class="text-xl font-bold text-gray-900 text-center">{{ $student->first_name }} {{ $student->last_name }}</h2>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                                        {{ $student->id_number }}
                                    </span>

                                    <div class="w-full mt-8 space-y-4">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm text-gray-500">Email</span>
                                            <span class="text-sm font-medium text-gray-900 truncate max-w-[150px]" title="{{ $student->email }}">{{ $student->email }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm text-gray-500">Teléfono</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $student->mobile_phone ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Placeholder Vacío --}}
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-gray-200 rounded-xl">
                                    <div class="text-gray-300 mb-3">
                                        <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-sm font-medium">No se ha seleccionado ningún estudiante.</p>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- 
                        COLUMNA DERECHA: ACCIÓN (PAGO)
                        Ancho: 8/12 (67%) en pantallas grandes.
                        Fondo: Blanco puro.
                    --}}
                    <div class="col-span-12 lg:col-span-8 bg-white flex flex-col relative">
                        
                        {{-- Botón cerrar en esquina superior --}}
                        <button wire:click="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 z-10">
                            <span class="text-2xl">&times;</span>
                        </button>

                        {{-- Encabezado Columna Derecha --}}
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $payment_id ? 'Editar Pago' : 'Nuevo Pago' }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Complete los detalles de la transacción.</p>
                            </div>
                        </div>

                        {{-- Cuerpo Columna Derecha (Formulario) --}}
                        <div class="p-8 flex-1 overflow-y-auto relative">
                            
                            {{-- Capa de bloqueo si no hay estudiante --}}
                            <div class="absolute inset-0 bg-white/80 z-20 flex items-center justify-center backdrop-blur-[1px] transition-opacity duration-300"
                                 x-show="!$wire.student_id"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:leave="ease-in duration-200"
                            >
                                <div class="bg-white shadow-lg rounded-full px-6 py-3 border border-gray-100 flex items-center gap-3">
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                    </span>
                                    <span class="text-sm font-medium text-gray-600">Seleccione un estudiante primero</span>
                                </div>
                            </div>

                            <form wire:submit.prevent="savePayment" class="space-y-8">
                                
                                {{-- 1. SELECCIÓN DE PAGO PENDIENTE --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="p-1.5 bg-yellow-100 rounded-full text-yellow-700">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <label class="text-sm font-bold text-yellow-900 uppercase">Cobros Pendientes</label>
                                        </div>
                                        <select 
                                            wire:model.live="enrollment_id" 
                                            class="block w-full py-3 pl-4 pr-10 bg-white border border-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent rounded-lg text-gray-700 text-sm shadow-sm"
                                        >
                                            <option value="">-- Seleccionar Pago Pendiente (Opcional) --</option>
                                            @foreach($studentEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->name ?? 'Módulo' }} 
                                                    ({{ $enrollment->courseSchedule->section_name ?? 'Sec' }})
                                                    — Monto: ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- 2. DETALLES FINANCIEROS (Concepto, Monto, Estado) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    
                                    {{-- Lado Izquierdo: Configuración --}}
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Concepto</label>
                                            <select 
                                                wire:model.live="payment_concept_id" 
                                                class="block w-full py-3 px-4 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow {{ $isConceptDisabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'text-gray-900' }}"
                                                {{ $isConceptDisabled ? 'disabled' : '' }}
                                            >
                                                <option value="">Seleccione concepto...</option>
                                                @foreach($payment_concepts as $concept)
                                                    <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('payment_concept_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Estado del Pago</label>
                                            <select 
                                                wire:model="status" 
                                                class="block w-full py-3 px-4 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow text-gray-900"
                                            >
                                                <option value="Completado">Completado</option>
                                                <option value="Pendiente">Pendiente</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Lado Derecho: El Gran Monto --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto a Cobrar (DOP)</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-400 text-3xl font-light group-focus-within:text-blue-500 transition-colors">$</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live="amount" 
                                                class="block w-full pl-10 pr-4 py-6 bg-gray-50 border-2 border-gray-200 text-4xl font-bold text-gray-900 rounded-xl focus:ring-0 focus:border-blue-500 focus:bg-white text-right shadow-sm transition-all placeholder-gray-300 {{ $isAmountDisabled ? 'cursor-not-allowed opacity-75' : '' }}"
                                                placeholder="0.00"
                                                {{ $isAmountDisabled ? 'readonly' : '' }}
                                            >
                                        </div>
                                        @error('amount') <span class="text-xs text-red-500 mt-1 block text-right">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <hr class="border-gray-100">

                                {{-- 3. MÉTODO DE PAGO Y EJECUCIÓN --}}
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Método de Pago</label>
                                        <div class="grid grid-cols-4 gap-4">
                                            @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                                <label class="cursor-pointer relative">
                                                    <input type="radio" wire:model.live="gateway" value="{{ $method }}" class="peer sr-only">
                                                    <div class="w-full py-3 px-2 text-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:shadow-md transition-all select-none">
                                                        {{ $method }}
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Panel Dinámico: Caja Registradora --}}
                                    <div class="min-h-[120px]">
                                        {{-- Caso Efectivo --}}
                                        <div x-show="$wire.gateway === 'Efectivo'" x-transition class="bg-gray-50 rounded-xl p-6 border border-gray-200 flex flex-col md:flex-row items-center gap-6">
                                            <div class="w-full md:w-1/2">
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Efectivo Recibido</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        wire:model.live="cash_received" 
                                                        class="w-full pl-8 pr-4 py-3 text-lg font-bold text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-shadow"
                                                        placeholder="0.00"
                                                    >
                                                </div>
                                            </div>
                                            
                                            <div class="hidden md:block w-px h-12 bg-gray-300"></div>

                                            <div class="w-full md:w-1/2 text-center md:text-left">
                                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Devuelta / Cambio</span>
                                                <span class="block text-3xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                                                    ${{ number_format($change_amount, 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Caso Transferencia/Tarjeta --}}
                                        <div x-show="$wire.gateway !== 'Efectivo'" x-transition class="pt-2">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación / Lote' : 'Referencia / Comprobante'"></span>
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="transaction_id" 
                                                class="block w-full py-3 px-4 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-shadow"
                                                placeholder="Ingrese el código de referencia..."
                                            >
                                            @error('transaction_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- Footer Columna Derecha --}}
                        <div class="p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-4 rounded-br-xl">
                            <button 
                                type="button" 
                                wire:click="closeModal"
                                class="px-6 py-3 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 shadow-sm transition-colors"
                            >
                                Cancelar
                            </button>
                            
                            <button 
                                type="button"
                                wire:click="savePayment"
                                wire:loading.attr="disabled"
                                class="inline-flex justify-center items-center px-8 py-3 text-sm font-bold text-white bg-blue-600 border border-transparent rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-95"
                            >
                                <span wire:loading wire:target="savePayment" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ $payment_id ? 'Actualizar Pago' : 'Procesar Pago' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>