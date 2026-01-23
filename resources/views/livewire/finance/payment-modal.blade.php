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
                
                {{-- HEADER --}}
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

                {{-- CONTENIDO DIVIDIDO --}}
                <div class="flex flex-col lg:flex-row flex-1 min-h-0 overflow-hidden">
                    
                    {{-- COLUMNA IZQUIERDA: CLIENTE --}}
                    <div class="w-full lg:w-4/12 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200 flex flex-col h-full overflow-hidden">
                        
                        {{-- Buscador --}}
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
                                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm disabled:bg-gray-100 disabled:text-gray-500"
                                    placeholder="Buscar por nombre..."
                                    {{ $student ? 'disabled' : '' }}
                                >
                            </div>
                        </div>

                        {{-- Lista Resultados --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @if($student)
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
                                    <p class="text-sm font-medium">Busque un cliente</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: PAGO --}}
                    <div class="w-full lg:w-8/12 bg-white flex flex-col relative h-full overflow-hidden">
                        
                        {{-- Overlay Bloqueo --}}
                        <div x-show="!$wire.student_id" x-transition.opacity.duration.300ms class="absolute inset-0 bg-white/80 z-30 flex flex-col items-center justify-center backdrop-blur-[2px]">
                            <div class="text-center p-8 max-w-sm bg-white rounded-2xl shadow-xl border border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900">Seleccione un Cliente</h3>
                                <p class="text-sm text-gray-500 mt-2">Utilice el buscador para comenzar.</p>
                            </div>
                        </div>

                        {{-- Cuerpo Scrollable --}}
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-8 custom-scrollbar">
                            
                            {{-- SECCIÓN A: CUENTAS POR COBRAR --}}
                            @if($pendingDebts->count() > 0)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        Cuentas por Cobrar
                                        <span class="bg-red-100 text-red-700 px-1.5 rounded-md text-[10px]">{{ $pendingDebts->count() }}</span>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($pendingDebts as $debt)
                                            <button 
                                                type="button"
                                                wire:click="selectDebt('{{ $debt['type'] }}', {{ $debt['id'] }})"
                                                class="flex items-center justify-between p-3 rounded-xl border text-left transition-all group {{ ($payment_id_to_update == $debt['id'] || $enrollment_id == $debt['id']) ? 'bg-indigo-50 border-indigo-500 shadow-md ring-1 ring-indigo-500' : 'bg-white border-gray-200 hover:border-indigo-300 hover:shadow-sm' }}"
                                            >
                                                <div>
                                                    <p class="font-bold text-sm text-gray-900 group-hover:text-indigo-700">{{ Str::limit($debt['concept'], 35) }}</p>
                                                    <p class="text-xs text-gray-500">{{ $debt['date']->format('d/m/Y') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-bold text-gray-900">RD$ {{ number_format($debt['amount'], 2) }}</p>
                                                    <span class="text-[10px] font-medium text-red-600 uppercase">Pendiente</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <hr class="border-gray-100">
                            @endif

                            {{-- SECCIÓN B: FORMULARIO DE TRANSACCIÓN --}}
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">Detalles de la Transacción</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Concepto --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Concepto</label>
                                        <select 
                                            wire:model.live="payment_concept_id" 
                                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 shadow-sm transition-colors disabled:bg-gray-50 disabled:text-gray-400"
                                            {{ $isConceptDisabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Seleccione concepto...</option>
                                            @foreach($payment_concepts as $concept)
                                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_concept_id') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Monto --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Monto (DOP)</label>
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

                                    {{-- Acción --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Acción</label>
                                        <div class="relative">
                                            <select wire:model.live="status" class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 pl-10 shadow-sm appearance-none cursor-pointer">
                                                <option value="Completado">Cobrar Ahora (Ingreso)</option>
                                                <option value="Pendiente">Generar Deuda (Crédito)</option>
                                            </select>
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                                <svg x-show="$wire.status === 'Completado'" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                <svg x-show="$wire.status === 'Pendiente'" class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SECCIÓN C: MÉTODO DE PAGO (SOLO SI ES COBRO INMEDIATO) --}}
                                <div x-show="$wire.status === 'Completado'" x-transition class="mt-6 pt-6 border-t border-gray-100">
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

                                    {{-- Panel Dinámico (Cambio, Referencia o Cardnet) --}}
                                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 shadow-inner">
                                        
                                        {{-- 1. Efectivo --}}
                                        <div x-show="$wire.gateway === 'Efectivo'" class="flex flex-col sm:flex-row gap-6 items-center">
                                            <div class="w-full sm:w-1/2">
                                                <label class="block text-xs font-bold text-gray-500 mb-1">Dinero Recibido</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                                    <input type="number" wire:model.live="cash_received" class="w-full pl-6 pr-3 py-2.5 border border-gray-300 rounded-lg text-lg font-bold text-gray-900 focus:ring-green-500 focus:border-green-500" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="w-full sm:w-1/2 bg-white rounded-lg p-3 border border-gray-200 text-center shadow-sm">
                                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Devuelta</span>
                                                <span class="block text-2xl font-black {{ $change_amount < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                                    ${{ number_format($change_amount, 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- 2. Tarjeta (Cardnet) --}}
                                        <div x-show="$wire.gateway === 'Tarjeta'">
                                            {{-- CONTENEDOR PARA EL IFRAME DE CARDNET --}}
                                            <div id="cardnet-container" class="w-full min-h-[300px] bg-white rounded-lg border border-gray-300 flex items-center justify-center">
                                                <span class="text-gray-400 text-sm">El formulario de pago seguro cargará aquí...</span>
                                            </div>
                                            
                                            {{-- FORMULARIO OCULTO PARA CARDNET --}}
                                            <form id="cardnet-form" style="display: none;">
                                                <input type="hidden" name="PWToken" id="PWToken" />
                                            </form>
                                        </div>

                                        {{-- 3. Otros Métodos --}}
                                        <div x-show="$wire.gateway !== 'Efectivo' && $wire.gateway !== 'Tarjeta'">
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Referencia / No. Autorización</label>
                                            <input type="text" wire:model="transaction_id" class="w-full border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 shadow-sm" placeholder="Ej: REF-12345678">
                                            @error('transaction_id') <p class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER (Sticky Bottom) --}}
                        <div class="px-6 py-5 bg-white border-t border-gray-200 flex justify-end gap-3 shrink-0 z-20">
                            <button wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-200">
                                Cancelar
                            </button>
                            
                            {{-- Botón de Acción --}}
                            <button 
                                wire:click="savePayment" 
                                wire:loading.attr="disabled"
                                class="px-8 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                {{-- Si es tarjeta, deshabilitamos el botón hasta que se procese el iframe, o lo usamos para trigger inicial --}}
                                {{ $gateway === 'Tarjeta' ? 'disabled' : '' }}
                                x-text="$wire.gateway === 'Tarjeta' ? 'Complete el pago arriba' : ($wire.status === 'Pendiente' ? 'Generar Deuda' : 'Procesar Cobro')"
                            >
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

    {{-- SCRIPT DE CARDNET CORREGIDO --}}
    <script>
        document.addEventListener('livewire:init', () => {
            
            // Escuchar evento para iniciar Cardnet
            Livewire.on('start-cardnet-payment', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                console.log('Iniciando Cardnet Custom Iframe...', payload);

                if (typeof PWCheckout === 'undefined') {
                    console.error('PWCheckout no cargado.');
                    alert('Error: La pasarela de pagos no está disponible. Verifique la configuración.');
                    return;
                }

                // Configurar
                PWCheckout.SetProperties({
                    "name": "Pago de Matrícula",
                    "email": payload.studentEmail,
                    "image": "{{ config('services.cardnet.image_url') }}",
                    "button_label": "Pagar #monto#",
                    "description": payload.description,
                    "currency": "DOP",
                    "amount": payload.amount,
                    "lang": "ESP",
                    "form_id": "cardnet-form", 
                    "checkout_card": 1,
                    "autoSubmit": "false", // Importante false para manejar nosotros el token
                    "empty": "false"
                });

                // Callback
                window.OnTokenReceived = function(token) {
                    console.log('Token recibido:', token);
                    @this.call('processCardnetPayment', token);
                };

                PWCheckout.Bind("tokenCreated", window.OnTokenReceived);

                // Renderizar en el DIV específico usando Custom Iframe
                if (typeof PWCheckout.OpenIframeCustom === 'function') {
                    document.getElementById('cardnet-container').innerHTML = ''; 
                    PWCheckout.OpenIframeCustom("cardnet-container");
                } else if (typeof PWCheckout.iframe !== 'undefined' && typeof PWCheckout.iframe.OpenIframeCustom === 'function') {
                    document.getElementById('cardnet-container').innerHTML = ''; 
                    PWCheckout.iframe.OpenIframeCustom("cardnet-container");
                } else {
                    console.error('Método OpenIframeCustom no encontrado en PWCheckout.');
                    alert('Error técnico: No se pudo cargar el formulario de tarjeta.');
                }
            });

            // Manejar impresión de tickets
            Livewire.on('printTicket', (event) => {
                const url = event.url;
                if (url) {
                    const printWindow = window.open(url, 'Ticket', 'width=400,height=600');
                    if (printWindow) {
                        printWindow.focus();
                    }
                }
            });
        });
    </script>
</div>