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
                    
                    {{-- IZQUIERDA: CLIENTE --}}
                    <div class="w-full lg:w-4/12 bg-gray-50 border-r border-gray-200 flex flex-col h-full overflow-hidden">
                        <div class="p-5 border-b border-gray-200 bg-white">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cliente</label>
                            <input type="text" wire:model.live.debounce.300ms="search_query" class="w-full bg-white border border-gray-300 rounded-lg text-sm p-2.5" placeholder="Buscar..." {{ $student ? 'disabled' : '' }}>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                            @if($student)
                                <div class="bg-white rounded-xl border border-indigo-100 p-5 shadow-sm relative">
                                    <button wire:click="clearStudent" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
                                    </button>
                                    <div class="text-center">
                                        <div class="h-12 w-12 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold mb-2">
                                            {{ substr($student->first_name, 0, 1) }}
                                        </div>
                                        <h4 class="font-bold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                        <div class="text-sm text-gray-500">{{ $student->student_code }}</div>
                                    </div>
                                </div>
                            @elseif(count($student_results) > 0)
                                @foreach($student_results as $result)
                                    <div wire:click="selectStudent({{ $result->id }})" class="p-3 bg-white rounded-lg border hover:border-indigo-500 cursor-pointer">
                                        <div class="font-bold text-sm">{{ $result->first_name }} {{ $result->last_name }}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- DERECHA: PAGO --}}
                    <div class="w-full lg:w-8/12 bg-white flex flex-col relative h-full overflow-hidden">
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-8 custom-scrollbar">
                            
                            @error('general')
                                <div class="p-4 text-sm text-red-800 bg-red-50 rounded-lg border border-red-200">{{ $message }}</div>
                            @enderror

                            @if($pendingDebts->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($pendingDebts as $debt)
                                        <button wire:click="selectDebt('{{ $debt['type'] }}', {{ $debt['id'] }})" class="flex justify-between p-3 rounded-xl border {{ ($payment_id_to_update == $debt['id'] || $enrollment_id == $debt['id']) ? 'bg-indigo-50 border-indigo-500' : 'bg-white' }}">
                                            <div class="text-left text-sm font-bold">{{ Str::limit($debt['concept'], 30) }}</div>
                                            <div class="text-right font-bold">RD$ {{ number_format($debt['amount'], 2) }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Concepto</label>
                                    <select wire:model.live="payment_concept_id" class="w-full border-gray-300 rounded-lg p-2.5 text-sm" {{ $isConceptDisabled ? 'disabled' : '' }}>
                                        <option value="">Seleccione...</option>
                                        @foreach($payment_concepts as $concept)
                                            <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_concept_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Monto</label>
                                    <input type="number" wire:model.live="amount" class="w-full border-gray-300 rounded-lg p-2.5 text-sm font-bold" {{ $isAmountDisabled ? 'readonly' : '' }}>
                                    @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Acción</label>
                                    <select wire:model.live="status" class="w-full border-gray-300 rounded-lg p-2.5 text-sm">
                                        <option value="Completado">Cobrar Ahora</option>
                                        <option value="Pendiente">Generar Deuda</option>
                                    </select>
                                </div>
                            </div>

                            {{-- MÉTODOS DE PAGO --}}
                            <div x-show="$wire.status === 'Completado'" class="mt-6 border-t pt-6">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Método de Pago</label>
                                <div class="grid grid-cols-4 gap-3 mb-6">
                                    @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'] as $method)
                                        <button type="button" wire:click="$set('gateway', '{{ $method }}')" class="p-3 rounded-xl border text-sm font-bold transition-all {{ $gateway === $method ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                            {{ $method }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- DETALLES ESPECÍFICOS --}}
                                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                                    
                                    {{-- Efectivo --}}
                                    <div x-show="$wire.gateway === 'Efectivo'">
                                        <label class="text-xs font-bold text-gray-500">Recibido</label>
                                        <input type="number" wire:model.live="cash_received" class="w-full border-gray-300 rounded-lg p-2.5 mt-1">
                                        <div class="mt-2 font-bold text-lg text-emerald-600">Devuelta: RD$ {{ number_format($change_amount, 2) }}</div>
                                    </div>
                                    
                                    {{-- Tarjeta --}}
                                    <div x-show="$wire.gateway === 'Tarjeta'" class="text-center py-4">
                                        <div class="bg-white p-4 rounded-full inline-block shadow-sm mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-indigo-600">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-900 font-medium">Pago con Redirección Segura</p>
                                        <p class="text-gray-500 text-sm mb-2">Será redirigido a la plataforma de Cardnet para completar el pago.</p>
                                        <div wire:loading wire:target="savePayment" class="text-indigo-600 font-bold text-sm animate-pulse">
                                            Conectando con Cardnet...
                                        </div>
                                    </div>

                                    {{-- Otros --}}
                                    <div x-show="!['Efectivo','Tarjeta'].includes($wire.gateway)">
                                        <label class="text-xs font-bold text-gray-500">Referencia / Autorización</label>
                                        <input type="text" wire:model="transaction_id" class="w-full border-gray-300 rounded-lg p-2.5 mt-1">
                                        @error('transaction_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER --}}
                        <div class="px-6 py-5 border-t bg-white flex justify-end gap-3">
                            <button wire:click="closeModal" class="px-5 py-2.5 border rounded-lg hover:bg-gray-50 text-sm font-bold text-gray-600">Cancelar</button>
                            <button wire:click="savePayment" class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 text-sm font-bold flex items-center gap-2" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $gateway === 'Tarjeta' ? 'Ir a Pagar' : 'Procesar Pago' }}</span>
                                <span wire:loading>Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORMULARIO OCULTO PARA REDIRECCIÓN POST --}}
    <form id="cardnet-post-form" action="{{ $cardnetUrl }}" method="POST" style="display:none;">
        @foreach($cardnetFields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        document.addEventListener('livewire:init', () => {
            // Escuchar evento de envío
            Livewire.on('submit-cardnet-form', () => {
                const form = document.getElementById('cardnet-post-form');
                if(form && form.action) {
                    console.log('Redirigiendo a Cardnet POST...', form.action);
                    form.submit();
                } else {
                    alert('Error técnico: No se pudo generar el formulario de pago.');
                }
            });

            Livewire.on('printTicket', (event) => {
                const url = event.url;
                if (url) window.open(url, 'Ticket', 'width=400,height=600').focus();
            });
        });
    </script>
</div>