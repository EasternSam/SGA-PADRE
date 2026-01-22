<div 
    x-data="{ show: $wire.entangle('show') }" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    {{-- BACKDROP --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    {{-- MODAL CONTAINER --}}
    <div x-show="show" class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
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
                class="relative w-full max-w-5xl h-full sm:h-auto sm:max-h-[90vh] bg-white sm:rounded-2xl shadow-2xl text-left transform transition-all flex flex-col overflow-hidden border border-gray-200"
            >
                
                {{-- HEADER --}}
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0 z-20">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 leading-tight">Terminal de Caja</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Registrar nuevo movimiento</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- CONTENT --}}
                <div class="flex flex-col lg:flex-row flex-1 min-h-0 overflow-hidden">
                    
                    {{-- COLUMNA IZQUIERDA: CLIENTE --}}
                    <div class="w-full lg:w-4/12 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200 flex flex-col h-full overflow-hidden">
                        
                        {{-- Buscador --}}
                        <div class="p-5 border-b border-gray-200 bg-white shrink-0 z-10">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Cliente / Estudiante</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    placeholder="Buscar por nombre o matrícula..."
                                    {{ $student ? 'disabled' : '' }}
                                >
                            </div>
                        </div>

                        {{-- Resultados / Info Cliente --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @if($student)
                                {{-- Tarjeta Cliente Seleccionado --}}
                                <div class="bg-white rounded-xl border border-indigo-100 p-5 shadow-sm relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 p-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="clearStudent" class="text-gray-400 hover:text-red-500 transition-colors" title="Cambiar cliente">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="flex flex-col items-center text-center mb-4">
                                        <div class="h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xl mb-3 ring-4 ring-indigo-50">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <h4 class="font-bold text-gray-900 text-lg">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 mt-1">
                                            {{ $student->student_code ?? 'Nuevo Ingreso' }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 text-sm border-t border-gray-100 pt-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Email:</span>
                                            <span class="text-gray-900 font-medium truncate max-w-[150px]">{{ $student->email }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Tel:</span>
                                            <span class="text-gray-900 font-medium">{{ $student->mobile_phone ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif(count($student_results) > 0)
                                <div class="space-y-2">
                                    <div class="text-xs font-bold text-gray-400 uppercase px-1">Resultados</div>
                                    @foreach($student_results as $result)
                                        <div wire:click="selectStudent({{ $result->id }})" class="p-3 bg-white rounded-lg border border-gray-200 hover:border-indigo-400 hover:shadow-md cursor-pointer transition-all flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs">
                                                {{ substr($result->first_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-sm text-gray-800 truncate">{{ $result->first_name }} {{ $result->last_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $result->student_code }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-60 pb-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mb-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Busque un cliente</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: DATOS DE PAGO --}}
                    <div class="w-full lg:w-8/12 bg-white flex flex-col relative h-full overflow-hidden">
                        
                        {{-- Overlay Bloqueo --}}
                        <div x-show="!$wire.student_id" x-transition.opacity class="absolute inset-0 bg-white/80 z-20 flex flex-col items-center justify-center backdrop-blur-sm">
                            <div class="text-center p-6 max-w-sm">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Seleccione un cliente</h3>
                                <p class="text-sm text-gray-500 mt-1">Busque y seleccione un estudiante para procesar el pago.</p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 custom-scrollbar">
                            
                            {{-- Mensaje de Error General --}}
                            @error('general') 
                                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- 1. SELECCIÓN DE DEUDA (Opcional) --}}
                            @if($studentEnrollments && $studentEnrollments->count() > 0)
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Pagar Deuda Existente</label>
                                    <div class="relative">
                                        <select wire:model.live="enrollment_id" class="w-full bg-amber-50 border border-amber-200 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block p-3 pr-10 shadow-sm appearance-none">
                                            <option value="">-- Crear Nuevo Cobro --</option>
                                            @foreach($studentEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->name }} - Pendiente: RD$ {{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-amber-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- 2. DETALLES DEL PAGO --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Concepto --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Concepto</label>
                                    <select 
                                        wire:model.live="payment_concept_id" 
                                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 shadow-sm disabled:bg-gray-100 disabled:text-gray-500"
                                        {{ $isConceptDisabled ? 'disabled' : '' }}
                                    >
                                        <option value="">Seleccione concepto...</option>
                                        @foreach($payment_concepts as $concept)
                                            <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_concept_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>

                                {{-- Monto --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Monto (DOP)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm font-bold">$</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            wire:model.live="amount" 
                                            step="0.01" 
                                            class="pl-7 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 font-bold text-gray-900 disabled:bg-gray-100" 
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                    @error('amount') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Acción</label>
                                    <select wire:model.live="status" class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 shadow-sm">
                                        <option value="Completado">Cobrar Ahora (Pagado)</option>
                                        <option value="Pendiente">Generar Deuda (Pendiente)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- 3. MÉTODO DE PAGO (Solo si es cobro inmediato) --}}
                            <div x-show="$wire.status === 'Completado'" x-transition class="space-y-4 pt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Método de Pago</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                        <button 
                                            type="button"
                                            wire:click="$set('gateway', '{{ $method }}')"
                                            class="py-2.5 px-3 rounded-lg border text-sm font-medium transition-all {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
                                        >
                                            {{ $method }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Campos dinámicos según método --}}
                                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                    {{-- Efectivo: Calculadora de Cambio --}}
                                    <div x-show="$wire.gateway === 'Efectivo'" class="flex flex-col sm:flex-row gap-4 items-end">
                                        <div class="w-full">
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Recibido</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                                <input type="number" wire:model.live="cash_received" class="w-full pl-6 pr-3 py-2 border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="w-full text-right">
                                            <span class="block text-xs font-bold text-gray-400 uppercase">Cambio</span>
                                            <span class="text-2xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                                RD$ {{ number_format($change_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Otros: Referencia --}}
                                    <div x-show="$wire.gateway !== 'Efectivo'">
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Referencia / No. Autorización</label>
                                        <input type="text" wire:model="transaction_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 py-2" placeholder="Ej: REF-123456">
                                        @error('transaction_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3 shrink-0 z-20">
                            <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button 
                                wire:click="savePayment" 
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove>
                                    {{ $status === 'Pendiente' ? 'Generar Deuda' : 'Procesar Pago' }}
                                </span>
                                <span wire:loading>Procesando...</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>