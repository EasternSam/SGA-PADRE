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
        class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- MODAL CONTAINER --}}
    <div x-show="show" class="fixed inset-0 z-10 w-screen overflow-y-auto">
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
                class="relative w-full max-w-6xl h-[90vh] bg-white rounded-2xl shadow-2xl text-left flex flex-col overflow-hidden ring-1 ring-black/5"
            >
                
                {{-- HEADER (Barra Superior) --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white shrink-0 z-20">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-sm shadow-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Terminal de Caja</h3>
                            <p class="text-xs text-gray-500 font-medium">Nueva transacción</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="group p-2 rounded-full hover:bg-gray-100 transition-colors focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-400 group-hover:text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- CONTENIDO DIVIDIDO (Grid Layout) --}}
                <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12">
                    
                    {{-- 
                        COLUMNA 1: SELECCIÓN DE ESTUDIANTE (4 Columnas)
                        Fondo Gris suave, lista scrollable.
                    --}}
                    <div class="lg:col-span-4 bg-gray-50 border-r border-gray-200 flex flex-col h-full overflow-hidden">
                        
                        {{-- Buscador Fijo --}}
                        <div class="p-5 border-b border-gray-200 bg-white shrink-0">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Cliente / Estudiante</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search_query"
                                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                    placeholder="Buscar por nombre, código..."
                                    {{ $student ? 'disabled' : '' }}
                                >
                            </div>
                        </div>

                        {{-- Lista de Resultados / Estudiante Seleccionado --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @if($student)
                                {{-- Tarjeta Cliente Seleccionado --}}
                                <div class="bg-white rounded-xl border-2 border-indigo-100 p-5 shadow-sm relative overflow-hidden group animate-fade-in-up">
                                    <button wire:click="clearStudent" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 p-1.5 hover:bg-red-50 rounded-lg transition-all" title="Cambiar cliente">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    
                                    <div class="flex flex-col items-center text-center">
                                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-700 flex items-center justify-center font-bold text-xl mb-3 shadow-inner">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <h4 class="font-bold text-gray-900 text-lg leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 mt-1 border border-indigo-100">
                                            {{ $student->student_code ?? 'Nuevo' }}
                                        </span>
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Email:</span>
                                            <span class="text-gray-900 font-medium truncate max-w-[140px]">{{ $student->email }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Móvil:</span>
                                            <span class="text-gray-900 font-medium">{{ $student->mobile_phone ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif(count($student_results) > 0)
                                <div class="px-1 text-xs font-bold text-gray-400 uppercase">Resultados</div>
                                @foreach($student_results as $result)
                                    <div wire:click="selectStudent({{ $result->id }})" class="group p-3 bg-white rounded-lg border border-gray-200 hover:border-indigo-500 hover:shadow-md cursor-pointer transition-all flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gray-100 text-gray-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center font-bold text-sm transition-colors shrink-0">
                                            {{ substr($result->first_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-sm text-gray-900 truncate">{{ $result->first_name }} {{ $result->last_name }}</div>
                                            <div class="text-xs text-gray-500 group-hover:text-indigo-600">{{ $result->student_code }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="h-64 flex flex-col items-center justify-center text-gray-400 opacity-60">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mb-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Busque un cliente</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        COLUMNA 2: DETALLES DE PAGO (8 Columnas)
                        Fondo Blanco, scroll propio para el cuerpo, footer fijo.
                    --}}
                    <div class="lg:col-span-8 bg-white flex flex-col h-full overflow-hidden relative">
                        
                        {{-- Overlay de Bloqueo si no hay estudiante --}}
                        <div 
                            x-show="!$wire.student_id" 
                            x-transition.opacity.duration.300ms
                            class="absolute inset-0 bg-white/80 z-30 flex flex-col items-center justify-center backdrop-blur-[2px]"
                        >
                            <div class="text-center p-8 max-w-sm bg-white rounded-2xl shadow-xl border border-gray-100">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Seleccione un Cliente</h3>
                                <p class="text-sm text-gray-500 mt-2">Utilice el buscador de la izquierda para seleccionar al estudiante.</p>
                            </div>
                        </div>

                        {{-- Cuerpo del Formulario --}}
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-8 custom-scrollbar">
                            
                            {{-- Mensaje de Error --}}
                            @error('general') 
                                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3 animate-shake">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium">{{ $message }}</span>
                                </div>
                            @enderror

                            {{-- 1. Selector de Deuda (Alert Box) --}}
                            @if($studentEnrollments && $studentEnrollments->count() > 0)
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 shadow-sm">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-center gap-3 text-amber-900">
                                            <div class="p-2 bg-amber-100 rounded-lg shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-bold">Pagos Pendientes</h5>
                                                <p class="text-xs text-amber-700">El estudiante tiene deudas registradas.</p>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-auto min-w-[250px]">
                                            <select 
                                                wire:model.live="enrollment_id" 
                                                class="w-full bg-white border-amber-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block p-2.5 shadow-sm cursor-pointer"
                                            >
                                                <option value="">-- Pagar Nueva Transacción --</option>
                                                @foreach($studentEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->id }}">
                                                        {{ $enrollment->courseSchedule->module->name }} (RD$ {{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Concepto --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Concepto de la Transacción</label>
                                    <select 
                                        wire:model.live="payment_concept_id" 
                                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 shadow-sm transition-colors disabled:bg-gray-50 disabled:text-gray-400"
                                        {{ $isConceptDisabled ? 'disabled' : '' }}
                                    >
                                        <option value="">Seleccione un concepto...</option>
                                        @foreach($payment_concepts as $concept)
                                            <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_concept_id') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                </div>

                                {{-- Monto --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Monto Total</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-lg font-bold">$</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            wire:model.live="amount" 
                                            step="0.01" 
                                            class="pl-8 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xl font-bold py-3 text-gray-900 disabled:bg-gray-50 disabled:text-gray-500" 
                                            placeholder="0.00"
                                            {{ $isAmountDisabled ? 'readonly' : '' }}
                                        >
                                    </div>
                                    @error('amount') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Acción a Realizar</label>
                                    <div class="relative">
                                        <select wire:model.live="status" class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 pl-10 shadow-sm appearance-none">
                                            <option value="Completado">Cobrar Ahora (Recibo)</option>
                                            <option value="Pendiente">Generar Deuda (Factura)</option>
                                        </select>
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                            <svg x-show="$wire.status === 'Completado'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-green-600"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                            <svg x-show="$wire.status === 'Pendiente'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-amber-500" style="display:none;"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. MÉTODO DE PAGO (Condicional) --}}
                            <div x-show="$wire.status === 'Completado'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="pt-6 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Método de Pago</label>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                    @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                        <button 
                                            type="button"
                                            wire:click="$set('gateway', '{{ $method }}')"
                                            class="group relative flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all {{ $gateway === $method ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50' }}"
                                        >
                                            <span class="text-sm font-bold">{{ $method }}</span>
                                            @if($gateway === $method)
                                                <div class="absolute -top-1.5 -right-1.5 bg-indigo-600 text-white rounded-full p-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                                </div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Panel Dinámico --}}
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 shadow-inner">
                                    {{-- Efectivo --}}
                                    <div x-show="$wire.gateway === 'Efectivo'" class="flex flex-col sm:flex-row gap-6 items-center">
                                        <div class="w-full sm:w-1/2">
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Dinero Recibido</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                                <input type="number" wire:model.live="cash_received" class="w-full pl-6 pr-3 py-2.5 border border-gray-300 rounded-lg text-lg font-bold text-gray-900 focus:ring-green-500 focus:border-green-500" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-1/2 bg-white rounded-lg p-3 border border-gray-200 text-center shadow-sm">
                                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Devuelta / Cambio</span>
                                            <span class="block text-2xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                                                ${{ number_format($change_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Referencia --}}
                                    <div x-show="$wire.gateway !== 'Efectivo'" style="display: none;">
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Referencia / No. Autorización</label>
                                        <input type="text" wire:model="transaction_id" class="w-full border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 shadow-sm" placeholder="Ej: REF-12345678">
                                        @error('transaction_id') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER (Sticky Bottom) --}}
                        <div class="px-6 py-5 bg-white border-t border-gray-200 flex justify-end gap-3 shrink-0 z-20">
                            <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-200">
                                Cancelar
                            </button>
                            <button 
                                wire:click="savePayment" 
                                wire:loading.attr="disabled"
                                class="px-8 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove>
                                    {{ $status === 'Pendiente' ? 'Generar Deuda' : 'Procesar Cobro' }}
                                </span>
                                <span wire:loading class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Procesando...
                                </span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>