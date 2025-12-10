<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP
        Fondo oscuro sólido para enfoque total.
    --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 transition-opacity"
        style="background-color: rgba(10, 10, 15, 0.95);" {{-- Muy oscuro para contraste --}}
        aria-hidden="true"
    ></div>

    {{-- WRAPPER --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-hidden flex items-center justify-center p-4" 
    >
        {{-- 
            PANEL PRINCIPAL
            Diseño: Flex Row (Sidebar + Main)
            Tamaño: Casi toda la pantalla (w-full max-w-[95%] h-[90%])
        --}}
        <div 
            x-show="show"
            x-trap.noscroll="show"
            @click.away="show = false"
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            x-transition:leave="ease-in duration-200" 
            x-transition:leave-start="opacity-100 scale-100" 
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white w-full max-w-[96rem] h-[90vh] rounded-xl shadow-2xl flex overflow-hidden border border-gray-800/10"
        >
            
            {{-- ============================================================== --}}
            {{-- ZONA 1: SIDEBAR (Buscador y Lista) - Ancho Fijo --}}
            {{-- ============================================================== --}}
            <div class="w-80 flex-shrink-0 bg-gray-50 border-r border-gray-200 flex flex-col h-full">
                
                {{-- Cabecera del Sidebar --}}
                <div class="p-4 border-b border-gray-200 bg-white">
                    <div class="flex items-center gap-2 mb-4 text-indigo-600">
                        <i class="fas fa-cash-register text-xl"></i>
                        <span class="font-bold text-lg tracking-tight text-gray-900">Caja POS</span>
                    </div>
                    
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search_query"
                            class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Buscar alumno..."
                            autofocus
                        >
                    </div>
                </div>

                {{-- Lista Scrollable --}}
                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    @if(count($student_results) > 0)
                        @foreach($student_results as $result)
                            <button 
                                wire:click="selectStudent({{ $result->id }})"
                                class="w-full text-left p-3 rounded-lg flex items-center gap-3 transition-all {{ $student && $student->id === $result->id ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-white hover:shadow-sm text-gray-700' }}"
                            >
                                <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $student && $student->id === $result->id ? 'bg-white/20' : 'bg-gray-200 text-gray-600' }}">
                                    {{ substr($result->first_name, 0, 1) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="font-bold text-sm truncate">{{ $result->first_name }} {{ $result->last_name }}</div>
                                    <div class="text-[10px] opacity-80 truncate">{{ $result->id_number }}</div>
                                </div>
                            </button>
                        @endforeach
                    @elseif(strlen($search_query) > 2)
                        <div class="text-center py-8 text-gray-400 text-xs">Sin resultados</div>
                    @else
                        <div class="text-center py-8 text-gray-400 text-xs">Ingrese nombre o ID</div>
                    @endif
                </div>

                {{-- Footer Sidebar (Botón Salir) --}}
                <div class="p-3 border-t border-gray-200 bg-gray-100">
                    <button wire:click="closeModal" class="w-full py-2 text-xs font-bold text-gray-500 hover:text-gray-800 hover:bg-gray-200 rounded transition-colors uppercase tracking-wide">
                        Cerrar Terminal
                    </button>
                </div>
            </div>


            {{-- ============================================================== --}}
            {{-- ZONA 2: MAIN WORKSPACE (Formulario) --}}
            {{-- ============================================================== --}}
            <div class="flex-1 flex flex-col h-full relative bg-white">
                
                {{-- Overlay de Bloqueo (Si no hay estudiante) --}}
                <div 
                    x-show="!$wire.student_id"
                    class="absolute inset-0 bg-white/90 backdrop-blur-sm z-30 flex flex-col items-center justify-center text-center transition-opacity"
                >
                    <div class="bg-gray-50 p-8 rounded-2xl border-2 border-dashed border-gray-300 max-w-md">
                        <div class="h-16 w-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-plus text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Seleccione un Cliente</h3>
                        <p class="text-sm text-gray-500">Utilice el buscador de la izquierda para seleccionar al estudiante y habilitar el terminal de cobro.</p>
                    </div>
                </div>

                {{-- BANNER DE ESTUDIANTE (Header del Main) --}}
                <div class="h-20 px-6 bg-white border-b border-gray-200 flex items-center justify-between shrink-0">
                    @if($student)
                        <div class="flex items-center gap-4 animate-fade-in-left">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-800 leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h2>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-700 font-bold border border-gray-200">{{ $student->id_number }}</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-envelope"></i> {{ $student->email }}</span>
                                </div>
                            </div>
                        </div>
                        <button wire:click="clearStudent" class="text-gray-400 hover:text-red-500 text-sm font-medium transition-colors" title="Cambiar Cliente">
                            <i class="fas fa-times mr-1"></i> Desvincular
                        </button>
                    @endif
                </div>

                {{-- CUERPO DEL FORMULARIO (Dos Grandes Columnas) --}}
                <div class="flex-1 p-6 overflow-hidden">
                    <form wire:submit.prevent="savePayment" class="h-full flex flex-col gap-6">
                        
                        {{-- Fila Superior: Alerta de Deuda --}}
                        @if($studentEnrollments && $studentEnrollments->count() > 0)
                            <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r shadow-sm flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle text-amber-500"></i>
                                    <span class="text-sm font-bold text-amber-800">Pagos Pendientes Disponibles:</span>
                                </div>
                                <select 
                                    wire:model.live="enrollment_id" 
                                    class="text-sm border-amber-300 rounded focus:ring-amber-500 focus:border-amber-500 bg-white py-1 pl-2 pr-8"
                                >
                                    <option value="">-- Seleccionar para autocompletar --</option>
                                    @foreach($studentEnrollments as $enrollment)
                                        <option value="{{ $enrollment->id }}">
                                            {{ $enrollment->courseSchedule->module->name }} (${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- AREA DE TRABAJO (Grid 2 Cols) --}}
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-8 h-full min-h-0">
                            
                            {{-- COLUMNA IZQUIERDA: DETALLES --}}
                            <div class="flex flex-col gap-5 h-full overflow-y-auto pr-2 custom-scrollbar">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Detalles de la Transacción</h4>
                                
                                {{-- Input Concepto --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Concepto de Pago</label>
                                    <select 
                                        wire:model.live="payment_concept_id" 
                                        class="w-full h-12 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors {{ $isConceptDisabled ? 'opacity-60 cursor-not-allowed' : '' }}"
                                        {{ $isConceptDisabled ? 'disabled' : '' }}
                                    >
                                        <option value="">Seleccione...</option>
                                        @foreach($payment_concepts as $concept)
                                            <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Input Monto (Gigante) --}}
                                <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100">
                                    <label class="block text-xs font-bold text-indigo-800 uppercase mb-2">Total a Cobrar (DOP)</label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-0 -translate-y-1/2 text-3xl text-indigo-300 ml-2">$</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live="amount" 
                                            class="w-full bg-transparent border-0 text-5xl font-black text-indigo-900 placeholder-indigo-200 focus:ring-0 p-0 pl-8"
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                </div>

                                {{-- Input Estado --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Estado del Pago</label>
                                    <div class="flex rounded-lg bg-gray-100 p-1">
                                        <button type="button" wire:click="$set('status', 'Completado')" class="flex-1 py-2 text-sm font-bold rounded shadow-sm transition-all {{ $status === 'Completado' ? 'bg-white text-green-700' : 'text-gray-500 hover:text-gray-700' }}">Completado</button>
                                        <button type="button" wire:click="$set('status', 'Pendiente')" class="flex-1 py-2 text-sm font-bold rounded shadow-sm transition-all {{ $status === 'Pendiente' ? 'bg-white text-yellow-700' : 'text-gray-500 hover:text-gray-700' }}">Pendiente</button>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMNA DERECHA: MÉTODO Y CAJA --}}
                            <div class="flex flex-col gap-5 h-full border-l border-gray-100 pl-8">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Método y Ejecución</h4>

                                {{-- Tabs de Método --}}
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                        <button 
                                            type="button"
                                            wire:click="$set('gateway', '{{ $method }}')"
                                            class="h-14 rounded-lg border font-bold text-sm transition-all flex items-center justify-center gap-2 {{ $gateway === $method ? 'border-indigo-600 bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50' }}"
                                        >
                                            <i class="fas {{ $method === 'Efectivo' ? 'fa-money-bill-wave' : ($method === 'Tarjeta' ? 'fa-credit-card' : ($method === 'Transferencia' ? 'fa-university' : 'fa-receipt')) }}"></i>
                                            {{ $method }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Panel Dinámico (Caja) --}}
                                <div class="flex-1 bg-gray-50 rounded-xl border border-gray-200 p-5 flex flex-col justify-center">
                                    
                                    {{-- Vista Efectivo --}}
                                    <div x-show="$wire.gateway === 'Efectivo'" class="space-y-4 animate-fade-in">
                                        <div class="flex justify-between items-center">
                                            <label class="font-bold text-gray-600">Recibido:</label>
                                            <div class="w-1/2 relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="cash_received" 
                                                    class="w-full pl-6 pr-3 py-2 text-right font-bold text-lg border-gray-300 rounded focus:ring-green-500 focus:border-green-500"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4 flex justify-between items-end">
                                            <label class="font-bold text-gray-500 text-sm uppercase">Cambio:</label>
                                            <div class="text-4xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                                ${{ number_format($change_amount, 2) }}
                                            </div>
                                        </div>
                                        @error('cash_received') <p class="text-red-500 text-xs text-right">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Vista Referencia --}}
                                    <div x-show="$wire.gateway !== 'Efectivo'" class="animate-fade-in">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                            <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación (Auth)' : 'Referencia de Transacción'"></span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="transaction_id" 
                                            class="w-full py-3 px-4 border border-gray-300 rounded-lg text-lg focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Ingrese código..."
                                        >
                                        @error('transaction_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                {{-- BARRA INFERIOR (Acciones) --}}
                <div class="h-20 px-8 bg-white border-t border-gray-200 flex items-center justify-end gap-4 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
                    <button 
                        wire:click="closeModal"
                        class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors"
                    >
                        Cancelar
                    </button>
                    
                    <button 
                        wire:click="savePayment"
                        wire:loading.attr="disabled"
                        class="px-8 py-3 bg-gray-900 hover:bg-black text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all transform active:scale-95 flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed min-w-[200px] justify-center"
                    >
                        <span wire:loading wire:target="savePayment" class="animate-spin text-white">
                            <i class="fas fa-circle-notch"></i>
                        </span>
                        <span wire:loading.remove wire:target="savePayment">
                            PROCESAR COBRO
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>