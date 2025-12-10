<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- 
        BACKDROP (Fondo Oscuro)
        Usamos estilo inline para forzar la opacidad al 100% y asegurar que el fondo se vea oscuro.
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
        style="background-color: rgba(17, 24, 39, 0.9); backdrop-filter: blur(5px);"
        aria-hidden="true"
    ></div>

    {{-- WRAPPER DE POSICIONAMIENTO --}}
    <div 
        x-show="show"
        class="fixed inset-0 z-10 overflow-hidden" 
    >
        <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
            
            {{-- 
                PANEL DEL MODAL (Casi Pantalla Completa)
                - w-[95vw]: 95% del ancho de la vista.
                - h-[92vh]: 92% del alto de la vista.
                - Esto elimina la sensación de "compactado".
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
                class="relative w-[95vw] h-[92vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-gray-700/20"
            >
                
                {{-- HEADER (Barra Superior) --}}
                <div class="h-16 px-6 bg-white border-b border-gray-200 flex items-center justify-between shrink-0 z-20">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-200">
                            <i class="fas fa-cash-register text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 tracking-tight leading-none">Terminal de Caja</h2>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mt-1">Sistema de Pagos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="bg-gray-100 px-4 py-1.5 rounded-full text-xs font-bold text-gray-500">
                            {{ now()->format('d M, Y') }}
                        </div>
                        <button wire:click="closeModal" class="h-10 w-10 rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center justify-center focus:outline-none">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                {{-- CONTENIDO PRINCIPAL (Grid 3 Columnas) --}}
                <div class="flex-1 grid grid-cols-12 overflow-hidden bg-gray-50">
                    
                    {{-- 
                        COLUMNA 1: BÚSQUEDA Y LISTA (20% Ancho)
                        Fondo blanco limpio.
                    --}}
                    <div class="col-span-12 lg:col-span-3 xl:col-span-3 bg-white border-r border-gray-200 flex flex-col h-full z-10">
                        <!-- Buscador -->
                        <div class="p-4 border-b border-gray-100">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Buscar Cliente</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-9 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-400"
                                    placeholder="Nombre, Matrícula..."
                                    autofocus
                                >
                            </div>
                        </div>
                        
                        <!-- Lista Scrollable -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar bg-gray-50/30">
                            @if(count($student_results) > 0)
                                @foreach($student_results as $result)
                                    <div 
                                        wire:click="selectStudent({{ $result->id }})"
                                        class="p-3 rounded-xl cursor-pointer transition-all border border-transparent group flex items-center gap-3 {{ $student && $student->id === $result->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 transform scale-[1.02]' : 'bg-white hover:bg-white hover:shadow-sm hover:border-gray-200 text-gray-600' }}"
                                    >
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $student && $student->id === $result->id ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600' }}">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden min-w-0">
                                            <h4 class="text-sm font-bold truncate {{ $student && $student->id === $result->id ? 'text-white' : 'text-gray-800' }}">{{ $result->first_name }} {{ $result->last_name }}</h4>
                                            <p class="text-[11px] truncate {{ $student && $student->id === $result->id ? 'text-indigo-200' : 'text-gray-400' }}">{{ $result->id_number }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif(strlen($search_query) > 2)
                                <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60">
                                    <i class="far fa-folder-open text-3xl mb-3"></i>
                                    <p class="text-sm font-medium">Sin resultados</p>
                                </div>
                            @else
                                <div class="h-full flex flex-col items-center justify-center text-gray-300 opacity-50">
                                    <i class="fas fa-search text-4xl mb-4"></i>
                                    <p class="text-sm">Buscador de Alumnos</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: PERFIL DEL ESTUDIANTE (25% Ancho)
                        Diseño tipo tarjeta de identificación.
                    --}}
                    <div class="hidden lg:flex col-span-3 border-r border-gray-200 flex-col items-center justify-center p-8 text-center bg-white relative">
                        @if($student)
                            <div class="absolute top-0 left-0 w-full h-1/3 bg-gradient-to-b from-indigo-50/50 to-transparent"></div>
                            
                            <button wire:click="clearStudent" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-all z-10" title="Desvincular Cliente">
                                <i class="fas fa-unlink"></i>
                            </button>

                            <div class="relative mb-6 animate-fade-in-up z-10">
                                <div class="h-32 w-32 rounded-full bg-white p-1.5 shadow-xl ring-1 ring-gray-100 mx-auto">
                                    <div class="h-full w-full rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-4xl font-bold text-white shadow-inner">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="absolute bottom-2 right-2 h-7 w-7 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>

                            <div class="animate-fade-in-up z-10 w-full" style="animation-delay: 100ms;">
                                <h3 class="text-2xl font-black text-gray-800 leading-tight mb-1">{{ $student->first_name }}</h3>
                                <h4 class="text-lg font-medium text-gray-500 mb-4">{{ $student->last_name }}</h4>
                                
                                <div class="inline-block bg-indigo-50 border border-indigo-100 px-4 py-1.5 rounded-full text-indigo-700 font-bold text-xs mb-8 shadow-sm">
                                    ID: {{ $student->id_number }}
                                </div>
                            </div>

                            <div class="w-full space-y-4 animate-fade-in-up z-10" style="animation-delay: 200ms;">
                                <div class="bg-gray-50 p-4 rounded-xl text-left border border-gray-100 flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-lg bg-white border border-gray-200 text-gray-400 flex items-center justify-center shrink-0 shadow-sm">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Correo Electrónico</p>
                                        <p class="text-sm font-bold text-gray-800 truncate" title="{{ $student->email }}">{{ $student->email }}</p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-xl text-left border border-gray-100 flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-lg bg-white border border-gray-200 text-gray-400 flex items-center justify-center shrink-0 shadow-sm">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Teléfono</p>
                                        <p class="text-sm font-bold text-gray-800">{{ $student->mobile_phone ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="opacity-30 flex flex-col items-center">
                                <i class="fas fa-id-card text-7xl text-gray-400 mb-6"></i>
                                <p class="text-lg font-medium text-gray-400">Seleccione un cliente<br>para ver su perfil.</p>
                            </div>
                        @endif
                    </div>

                    {{-- 
                        COLUMNA 3: FORMULARIO DE PAGO (55% Ancho)
                        Área de trabajo principal. Muy espaciosa.
                    --}}
                    <div class="col-span-12 lg:col-span-6 flex flex-col h-full bg-white relative">
                        
                        {{-- Bloqueo (Overlay) si no hay estudiante --}}
                        <div 
                            class="absolute inset-0 bg-white/70 backdrop-blur-sm z-30 flex flex-col items-center justify-center transition-opacity duration-300"
                            x-show="!$wire.student_id"
                        >
                            <div class="bg-white p-10 rounded-2xl shadow-2xl border border-gray-100 text-center max-w-md transform scale-100">
                                <div class="h-16 w-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                                    <i class="fas fa-arrow-left text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Terminal en Espera</h3>
                                <p class="text-base text-gray-500 leading-relaxed">Seleccione un estudiante del listado para habilitar la caja y procesar el pago.</p>
                            </div>
                        </div>

                        {{-- Cuerpo del Formulario (Scrollable si es necesario, pero intentamos que quepa) --}}
                        <div class="flex-1 overflow-y-auto p-8 lg:p-10 custom-scrollbar">
                            <form wire:submit.prevent="savePayment" class="flex flex-col gap-8 h-full justify-start">
                                
                                {{-- 1. Pagos Pendientes (Dropdown Grande) --}}
                                @if($studentEnrollments && $studentEnrollments->count() > 0)
                                    <div class="bg-amber-50 rounded-2xl p-1.5 border border-amber-200 animate-fade-in-down shadow-sm">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-exclamation-circle text-amber-500 text-lg"></i>
                                            </div>
                                            <select 
                                                wire:model.live="enrollment_id" 
                                                class="block w-full py-4 pl-12 pr-10 bg-transparent border-0 focus:ring-0 text-amber-900 font-bold text-base cursor-pointer"
                                            >
                                                <option value="">-- Seleccionar Deuda Pendiente ({{ $studentEnrollments->count() }}) --</option>
                                                @foreach($studentEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->id }}">
                                                        {{ $enrollment->courseSchedule->module->name }} — ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                {{-- 2. Configuración Financiera (Concepto y Monto) --}}
                                <div class="grid grid-cols-12 gap-6">
                                    {{-- Concepto --}}
                                    <div class="col-span-7">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 ml-1">Concepto</label>
                                        <select 
                                            wire:model.live="payment_concept_id" 
                                            class="w-full h-16 px-4 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 font-bold text-base focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all shadow-sm"
                                            {{ $isConceptDisabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Seleccionar concepto...</option>
                                            @foreach($payment_concepts as $concept)
                                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_concept_id') <span class="text-xs text-red-500 mt-1 pl-1 font-bold">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Monto Gigante --}}
                                    <div class="col-span-5">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 ml-1 text-right">Monto (DOP)</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-400 text-3xl font-light group-focus-within:text-indigo-500 transition-colors">$</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live="amount" 
                                                class="w-full h-16 pl-10 pr-4 bg-white border-2 border-indigo-100 text-4xl font-black text-indigo-900 rounded-xl focus:ring-0 focus:border-indigo-500 text-right shadow-sm transition-all"
                                                placeholder="0.00"
                                                {{ $isAmountDisabled ? 'readonly' : '' }}
                                            >
                                        </div>
                                        @error('amount') <span class="text-xs text-red-500 mt-1 block text-right font-bold">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Separador Sutil --}}
                                <hr class="border-gray-100">

                                {{-- 3. Método de Pago y Caja --}}
                                <div class="grid grid-cols-12 gap-8 h-full">
                                    {{-- Selector de Método (Vertical) --}}
                                    <div class="col-span-4 flex flex-col gap-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Método</label>
                                        @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                            <button 
                                                type="button"
                                                wire:click="$set('gateway', '{{ $method }}')"
                                                class="w-full py-4 px-4 rounded-xl text-left font-bold text-sm transition-all border flex items-center justify-between group {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-[1.03]' : 'bg-white text-gray-500 border-gray-200 hover:border-indigo-300 hover:text-indigo-600' }}"
                                            >
                                                <span>{{ $method }}</span>
                                                @if($gateway === $method) <i class="fas fa-check"></i> @endif
                                            </button>
                                        @endforeach
                                    </div>

                                    {{-- Panel de Detalles Dinámico --}}
                                    <div class="col-span-8">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Detalles del Pago</label>
                                        
                                        <div class="bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 p-6 h-[calc(100%-2rem)] flex flex-col justify-center">
                                            
                                            {{-- CASO EFECTIVO --}}
                                            <div x-show="$wire.gateway === 'Efectivo'" class="space-y-6 animate-fade-in">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Efectivo Recibido</label>
                                                    <div class="relative">
                                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xl">$</span>
                                                        <input 
                                                            type="number" 
                                                            step="0.01" 
                                                            wire:model.live="cash_received" 
                                                            class="w-full pl-10 pr-4 py-4 text-3xl font-bold text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 shadow-sm transition-all"
                                                            placeholder="0.00"
                                                        >
                                                    </div>
                                                </div>
                                                
                                                <div class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center shadow-sm">
                                                    <span class="text-sm font-bold text-gray-500 uppercase">Cambio / Devuelta</span>
                                                    <span class="text-4xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                                                        ${{ number_format($change_amount, 2) }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- CASO OTROS --}}
                                            <div x-show="$wire.gateway !== 'Efectivo'" class="animate-fade-in">
                                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                                    <span x-text="$wire.gateway === 'Tarjeta' ? 'Número de Aprobación (Auth Code)' : 'Referencia / Comprobante'"></span>
                                                </label>
                                                <div class="relative">
                                                    <i class="fas fa-receipt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                                    <input 
                                                        type="text" 
                                                        wire:model="transaction_id" 
                                                        class="w-full pl-10 pr-4 py-4 bg-white border border-gray-300 rounded-xl text-lg focus:ring-2 focus:ring-indigo-500 shadow-sm"
                                                        placeholder="Ingrese número de referencia..."
                                                    >
                                                </div>
                                                @error('transaction_id') <span class="text-xs text-red-500 mt-2 block font-bold">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- FOOTER FIJO (Botones de Acción) --}}
                        <div class="p-6 bg-white border-t border-gray-100 flex items-center justify-between shrink-0 z-20 shadow-[0_-5px_15px_-5px_rgba(0,0,0,0.05)]">
                            
                            {{-- Selector de Estado Rápido --}}
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-gray-400 uppercase">Estado:</span>
                                <div class="bg-gray-100 p-1 rounded-lg flex">
                                    <button 
                                        wire:click="$set('status', 'Completado')" 
                                        class="px-4 py-2 text-xs font-bold rounded-md transition-all {{ $status === 'Completado' ? 'bg-white text-green-700 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}"
                                    >
                                        Completado
                                    </button>
                                    <button 
                                        wire:click="$set('status', 'Pendiente')" 
                                        class="px-4 py-2 text-xs font-bold rounded-md transition-all {{ $status === 'Pendiente' ? 'bg-white text-yellow-700 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}"
                                    >
                                        Pendiente
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button 
                                    wire:click="closeModal"
                                    class="px-8 py-4 text-sm font-bold text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-200"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    wire:click="savePayment"
                                    wire:loading.attr="disabled"
                                    class="px-10 py-4 bg-gray-900 hover:bg-black text-white rounded-xl font-bold text-lg shadow-xl shadow-gray-200 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading wire:target="savePayment" class="animate-spin">
                                        <i class="fas fa-circle-notch"></i>
                                    </span>
                                    <span>Cobrar ${{ number_format($amount, 2) }}</span>
                                    <i class="fas fa-arrow-right text-sm opacity-50" wire:loading.remove wire:target="savePayment"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>