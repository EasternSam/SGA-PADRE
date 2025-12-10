<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP (Fondo Oscuro)
        Usamos estilo inline para asegurar la opacidad al 100% sin depender de la compilación de Tailwind.
        Esto arregla el problema de que 'no se oscurece'.
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
        style="background-color: rgba(17, 24, 39, 0.85); backdrop-filter: blur(4px);"
        aria-hidden="true"
    ></div>

    {{-- WRAPPER DE POSICIONAMIENTO --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-hidden" 
    >
        <div class="flex min-h-full items-center justify-center p-4">
            
            {{-- 
                PANEL DEL MODAL 
                - Max-w-7xl: Muy ancho.
                - h-[85vh]: Ocupa el 85% de la altura de la pantalla (evita scroll de ventana).
                - Flex col: Para cabecera fija y cuerpo flexible.
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
                class="relative w-full max-w-7xl h-[85vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-gray-700/50"
            >
                
                {{-- HEADER (Barra Superior Fija) --}}
                <div class="h-16 px-6 bg-white border-b border-gray-100 flex items-center justify-between shrink-0 z-20 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 bg-gradient-to-br from-indigo-600 to-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-200">
                            <i class="fas fa-cash-register text-white text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 tracking-tight leading-none">Terminal de Caja</h2>
                            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mt-0.5">Nueva Transacción</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="h-8 w-8 rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- CONTENIDO PRINCIPAL (Grid 3 Columnas) --}}
                <div class="flex-1 grid grid-cols-12 overflow-hidden bg-gray-50/50">
                    
                    {{-- 
                        COLUMNA 1: BÚSQUEDA Y LISTA (Ancho: 25%)
                        Propósito: Encontrar al estudiante rápidamente.
                    --}}
                    <div class="col-span-12 lg:col-span-3 bg-white border-r border-gray-200 flex flex-col h-full z-10">
                        <!-- Buscador Fijo -->
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Buscar Cliente</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm placeholder-gray-400"
                                    placeholder="Nombre o ID..."
                                    autofocus
                                >
                            </div>
                        </div>
                        
                        <!-- Lista Scrollable -->
                        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                            @if(count($student_results) > 0)
                                @foreach($student_results as $result)
                                    <div 
                                        wire:click="selectStudent({{ $result->id }})"
                                        class="p-3 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-indigo-50 hover:border-indigo-100 group flex items-center gap-3 {{ $student && $student->id === $result->id ? 'bg-indigo-600 text-white shadow-md transform scale-[1.02]' : 'bg-white text-gray-600' }}"
                                    >
                                        <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $student && $student->id === $result->id ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-600' }}">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden min-w-0">
                                            <h4 class="text-sm font-bold truncate {{ $student && $student->id === $result->id ? 'text-white' : 'text-gray-700' }}">{{ $result->first_name }} {{ $result->last_name }}</h4>
                                            <p class="text-[10px] truncate {{ $student && $student->id === $result->id ? 'text-indigo-200' : 'text-gray-400' }}">{{ $result->id_number }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif(strlen($search_query) > 2)
                                <div class="text-center py-12 text-gray-400">
                                    <i class="far fa-folder-open text-2xl mb-2 opacity-50"></i>
                                    <p class="text-xs">No encontrado</p>
                                </div>
                            @else
                                <div class="text-center py-12 text-gray-300">
                                    <i class="fas fa-search text-2xl mb-2 opacity-50"></i>
                                    <p class="text-xs">Resultados aquí</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: PERFIL DEL ESTUDIANTE (Ancho: 25%)
                        Propósito: Confirmación visual clara de a quién se cobra.
                    --}}
                    <div class="col-span-12 lg:col-span-3 border-r border-gray-200 flex flex-col items-center justify-center p-6 text-center bg-gray-50 relative overflow-hidden">
                        @if($student)
                            {{-- Botón Desvincular Flotante --}}
                            <button wire:click="clearStudent" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-all" title="Desvincular">
                                <i class="fas fa-user-minus"></i>
                            </button>

                            <div class="relative mb-5 animate-fade-in-up">
                                <div class="h-28 w-28 rounded-full bg-white p-1 shadow-xl ring-1 ring-gray-100">
                                    <div class="h-full w-full rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-3xl font-bold text-white shadow-inner">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="absolute bottom-1 right-1 h-5 w-5 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>

                            <div class="animate-fade-in-up" style="animation-delay: 100ms;">
                                <h3 class="text-xl font-black text-gray-800 leading-tight mb-1">{{ $student->first_name }}</h3>
                                <h4 class="text-lg font-medium text-gray-500 mb-3">{{ $student->last_name }}</h4>
                                
                                <span class="inline-block bg-white border border-gray-200 px-3 py-1 rounded-full text-indigo-600 font-bold text-xs mb-6 shadow-sm">
                                    {{ $student->id_number }}
                                </span>
                            </div>

                            <div class="w-full space-y-3 animate-fade-in-up" style="animation-delay: 200ms;">
                                <div class="bg-white p-3 rounded-xl text-left border border-gray-100 shadow-sm flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <i class="fas fa-envelope text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] text-gray-400 uppercase font-bold">Email</p>
                                        <p class="text-xs font-medium text-gray-800 truncate" title="{{ $student->email }}">{{ $student->email }}</p>
                                    </div>
                                </div>
                                <div class="bg-white p-3 rounded-xl text-left border border-gray-100 shadow-sm flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <i class="fas fa-phone text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase font-bold">Teléfono</p>
                                        <p class="text-xs font-medium text-gray-800">{{ $student->mobile_phone ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="opacity-40 flex flex-col items-center">
                                <div class="h-24 w-24 bg-gray-200 rounded-full mb-4 flex items-center justify-center">
                                    <i class="fas fa-user text-4xl text-gray-400"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-500">Seleccione un cliente<br>de la lista izquierda</p>
                            </div>
                        @endif
                    </div>

                    {{-- 
                        COLUMNA 3: FORMULARIO DE PAGO (Ancho: 50%)
                        Propósito: Espacio amplio para los inputs y cálculos.
                    --}}
                    <div class="col-span-12 lg:col-span-6 flex flex-col h-full bg-white relative">
                        
                        {{-- Bloqueo si no hay estudiante --}}
                        <div 
                            class="absolute inset-0 bg-white/80 backdrop-blur-[2px] z-30 flex flex-col items-center justify-center transition-opacity duration-300"
                            x-show="!$wire.student_id"
                        >
                            <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-100 text-center max-w-sm transform scale-100">
                                <div class="h-14 w-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                                    <i class="fas fa-arrow-left text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Terminal en Espera</h3>
                                <p class="text-sm text-gray-500 leading-relaxed">Seleccione un estudiante para habilitar la caja y procesar el pago.</p>
                            </div>
                        </div>

                        {{-- Cuerpo del Formulario (Scrollable) --}}
                        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                            <form wire:submit.prevent="savePayment" class="flex flex-col gap-8 h-full">
                                
                                {{-- 1. Deuda Pendiente (Si existe) --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-amber-50 rounded-xl p-1 border border-amber-200 animate-fade-in-down">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-amber-500"></i>
                                            </div>
                                            <select 
                                                wire:model.live="enrollment_id" 
                                                class="block w-full py-3 pl-10 pr-10 bg-transparent border-0 focus:ring-0 text-amber-900 font-bold text-sm cursor-pointer"
                                            >
                                                <option value="">-- Pagar Deuda Pendiente ({{ $studentEnrollments->count() }}) --</option>
                                                @foreach($studentEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->id }}">
                                                        {{ $enrollment->courseSchedule->module->name }} — RD$ {{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                {{-- 2. Datos Financieros (Grid 2 cols) --}}
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Concepto</label>
                                        <select 
                                            wire:model.live="payment_concept_id" 
                                            class="w-full h-12 px-4 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm"
                                            {{ $isConceptDisabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Seleccionar concepto...</option>
                                            @foreach($payment_concepts as $concept)
                                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_concept_id') <span class="text-xs text-red-500 mt-1 pl-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- 3. EL MONTO GIGANTE --}}
                                <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-colors">
                                    <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
                                        <i class="fas fa-coins text-6xl"></i>
                                    </div>
                                    
                                    <div class="flex justify-between items-center mb-1 relative z-10">
                                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total a Cobrar</label>
                                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[10px] font-bold rounded uppercase">DOP</span>
                                    </div>
                                    
                                    <div class="relative z-10 flex items-baseline">
                                        <span class="text-3xl text-gray-400 font-light mr-2">$</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live="amount" 
                                            class="w-full bg-transparent border-0 p-0 text-5xl font-black text-gray-900 placeholder-gray-200 focus:ring-0 leading-tight"
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                    @error('amount') <span class="text-xs text-red-500 mt-2 block font-bold relative z-10">{{ $message }}</span> @enderror
                                </div>

                                {{-- 4. Método de Pago --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Método de Pago</label>
                                    <div class="grid grid-cols-4 gap-3">
                                        @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                            <button 
                                                type="button"
                                                wire:click="$set('gateway', '{{ $method }}')"
                                                class="py-3 px-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all border {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200 transform scale-105' : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
                                            >
                                                {{ $method }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- 5. Panel Dinámico (Caja) --}}
                                <div class="flex-1 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 p-6 flex flex-col justify-center min-h-[140px]">
                                    
                                    {{-- CASO EFECTIVO --}}
                                    <div x-show="$wire.gateway === 'Efectivo'" class="space-y-5 animate-fade-in">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm font-bold text-gray-500 uppercase">Recibido:</label>
                                            <div class="relative w-40">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="cash_received" 
                                                    class="w-full pl-7 pr-3 py-2 text-xl font-bold text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-right shadow-sm"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                        </div>
                                        <div class="h-px bg-gray-200 w-full"></div>
                                        <div class="flex items-center justify-between">
                                            <label class="text-base font-black text-gray-700 uppercase">Devuelta:</label>
                                            <span class="text-4xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                                ${{ number_format($change_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- CASO OTROS --}}
                                    <div x-show="$wire.gateway !== 'Efectivo'" class="animate-fade-in">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                                            <span x-text="$wire.gateway === 'Tarjeta' ? 'Auth Code (Últimos 4)' : 'Referencia / Comprobante'"></span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="transaction_id" 
                                            class="w-full h-12 px-4 bg-white border border-gray-300 rounded-xl text-base focus:ring-2 focus:ring-indigo-500 shadow-sm placeholder-gray-300"
                                            placeholder="Ingrese referencia..."
                                        >
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- FOOTER FIJO --}}
                        <div class="p-6 bg-white border-t border-gray-100 flex items-center justify-between shrink-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                            
                            <div class="flex items-center gap-3">
                                <div class="bg-gray-100 rounded-lg p-1 flex">
                                    <button 
                                        wire:click="$set('status', 'Completado')" 
                                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all {{ $status === 'Completado' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}"
                                    >
                                        Pagado
                                    </button>
                                    <button 
                                        wire:click="$set('status', 'Pendiente')" 
                                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all {{ $status === 'Pendiente' ? 'bg-white text-yellow-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}"
                                    >
                                        Pendiente
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button 
                                    wire:click="closeModal"
                                    class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    wire:click="savePayment"
                                    wire:loading.attr="disabled"
                                    class="px-8 py-3 bg-gray-900 hover:bg-black text-white rounded-xl font-bold text-base shadow-lg shadow-gray-200 transition-all transform active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading wire:target="savePayment" class="animate-spin">
                                        <i class="fas fa-circle-notch"></i>
                                    </span>
                                    <span>Cobrar ${{ number_format($amount, 2) }}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>