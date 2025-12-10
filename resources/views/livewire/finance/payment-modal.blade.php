<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP (Fondo Oscuro)
        Usamos estilo inline para asegurar la opacidad sin depender de la compilación JIT de Tailwind si falla.
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
                Dimensiones: Ancho máximo 7XL y Altura fija del 85% de la pantalla (h-[85vh])
                para evitar scrolls externos.
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
                class="relative w-full max-w-7xl h-[85vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
            >
                
                {{-- HEADER (Barra Superior) --}}
                <div class="h-16 px-8 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-200">
                            <i class="fas fa-cash-register text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Terminal de Caja</h2>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Nueva Transacción</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="h-10 w-10 rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- CONTENIDO PRINCIPAL (Grid 3 Columnas) --}}
                <div class="flex-1 grid grid-cols-12 overflow-hidden">
                    
                    {{-- 
                        COLUMNA 1: BÚSQUEDA Y LISTA (25%)
                        Fondo: Gris muy claro
                    --}}
                    <div class="col-span-12 lg:col-span-3 bg-gray-50 border-r border-gray-200 flex flex-col h-full">
                        <div class="p-5 border-b border-gray-200 bg-white/50">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Buscar Cliente</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm"
                                    placeholder="Nombre o ID..."
                                    autofocus
                                >
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            @if(count($student_results) > 0)
                                @foreach($student_results as $result)
                                    <div 
                                        wire:click="selectStudent({{ $result->id }})"
                                        class="p-3 rounded-xl cursor-pointer transition-all border border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm group flex items-center gap-3 {{ $student && $student->id === $result->id ? 'bg-indigo-50 border-indigo-200 shadow-sm' : '' }}"
                                    >
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="text-sm font-bold text-gray-700 truncate group-hover:text-indigo-700">{{ $result->first_name }} {{ $result->last_name }}</h4>
                                            <p class="text-xs text-gray-400 truncate">{{ $result->id_number }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif(strlen($search_query) > 2)
                                <div class="text-center py-10 text-gray-400">
                                    <p class="text-sm">No se encontraron resultados.</p>
                                </div>
                            @else
                                <div class="text-center py-10 text-gray-400 opacity-60">
                                    <i class="fas fa-search text-3xl mb-3"></i>
                                    <p class="text-xs">Escriba para buscar...</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: PERFIL DEL ESTUDIANTE (25%)
                        Fondo: Blanco con detalles visuales
                    --}}
                    <div class="col-span-12 lg:col-span-3 border-r border-gray-100 flex flex-col items-center justify-center p-8 text-center bg-white relative overflow-hidden">
                        @if($student)
                            {{-- Decoración de fondo --}}
                            <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-indigo-50 to-transparent"></div>
                            
                            <button wire:click="clearStudent" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors z-10" title="Desvincular">
                                <i class="fas fa-unlink"></i>
                            </button>

                            <div class="relative mb-6">
                                <div class="h-32 w-32 rounded-full bg-white p-1 shadow-xl ring-4 ring-indigo-50">
                                    <div class="h-full w-full rounded-full bg-indigo-600 flex items-center justify-center text-4xl font-bold text-white">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="absolute bottom-2 right-2 h-6 w-6 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>

                            <h3 class="text-2xl font-black text-gray-800 leading-tight mb-1">{{ $student->first_name }}</h3>
                            <h4 class="text-xl font-medium text-gray-500 mb-4">{{ $student->last_name }}</h4>
                            
                            <div class="bg-indigo-50 px-4 py-2 rounded-full text-indigo-700 font-bold text-sm mb-8">
                                {{ $student->id_number }}
                            </div>

                            <div class="w-full space-y-4">
                                <div class="bg-gray-50 p-4 rounded-xl text-left border border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Email</p>
                                    <p class="text-sm font-medium text-gray-700 truncate" title="{{ $student->email }}">{{ $student->email }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-xl text-left border border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Teléfono</p>
                                    <p class="text-sm font-medium text-gray-700">{{ $student->mobile_phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="opacity-30">
                                <i class="fas fa-id-card text-8xl text-gray-300 mb-6"></i>
                                <p class="text-lg font-medium text-gray-400">Seleccione un estudiante<br>para ver su perfil.</p>
                            </div>
                        @endif
                    </div>

                    {{-- 
                        COLUMNA 3: FORMULARIO DE PAGO (50%)
                        Fondo: Blanco, Espacioso, Inputs Grandes
                    --}}
                    <div class="col-span-12 lg:col-span-6 flex flex-col h-full bg-white relative">
                        
                        {{-- Bloqueo si no hay estudiante --}}
                        <div 
                            class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex flex-col items-center justify-center transition-opacity duration-300"
                            x-show="!$wire.student_id"
                        >
                            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 text-center max-w-sm">
                                <div class="h-12 w-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                                    <i class="fas fa-arrow-left text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Esperando Cliente</h3>
                                <p class="text-sm text-gray-500">Por favor, busque y seleccione un estudiante en el panel izquierdo para comenzar el cobro.</p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-8">
                            <form wire:submit.prevent="savePayment" class="h-full flex flex-col gap-8">
                                
                                {{-- 1. Selector de Deuda Pendiente --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-amber-50 p-1 rounded-xl border border-amber-200">
                                        <select 
                                            wire:model.live="enrollment_id" 
                                            class="block w-full py-3 px-4 bg-transparent border-0 focus:ring-0 text-amber-900 font-medium text-sm cursor-pointer"
                                        >
                                            <option value="">-- Ver pagos pendientes disponibles ({{ $studentEnrollments->count() }}) --</option>
                                            @foreach($studentEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    Pagar: {{ $enrollment->courseSchedule->module->name }} - ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- 2. Configuración del Pago --}}
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Concepto de la Transacción</label>
                                        <select 
                                            wire:model.live="payment_concept_id" 
                                            class="w-full h-12 px-4 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                                            {{ $isConceptDisabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Seleccionar concepto...</option>
                                            @foreach($payment_concepts as $concept)
                                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_concept_id') <span class="text-xs text-red-500 mt-1 pl-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- 3. EL MONTO GIGANTE --}}
                                <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-bold text-indigo-900 uppercase">Total a Cobrar</label>
                                        @if($amount > 0)
                                            <span class="px-2 py-1 bg-indigo-200 text-indigo-800 text-xs font-bold rounded">DOP</span>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 text-4xl text-indigo-300 font-light ml-4">$</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live="amount" 
                                            class="w-full bg-white border-0 rounded-xl py-4 pl-12 pr-4 text-right text-5xl font-black text-indigo-900 placeholder-indigo-200 focus:ring-4 focus:ring-indigo-200/50 shadow-sm"
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                    @error('amount') <span class="text-xs text-red-500 mt-2 block text-right font-bold">{{ $message }}</span> @enderror
                                </div>

                                {{-- 4. Método de Pago (Tabs Grandes) --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-3">Método de Pago</label>
                                    <div class="grid grid-cols-4 gap-2 bg-gray-100 p-1.5 rounded-xl">
                                        @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                            <button 
                                                type="button"
                                                wire:click="$set('gateway', '{{ $method }}')"
                                                class="py-3 rounded-lg text-sm font-bold transition-all {{ $gateway === $method ? 'bg-white text-indigo-600 shadow-md transform scale-[1.02]' : 'text-gray-500 hover:text-gray-700' }}"
                                            >
                                                {{ $method }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- 5. Panel Dinámico (Caja / Referencia) --}}
                                <div class="flex-1 bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-200 flex flex-col justify-center">
                                    
                                    {{-- CASO EFECTIVO --}}
                                    <div x-show="$wire.gateway === 'Efectivo'" class="space-y-6">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm font-bold text-gray-500 uppercase">Recibido:</label>
                                            <div class="relative w-1/2">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    wire:model.live="cash_received" 
                                                    class="w-full pl-8 pr-4 py-3 text-xl font-bold text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-right"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                        </div>
                                        <hr class="border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <label class="text-base font-black text-gray-700 uppercase">Devuelta:</label>
                                            <span class="text-4xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                                                ${{ number_format($change_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- CASO OTROS --}}
                                    <div x-show="$wire.gateway !== 'Efectivo'">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">
                                            <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación (Auth Code)' : 'Referencia de la Transacción'"></span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="transaction_id" 
                                            class="w-full h-14 px-4 bg-white border border-gray-300 rounded-xl text-lg focus:ring-2 focus:ring-indigo-500 shadow-sm"
                                            placeholder="Ej: 99887766..."
                                        >
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- FOOTER DE ACCIÓN --}}
                        <div class="p-6 bg-white border-t border-gray-100 flex items-center justify-between shrink-0">
                            
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-500 font-medium">Estado:</label>
                                <select wire:model="status" class="bg-gray-50 border-0 rounded-lg text-sm font-bold text-gray-800 py-1.5 pl-3 pr-8 focus:ring-0 cursor-pointer hover:bg-gray-100">
                                    <option value="Completado">Completado</option>
                                    <option value="Pendiente">Pendiente</option>
                                </select>
                            </div>

                            <div class="flex gap-4">
                                <button 
                                    wire:click="closeModal"
                                    class="px-6 py-3 text-gray-500 font-bold hover:text-gray-800 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    wire:click="savePayment"
                                    wire:loading.attr="disabled"
                                    class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center gap-2"
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