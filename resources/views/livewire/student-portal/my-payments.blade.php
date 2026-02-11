<div class="min-h-screen bg-gray-50/50 pb-12">

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Finanzas</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Gestiona tus pagos, visualiza deudas pendientes y descarga tus comprobantes.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium flex items-center gap-2 shadow-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    Estado: Activo
                </div>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- Alertas --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-6 right-6 z-50">
                <div class="bg-gray-900 text-white rounded-lg px-4 py-3 shadow-xl flex items-center gap-3 min-w-[300px]">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div class="flex-1 text-sm font-medium">{{ session('message') }}</div>
                    <button @click="show = false" class="text-gray-400 hover:text-white"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" class="fixed bottom-6 right-6 z-50">
                <div class="bg-red-900 text-white rounded-lg px-4 py-3 shadow-xl flex items-center gap-3 min-w-[300px]">
                    <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                    <button @click="show = false" class="text-gray-400 hover:text-white"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
        @endif

        {{-- SECCIÓN 1: PENDIENTES --}}
        <section>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    Pagos Pendientes
                    @if($pendingDebts->count() > 0)
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-red-200">{{ $pendingDebts->count() }}</span>
                    @endif
                </h3>
            </div>
            @if($pendingDebts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($pendingDebts as $debt)
                        <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col h-full overflow-hidden">
                            <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex justify-between items-start">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 mb-3">{{ $debt->paymentConcept->name ?? 'Pendiente' }}</span>
                                    <h4 class="text-base font-bold text-gray-900 leading-snug">{{ $debt->enrollment->courseSchedule->module->name ?? 'Módulo General' }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ $debt->enrollment->courseSchedule->module->course->name ?? 'Curso Académico' }}</p>
                                </div>
                                <div class="text-xs font-mono text-gray-400 bg-white border border-gray-100 px-2 py-1 rounded-md">#{{ $debt->id }}</div>
                            </div>
                            <div class="p-6 flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Sección</p><p class="font-medium text-gray-900 mt-1">{{ $debt->enrollment->courseSchedule->section_name ?? 'N/A' }}</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Vencimiento</p><p class="font-medium {{ $debt->due_date && $debt->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $debt->due_date ? $debt->due_date->format('d/m/Y') : 'N/A' }}</p></div>
                                </div>
                            </div>
                            <div class="p-6 pt-0 mt-auto">
                                <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-50">
                                    <div><p class="text-xs text-gray-500 mb-0.5">Total a Pagar</p><p class="text-xl font-bold text-gray-900 tracking-tight">RD$ {{ number_format($debt->amount, 2) }}</p></div>
                                    <button wire:click="openPaymentModal({{ $debt->id }})" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm hover:bg-gray-800 transition-colors">Pagar Ahora</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-3 flex items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                    <h3 class="text-base font-semibold text-gray-900">Estás al día</h3>
                    <p class="mt-1 text-sm text-gray-500">No tienes pagos pendientes.</p>
                </div>
            @endif
        </section>

        {{-- SECCIÓN 2: HISTORIAL --}}
        <section>
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Historial de Transacciones</h3>
                    <button wire:click="downloadFinancialReport" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="downloadFinancialReport" class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> Descargar Reporte</span>
                        <span wire:loading wire:target="downloadFinancialReport" class="flex items-center gap-2">Generando...</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Concepto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Método</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Monto</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($paymentHistory as $payment)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">{{ $payment->created_at->format('d M, Y') }}</div><div class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</div></td>
                                    <td class="px-6 py-4"><div class="text-sm font-medium text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>@if($payment->enrollment)<div class="text-xs text-gray-500 mt-0.5">{{ $payment->enrollment->courseSchedule->module->name ?? '' }}</div>@endif<div class="text-[10px] text-gray-400 font-mono mt-1">ID: {{ $payment->transaction_id ?? '---' }}</div></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment->gateway }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $payment->status === 'Completado' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : ($payment->status === 'Pendiente' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-red-50 text-red-700 ring-red-600/20') }}">{{ $payment->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">RD$ {{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($payment->status === 'Completado')
                                            {{-- MODIFICADO: Usar @click directo de AlpineJS para abrir el popup --}}
                                            <button @click="window.open('{{ route('finance.ticket', $payment->id) }}', 'Ticket', 'width=400,height=600').focus()" class="text-indigo-600 hover:text-indigo-900 cursor-pointer">Ver Recibo</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No hay transacciones registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($paymentHistory->hasPages()) <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">{{ $paymentHistory->links() }}</div> @endif
            </div>
        </section>

        {{-- MODAL DE PAGO (Estilo SaaS) --}}
        @if($showPaymentModal)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                            
                            {{-- Modal Header --}}
                            <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Realizar Pago</h3>
                                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="px-6 py-6 bg-white">
                                {{-- Resumen --}}
                                <div class="mb-6 bg-gray-50 rounded-xl p-5 border border-gray-100 flex justify-between items-center">
                                    <div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Concepto</p><p class="text-sm font-bold text-gray-900">{{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo Académico' }}</p></div>
                                    <div class="text-right"><p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total</p><p class="text-xl font-bold text-gray-900">RD$ {{ number_format($amountToPay, 2) }}</p></div>
                                </div>

                                {{-- Método de Pago --}}
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de pago</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="cursor-pointer relative" wire:click="$set('paymentMethod', 'card')">
                                            <input type="radio" wire:model.live="paymentMethod" value="card" class="peer sr-only">
                                            <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 peer-checked:border-indigo-600 peer-checked:ring-1 peer-checked:ring-indigo-600 bg-white flex flex-col items-center gap-2">
                                                <svg class="w-6 h-6 text-gray-500 peer-checked:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 00-3-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                                <span class="text-sm font-medium text-gray-600 peer-checked:text-gray-900">Tarjeta (Cardnet)</span>
                                            </div>
                                            <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </label>
                                        <label class="cursor-pointer relative" wire:click="$set('paymentMethod', 'transfer')">
                                            <input type="radio" wire:model.live="paymentMethod" value="transfer" class="peer sr-only">
                                            <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 bg-white flex flex-col items-center gap-2">
                                                <svg class="w-6 h-6 text-gray-500 peer-checked:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                                <span class="text-sm font-medium text-gray-600 peer-checked:text-gray-900">Transferencia</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- NUEVA SECCIÓN: Tipo de Comprobante Fiscal --}}
                                <div class="mb-6 pt-4 border-t border-gray-100 animate-fade-in-up">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Comprobante Fiscal</label>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input id="ncf_b02" type="radio" wire:model.live="ncfType" value="B02" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                            <label for="ncf_b02" class="ml-3 block text-sm font-medium text-gray-700 cursor-pointer">Consumidor Final (B02)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="ncf_b01" type="radio" wire:model.live="ncfType" value="B01" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                            <label for="ncf_b01" class="ml-3 block text-sm font-medium text-gray-700 cursor-pointer">Crédito Fiscal (B01)</label>
                                        </div>
                                    </div>

                                    {{-- Campos extra para Crédito Fiscal --}}
                                    @if(isset($ncfType) && $ncfType === 'B01')
                                        <div class="mt-4 grid grid-cols-1 gap-4 animate-fade-in-up bg-gray-50 p-4 rounded-lg border border-gray-200">
                                            <div>
                                                <label for="rnc" class="block text-xs font-bold text-gray-700 uppercase mb-1">RNC / Cédula *</label>
                                                <input type="text" wire:model="rnc" id="rnc" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3" placeholder="101123456">
                                                @error('rnc') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label for="companyName" class="block text-xs font-bold text-gray-700 uppercase mb-1">Razón Social *</label>
                                                <input type="text" wire:model="companyName" id="companyName" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3" placeholder="Nombre de la empresa">
                                                @error('companyName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info Cardnet --}}
                                <div x-show="$wire.paymentMethod === 'card'" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                    <p class="text-sm text-gray-600 mb-1">Serás redirigido a <strong>Cardnet</strong> para pagar de forma segura.</p>
                                </div>

                                {{-- Campos Transferencia --}}
                                <div x-show="$wire.paymentMethod === 'transfer'" class="mt-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Referencia</label>
                                    <input type="text" wire:model="transferReference" class="w-full border-gray-300 rounded-lg shadow-sm py-2.5" placeholder="Ej: 00458822">
                                    @error('transferReference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                @error('general') <div class="mt-4 p-3 bg-red-100 text-red-700 rounded text-sm">{{ $message }}</div> @enderror
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <button wire:click="closeModal" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">Cancelar</button>
                                <button wire:click="initiatePayment" wire:loading.attr="disabled" class="inline-flex justify-center rounded-lg border border-transparent bg-gray-900 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50 transition-colors w-full sm:w-auto">
                                    <span wire:loading.remove>{{ $paymentMethod === 'card' ? 'Pagar con Tarjeta' : 'Reportar Pago' }}</span>
                                    <span wire:loading>Procesando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL PDF --}}
        <div x-data="{ show: false, pdfUrl: '' }" @open-pdf-modal.window="pdfUrl = $event.detail.url; show = true;" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="show = false; pdfUrl = ''"></div>
                <div class="inline-block w-full max-w-6xl p-4 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl relative">
                    <button @click="show = false; pdfUrl = ''" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    <div class="mt-8 bg-gray-100 rounded-lg overflow-hidden" style="height: 75vh;"><iframe :src="pdfUrl" frameborder="0" width="100%" height="100%"></iframe></div>
                </div>
            </div>
        </div>

    </div>

    {{-- FORMULARIO OCULTO PARA CARDNET --}}
    <form id="cardnet-form" method="POST" style="display:none;"></form>

    <script>
        document.addEventListener('livewire:init', () => {
            // Este listener original se mantiene por compatibilidad si el backend lo dispara en otros casos,
            // pero el botón de la tabla ahora usa AlpineJS directamente.
            Livewire.on('printTicket', event => { 
                // Fix para manejo flexible del evento (objeto o detalle)
                const data = event[0] || event; 
                if(data.url) window.open(data.url, 'Ticket', 'width=400,height=600').focus(); 
            });
            
            Livewire.on('submit-cardnet-form', event => {
                // Fix para Livewire 3 events structure
                const data = event.data || (Array.isArray(event) ? event[0].data : null); 
                
                if(!data) {
                    console.error('Datos de Cardnet inválidos:', event);
                    return alert('Error de configuración en pasarela.');
                }

                const form = document.getElementById('cardnet-form');
                if(!data.url) return alert('Error de configuración en pasarela (URL).');
                
                form.action = data.url;
                form.innerHTML = '';
                for (const [key, value] of Object.entries(data.fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                form.submit();
            });

            Livewire.on('open-pdf-modal', (event) => {
                // Fix para Livewire 3 events structure
                const url = event.url || (event[0] ? event[0].url : null) || (event.detail ? event.detail.url : null);
                if (url) window.open(url, '_blank');
            });
        });
    </script>
</div>