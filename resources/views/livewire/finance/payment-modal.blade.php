{{-- 
Modal de Pago con Diseño Premium / Terminal POS
--}}
<div 
    x-data="{ show: $wire.entangle('show') }" 
    x-show="show" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    <!-- Backdrop con desenfoque -->
    <div 
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Contenedor del Modal -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <div 
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-gray-100"
                x-show="show"
                @click.away="show = false"
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
            >
                
                <!-- Header con Degradado -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 flex justify-between items-center relative overflow-hidden">
                    <!-- Decoración de fondo sutil -->
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                            <i class="fas fa-cash-register text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white tracking-tight">
                                {{ $payment_id ? 'Editar Transacción' : 'Terminal de Pagos' }}
                            </h3>
                            <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">Sistema de Caja</p>
                        </div>
                    </div>

                    <button @click="show = false" class="text-indigo-100 hover:text-white hover:bg-white/10 rounded-full p-2 transition-colors relative z-10">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row h-full">
                    
                    <!-- COLUMNA IZQUIERDA: CLIENTE Y BÚSQUEDA (Fondo Gris Claro) -->
                    <div class="w-full lg:w-2/5 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200 p-6 flex flex-col">
                        
                        <!-- Barra de Búsqueda (Siempre visible para cambios rápidos) -->
                        <div class="relative mb-6 group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-600">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search_query"
                                placeholder="Buscar estudiante por nombre o ID..." 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white transition-shadow focus:shadow-md"
                            >
                            
                            <!-- Dropdown de Resultados Flotante -->
                            @if(count($student_results) > 0 && !$student)
                                <ul class="absolute z-20 mt-2 w-full bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden max-h-80 overflow-y-auto transform origin-top transition-all">
                                    <li class="px-4 py-2 text-xs font-semibold text-gray-400 bg-gray-50 uppercase tracking-wider">Resultados</li>
                                    @foreach($student_results as $result)
                                        <li 
                                            wire:click="selectStudent({{ $result->id }})"
                                            class="cursor-pointer px-4 py-3 hover:bg-indigo-50 transition-colors border-b last:border-0 border-gray-100 flex items-center gap-3"
                                        >
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                                {{ substr($result->first_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 text-sm">{{ $result->first_name }} {{ $result->last_name }}</p>
                                                <p class="text-xs text-gray-500">ID: {{ $result->id_number }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <!-- Tarjeta de Estudiante (Estado Seleccionado) -->
                        @if($student)
                            <div class="flex-1 flex flex-col items-center justify-center p-6 bg-white rounded-2xl border-2 border-indigo-50 shadow-sm relative overflow-hidden group">
                                <!-- Botón Quitar -->
                                <button 
                                    wire:click="clearStudent" 
                                    class="absolute top-3 right-3 text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-all opacity-0 group-hover:opacity-100"
                                    title="Cambiar estudiante"
                                >
                                    <i class="fas fa-times"></i>
                                </button>

                                <div class="relative">
                                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 p-1 shadow-lg mb-4">
                                        <div class="h-full w-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                                            <span class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-br from-indigo-600 to-purple-600">
                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Badge de Estado -->
                                    <div class="absolute bottom-4 right-0 h-6 w-6 bg-green-400 border-4 border-white rounded-full"></div>
                                </div>

                                <h4 class="text-xl font-bold text-gray-900 text-center">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                <p class="text-sm font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full mt-2">{{ $student->id_number }}</p>
                                
                                <div class="mt-6 w-full space-y-3">
                                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                                        <span class="text-gray-500">Email</span>
                                        <span class="font-medium text-gray-800">{{ Str::limit($student->email, 20) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                                        <span class="text-gray-500">Teléfono</span>
                                        <span class="font-medium text-gray-800">{{ $student->mobile_phone ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Estado Vacío -->
                            <div class="flex-1 flex flex-col items-center justify-center text-center p-8 opacity-50">
                                <div class="h-24 w-24 bg-gray-200 rounded-full mb-4 flex items-center justify-center">
                                    <i class="fas fa-user text-4xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Seleccione un estudiante para comenzar la transacción.</p>
                            </div>
                        @endif
                    </div>

                    <!-- COLUMNA DERECHA: FORMULARIO DE PAGO (Fondo Blanco) -->
                    <div class="w-full lg:w-3/5 p-6 lg:p-8 flex flex-col">
                        
                        <div class="flex-1 {{ !$student ? 'opacity-40 pointer-events-none grayscale' : '' }} transition-all duration-300">
                            
                            <!-- Alertas de Error -->
                            @if ($errors->any())
                                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm animate-pulse">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">Atención necesaria</h3>
                                            <div class="mt-1 text-sm text-red-700">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <form wire:submit.prevent="savePayment">
                                
                                <!-- Sección: Pagos Pendientes (Destacada si existe) -->
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="mb-6 bg-amber-50 rounded-xl p-4 border border-amber-200 shadow-sm">
                                        <label class="flex items-center text-sm font-bold text-amber-800 mb-2">
                                            <i class="fas fa-file-invoice-dollar mr-2"></i> Cobros Pendientes
                                        </label>
                                        <div class="relative">
                                            <select 
                                                wire:model.live="enrollment_id" 
                                                class="block w-full pl-3 pr-10 py-3 text-base border-amber-300 focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm rounded-lg bg-white"
                                            >
                                                <option value="">-- Seleccionar Pago Pendiente --</option>
                                                @foreach($studentEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->id }}">
                                                        {{ $enrollment->courseSchedule->module->name ?? 'Módulo' }} 
                                                        ({{ $enrollment->courseSchedule->section_name ?? 'Sec' }})
                                                        — ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <!-- Concepto -->
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto de Pago</label>
                                        <div class="relative">
                                            <select 
                                                wire:model.live="payment_concept_id" 
                                                class="block w-full pl-3 pr-10 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors"
                                                {{ $isConceptDisabled ? 'disabled' : '' }}
                                            >
                                                <option value="">Seleccione concepto...</option>
                                                @foreach($payment_concepts as $concept)
                                                    <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                                        <select 
                                            wire:model="status" 
                                            class="block w-full py-3 px-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors"
                                        >
                                            <option value="Completado">Completado</option>
                                            <option value="Pendiente">Pendiente</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- TARJETA DE MONTO (Central) -->
                                <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100 mb-6">
                                    <div class="flex items-end justify-between mb-2">
                                        <label class="text-sm font-bold text-indigo-900 uppercase tracking-wide">Monto a Cobrar</label>
                                        <span class="text-xs font-semibold text-indigo-400 bg-white px-2 py-1 rounded border border-indigo-100">DOP</span>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 text-2xl font-light">$</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live="amount" 
                                            class="block w-full pl-10 pr-4 py-3 bg-white border-2 border-indigo-100 text-3xl font-bold text-indigo-900 rounded-xl focus:ring-0 focus:border-indigo-500 text-right shadow-sm placeholder-gray-200 transition-all"
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                </div>

                                <!-- Método de Pago -->
                                <div class="mb-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Método de Pago</label>
                                    <div class="grid grid-cols-4 gap-3">
                                        @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                            <label class="cursor-pointer relative">
                                                <input type="radio" wire:model.live="gateway" value="{{ $method }}" class="peer sr-only">
                                                <div class="w-full py-3 px-2 text-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white transition-all shadow-sm">
                                                    {{ $method }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- DETALLES ESPECÍFICOS DEL MÉTODO -->
                                <div x-show="$wire.gateway === 'Efectivo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-5 rounded-xl border border-gray-200 shadow-inner">
                                        <div class="flex justify-between items-center mb-4">
                                            <label class="text-sm font-bold text-gray-600 uppercase">Efectivo Recibido</label>
                                            <div class="relative w-1/2">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="cash_received" 
                                                    class="w-full pl-7 pr-3 py-2 text-right text-lg font-bold text-gray-800 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center pt-4 border-t border-gray-200/60">
                                            <span class="text-base font-bold text-gray-700">DEVUELTA / CAMBIO</span>
                                            <span class="text-3xl font-extrabold {{ $change_amount < 0 ? 'text-red-500' : 'text-emerald-600' }} drop-shadow-sm">
                                                RD$ {{ number_format($change_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="$wire.gateway !== 'Efectivo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación / Lote' : 'Número de Referencia / Comprobante'"></span>
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="transaction_id" 
                                        class="block w-full py-3 px-4 bg-white border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm shadow-sm"
                                        placeholder="Ingrese el identificador de la transacción..."
                                    >
                                </div>

                            </form>
                        </div>

                        <!-- Footer / Botones -->
                        <div class="mt-auto pt-8 flex items-center justify-between border-t border-gray-100 gap-4">
                            <button 
                                type="button" 
                                wire:click="closeModal"
                                class="px-6 py-3 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors"
                            >
                                Cancelar
                            </button>
                            
                            <button 
                                type="submit"
                                wire:click="savePayment"
                                wire:loading.attr="disabled"
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-95"
                            >
                                <span wire:loading wire:target="savePayment" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="savePayment">
                                    <i class="fas fa-check-circle mr-2"></i> {{ $payment_id ? 'Actualizar Pago' : 'Procesar Pago' }}
                                </span>
                                <span wire:loading wire:target="savePayment">Procesando...</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>