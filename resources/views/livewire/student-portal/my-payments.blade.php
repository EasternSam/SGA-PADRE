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
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 class="fixed bottom-6 right-6 z-50">
                <div class="bg-gray-900 text-white rounded-lg px-4 py-3 shadow-xl flex items-center gap-3 min-w-[300px]">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">
                        {{ session('message') }}
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- SECCIÓN 1: PENDIENTES DE PAGO --}}
        <section>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    Pagos Pendientes
                    @if($pendingDebts->count() > 0)
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-red-200">
                            {{ $pendingDebts->count() }}
                        </span>
                    @endif
                </h3>
            </div>
            
            @if($pendingDebts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($pendingDebts as $debt)
                        <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col h-full overflow-hidden">
                            <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex justify-between items-start">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 mb-3">
                                        {{ $debt->paymentConcept->name ?? 'Pendiente' }}
                                    </span>
                                    <h4 class="text-base font-bold text-gray-900 leading-snug">
                                        {{ $debt->enrollment->courseSchedule->module->name ?? 'Módulo General' }}
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $debt->enrollment->courseSchedule->module->course->name ?? 'Curso Académico' }}
                                    </p>
                                </div>
                                <div class="text-xs font-mono text-gray-400 bg-white border border-gray-100 px-2 py-1 rounded-md">
                                    #{{ $debt->id }}
                                </div>
                            </div>

                            <div class="p-6 flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Sección</p>
                                        <p class="font-medium text-gray-900 mt-1">{{ $debt->enrollment->courseSchedule->section_name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Vencimiento</p>
                                        <p class="font-medium {{ $debt->due_date && $debt->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }} mt-1">
                                            {{ $debt->due_date ? $debt->due_date->format('d/m/Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 pt-0 mt-auto">
                                <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-50">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-0.5">Total a Pagar</p>
                                        <p class="text-xl font-bold text-gray-900 tracking-tight">
                                            RD$ {{ number_format($debt->amount, 2) }}
                                        </p>
                                    </div>
                                    <button 
                                        wire:click="openPaymentModal({{ $debt->id }})"
                                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors"
                                    >
                                        Pagar Ahora
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-3 flex items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Estás al día</h3>
                    <p class="mt-1 text-sm text-gray-500">No tienes pagos pendientes en este momento.</p>
                </div>
            @endif
        </section>

        {{-- SECCIÓN 2: HISTORIAL DE PAGOS --}}
        <section>
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Historial de Transacciones</h3>
                    
                    <button wire:click="downloadFinancialReport" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="downloadFinancialReport" class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar Reporte
                        </span>
                        <span wire:loading wire:target="downloadFinancialReport" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generando...
                        </span>
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($paymentHistory as $payment)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment->created_at->format('d M, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>
                                                @if($payment->enrollment)
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $payment->enrollment->courseSchedule->module->name ?? '' }}
                                                    </div>
                                                @endif
                                                <div class="text-[10px] text-gray-400 font-mono mt-1">ID: {{ $payment->transaction_id ?? '---' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            @if(Str::contains(strtolower($payment->gateway), 'tarjeta'))
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 00-3-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            @else
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                            @endif
                                            <span class="font-medium text-gray-700">{{ $payment->gateway }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $payment->status === 'Completado' || $payment->status === 'Pagado',
                                            'bg-amber-50 text-amber-700 ring-amber-600/20' => $payment->status === 'Pendiente',
                                            'bg-red-50 text-red-700 ring-red-600/20' => in_array($payment->status, ['Fallido', 'Rechazado']),
                                        ])>
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ ($payment->status === 'Completado' || $payment->status === 'Pagado') ? 'bg-emerald-500' : ($payment->status === 'Pendiente' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                        RD$ {{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                                        No hay transacciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($paymentHistory->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $paymentHistory->links() }}
                    </div>
                @endif
            </div>
        </section>

        {{-- MODAL DE PAGO (Diseño Checkout SaaS) --}}
        @if($showPaymentModal)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                            
                            {{-- Modal Header --}}
                            <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900" id="modal-title">
                                    Completar Pago
                                </h3>
                                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="px-6 py-6 bg-white">
                                {{-- Resumen de Orden --}}
                                <div class="mb-8 bg-gray-50 rounded-xl p-5 border border-gray-100 flex justify-between items-center">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Concepto</p>
                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo Académico' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total</p>
                                        <p class="text-xl font-bold text-gray-900">
                                            RD$ {{ number_format($amountToPay, 2) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Selector de Método --}}
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de pago</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        {{-- Opción Tarjeta --}}
                                        <label class="cursor-pointer relative" wire:click="$set('paymentMethod', 'card')">
                                            <input type="radio" wire:model.live="paymentMethod" value="card" class="peer sr-only">
                                            <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 transition-all bg-white flex flex-col items-center gap-2 h-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500 peer-checked:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-600 peer-checked:text-gray-900">Tarjeta</span>
                                            </div>
                                            <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-gray-900 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </label>

                                        {{-- Opción Transferencia --}}
                                        <label class="cursor-pointer relative" wire:click="$set('paymentMethod', 'transfer')">
                                            <input type="radio" wire:model.live="paymentMethod" value="transfer" class="peer sr-only">
                                            <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 transition-all bg-white flex flex-col items-center gap-2 h-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500 peer-checked:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-600 peer-checked:text-gray-900">Transferencia</span>
                                            </div>
                                            <div class="absolute top-2 right-2 w-2 h-2 rounded-full bg-gray-900 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Formularios --}}
                                <div class="mt-6">
                                    {{-- FORMULARIO OCULTO PARA CARDNET --}}
                                    <form id="cardnet-form" style="display: none;">
                                        <input type="hidden" name="PWToken" id="PWToken" />
                                    </form>

                                    @if($paymentMethod === 'card')
                                        <div class="space-y-4 animate-fade-in-up" wire:key="card-form">
                                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex gap-3">
                                                <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <p class="text-sm text-indigo-800">
                                                    Al hacer clic en "Pagar Ahora", se abrirá una ventana segura de Cardnet para ingresar los datos de su tarjeta.
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-5 animate-fade-in-up" wire:key="transfer-form">
                                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wide mb-3">Cuentas para depósito</h4>
                                                <ul class="space-y-3 text-sm">
                                                    <li class="flex justify-between border-b border-gray-200 pb-2 last:border-0 last:pb-0">
                                                        <span class="text-gray-600">Banco Popular</span>
                                                        <span class="font-mono font-medium text-gray-900">792-45678-9</span>
                                                    </li>
                                                    <li class="flex justify-between border-b border-gray-200 pb-2 last:border-0 last:pb-0">
                                                        <span class="text-gray-600">Banreservas</span>
                                                        <span class="font-mono font-medium text-gray-900">220-00456-7</span>
                                                    </li>
                                                    <li class="flex justify-between pt-1">
                                                        <span class="text-xs text-gray-500">RNC Empresa</span>
                                                        <span class="text-xs font-mono text-gray-900">1-01-00000-0</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">Número de Referencia / Comprobante</label>
                                                <input type="text" wire:model="transferReference" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" placeholder="Ej. 2384920">
                                                @error('transferReference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg flex gap-3">
                                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <p class="text-xs text-amber-800 leading-relaxed">
                                                    Los pagos por transferencia requieren validación manual (aprox. 24h). Tu estado se actualizará automáticamente.
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @error('general') 
                                        <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-100 text-sm text-red-600 flex items-center gap-2">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                            {{ $message }}
                                        </div> 
                                    @enderror
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <button 
                                    type="button" 
                                    wire:click="closeModal" 
                                    class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="initiatePayment"
                                    wire:loading.attr="disabled"
                                    class="inline-flex justify-center rounded-lg border border-transparent bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full sm:w-auto min-w-[120px]"
                                >
                                    <span wire:loading.remove wire:target="initiatePayment">
                                        Pagar RD$ {{ number_format($amountToPay, 2) }}
                                    </span>
                                    <span wire:loading wire:target="initiatePayment" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Procesando
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- MODAL VISOR PDF --}}
        <div
            x-data="{ show: false, pdfUrl: '' }"
            @open-pdf-modal.window="
                pdfUrl = $event.detail.url;
                show = true;
            "
            x-show="show"
            x-on:keydown.escape.window="show = false; pdfUrl = ''"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
            style="display: none;"
        >
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="show = false; pdfUrl = ''" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div
                    x-show="show"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block w-full max-w-6xl p-4 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
                >
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">
                            Reporte Financiero
                        </h3>
                        <button @click="show = false; pdfUrl = ''" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-50 transition-colors">
                            <span class="sr-only">Cerrar</span>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 bg-gray-100 rounded-lg overflow-hidden" style="width: 100%; height: 75vh;">
                        <iframe :src="pdfUrl" frameborder="0" width="100%" height="100%">
                            Tu navegador no soporta iframes.
                        </iframe>
                    </div>

                    <div class="flex justify-end pt-4 mt-4 border-t border-gray-100">
                        <button @click="show = false; pdfUrl = ''" type="button" class="ml-3 inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPT DE CARDNET (Manejador de Eventos) --}}
    <script>
        document.addEventListener('livewire:init', () => {
            // Escuchar el evento de PHP para iniciar Cardnet
            Livewire.on('start-cardnet-payment', (data) => {
                // Verificar si 'data' es un array (Livewire 3) o un objeto directo
                // Si es un array (Livewire 3 a veces envuelve los parámetros), accedemos al primer elemento
                const payload = Array.isArray(data) ? data[0] : data;

                console.log('Evento start-cardnet-payment recibido', data);
                console.log('Payload procesado:', payload);
                
                // Configurar PWCheckout
                if (typeof PWCheckout !== 'undefined') {
                    PWCheckout.SetProperties({
                        "name": "Pago de Matrícula",
                        "email": payload.studentEmail,
                        "image": "{{ config('services.cardnet.image_url') }}",
                        "button_label": "Pagar #monto#",
                        "description": payload.description,
                        "currency": "DOP",
                        "amount": payload.amount,
                        "lang": "ESP",
                        "form_id": payload.formId, // ID del form oculto
                        "checkout_card": 1,
                        "autoSubmit": "false",
                        "empty": "false"
                    });
                    
                    // Definir callback global
                    window.OnTokenReceived = function(token) {
                        console.log('Token recibido de Cardnet:', token);
                        // Enviar token al componente Livewire
                        @this.call('processCardnetPayment', token);
                    };

                    // Vincular evento
                    PWCheckout.Bind("tokenCreated", window.OnTokenReceived);

                    // Abrir Lightbox
                    PWCheckout.OpenLightbox();
                } else {
                    console.error('PWCheckout no está definido. El script de Cardnet no se cargó.');
                    alert('Error técnico: No se pudo cargar la pasarela de pagos.');
                }
            });
        });
    </script>
</div>