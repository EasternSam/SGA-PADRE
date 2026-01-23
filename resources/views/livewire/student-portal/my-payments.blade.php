<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Mis Pagos') }}
                </h2>
                <p class="text-sm text-gray-500">Historial y pagos pendientes</p>
            </div>
            <button wire:click="downloadFinancialReport" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Estado de Cuenta
            </button>
        </div>

        {{-- Mensajes Flash --}}
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Deudas Pendientes --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Pendientes</span>
                    Pagos por realizar
                </h3>

                @if($pendingDebts->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($pendingDebts as $debt)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-white flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">
                                            {{ $debt->paymentConcept->name ?? 'Cuota' }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ $debt->due_date ? $debt->due_date->format('d/m/Y') : '-' }}</span>
                                    </div>
                                    
                                    @if($debt->enrollment)
                                        <h4 class="font-bold text-gray-800 mb-1">{{ $debt->enrollment->courseSchedule->module->name ?? 'Módulo' }}</h4>
                                        <p class="text-xs text-gray-500 mb-3">{{ $debt->enrollment->courseSchedule->module->course->name ?? '' }}</p>
                                    @else
                                        <h4 class="font-bold text-gray-800 mb-3">{{ $debt->description ?? 'Pago General' }}</h4>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-xl font-bold text-gray-900">RD$ {{ number_format($debt->amount, 2) }}</span>
                                    <button wire:click="openPaymentModal({{ $debt->id }})" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Pagar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 bg-gray-50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">¡Estás al día!</h3>
                        <p class="mt-1 text-sm text-gray-500">No tienes pagos pendientes en este momento.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Historial --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Pagos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($paymentHistory as $payment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $payment->paymentConcept->name ?? 'Pago General' }}
                                        @if($payment->enrollment)
                                            <span class="text-gray-500 font-normal">- {{ $payment->enrollment->courseSchedule->module->name ?? '' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        RD$ {{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->gateway }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $payment->status === 'Completado' ? 'bg-green-100 text-green-800' : 
                                               ($payment->status === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($payment->status === 'Completado')
                                            <a href="{{ route('finance.ticket', $payment->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Ver Recibo</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay historial disponible.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $paymentHistory->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE PAGO ESTUDIANTE --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Realizar Pago</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Estás a punto de pagar: <span class="font-bold text-gray-800">RD$ {{ number_format($amountToPay, 2) }}</span>
                                    </p>

                                    {{-- Selección de Método --}}
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button type="button" wire:click="$set('paymentMethod', 'card')" 
                                                class="flex flex-col items-center justify-center p-3 border rounded-lg {{ $paymentMethod === 'card' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 hover:bg-gray-50' }}">
                                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                                <span class="text-xs font-bold">Tarjeta (Cardnet)</span>
                                            </button>
                                            <button type="button" wire:click="$set('paymentMethod', 'transfer')" 
                                                class="flex flex-col items-center justify-center p-3 border rounded-lg {{ $paymentMethod === 'transfer' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 hover:bg-gray-50' }}">
                                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                                <span class="text-xs font-bold">Transferencia</span>
                                            </button>
                                        </div>
                                        @error('paymentMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Info Cardnet --}}
                                    <div x-show="$wire.paymentMethod === 'card'" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                        <p class="text-sm text-gray-600">Serás redirigido a la plataforma segura de <strong>Cardnet</strong> para completar tu pago.</p>
                                        <div class="mt-2 flex justify-center space-x-2">
                                            {{-- Iconos de tarjetas --}}
                                            <div class="bg-white p-1 rounded border shadow-sm w-10 h-6"></div>
                                            <div class="bg-white p-1 rounded border shadow-sm w-10 h-6"></div>
                                        </div>
                                    </div>

                                    {{-- Campos Transferencia --}}
                                    <div x-show="$wire.paymentMethod === 'transfer'" class="mt-4">
                                        <div class="mb-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Referencia / Comprobante</label>
                                            <input type="text" wire:model="transferReference" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: 00458822">
                                            @error('transferReference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="text-xs text-gray-500 bg-yellow-50 p-3 rounded border border-yellow-200">
                                            Por favor deposita al Banco X, Cuenta Y. Tu pago será validado manualmente en 24-48 horas.
                                        </div>
                                    </div>

                                    @error('general')
                                        <div class="mt-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="initiatePayment" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove>{{ $paymentMethod === 'card' ? 'Ir a Pagar' : 'Reportar Pago' }}</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulario Oculto para Cardnet --}}
    <form id="cardnet-form" action="{{ $cardnetUrl }}" method="POST" style="display:none;">
        @foreach($cardnetFields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    {{-- Script de activación --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('submit-cardnet-form', () => {
                const form = document.getElementById('cardnet-form');
                if(form && form.action) {
                    console.log('Redirigiendo a Cardnet...', form.action);
                    form.submit();
                } else {
                    alert('Error técnico al iniciar pasarela.');
                }
            });
            
            Livewire.on('open-pdf-modal', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</div>