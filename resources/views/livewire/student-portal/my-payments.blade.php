<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
    
    {{-- Header de la sección --}}
    <div class="md:flex md:items-center md:justify-between border-b border-gray-200 pb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:truncate sm:text-4xl sm:tracking-tight">
                Mis Finanzas
            </h2>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">
                Gestiona tus pagos pendientes, revisa tu historial de transacciones y mantén tu cuenta al día.
            </p>
        </div>
    </div>

    {{-- Alertas --}}
    @if (session()->has('message'))
        <div class="rounded-xl bg-green-50 p-4 border border-green-200 shadow-sm animate-fade-in-down">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- SECCIÓN 1: DEUDAS PENDIENTES --}}
    <section>
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-amber-100 p-2 rounded-lg text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Pendientes de Pago</h3>
        </div>
        
        @if($pendingDebts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($pendingDebts as $debt)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-24 h-24 text-indigo-500 transform rotate-12" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h14a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" /></svg>
                        </div>

                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        Pendiente
                                    </span>
                                    <span class="text-sm text-gray-400 font-medium">#{{ $debt->id }}</span>
                                </div>
                                
                                <h4 class="text-lg font-bold text-gray-900 mb-1">
                                    {{ $debt->courseSchedule->module->name ?? 'Módulo sin nombre' }}
                                </h4>
                                <p class="text-sm text-gray-500 mb-4">{{ $debt->courseSchedule->module->course->name ?? 'Curso General' }}</p>
                                
                                <div class="space-y-2 text-sm text-gray-600 mb-6">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>Sección: <span class="font-semibold">{{ $debt->courseSchedule->section_name }}</span></span>
                                    </div>
                                    @if($debt->courseSchedule->teacher)
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Prof. {{ $debt->courseSchedule->teacher->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wide">Total a Pagar</p>
                                    <p class="text-2xl font-black text-gray-900">
                                        RD$ {{ number_format($debt->payment->amount ?? $debt->courseSchedule->module->course->registration_fee ?? 0, 2) }}
                                    </p>
                                </div>
                                <button 
                                    wire:click="openPaymentModal({{ $debt->id }})"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-md hover:bg-indigo-700 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform active:scale-95"
                                >
                                    <span>Pagar</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-2xl p-10 text-center border-2 border-dashed border-gray-200">
                <div class="mx-auto h-16 w-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">¡Todo al día!</h3>
                <p class="mt-2 text-sm text-gray-500">No tienes pagos pendientes en este momento. ¡Excelente trabajo!</p>
            </div>
        @endif
    </section>

    {{-- SECCIÓN 2: HISTORIAL DE PAGOS --}}
    <section>
        <div class="flex items-center gap-3 mb-6 pt-6 border-t border-gray-200">
            <div class="bg-gray-100 p-2 rounded-lg text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Historial de Transacciones</h3>
        </div>

        <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Método</th>
                            <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Referencia</th>
                            <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-3 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider pr-6">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($paymentHistory as $payment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                    <div class="text-sm font-bold text-gray-900">{{ $payment->created_at->format('d M, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-600">
                                    <div class="font-semibold text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>
                                    @if($payment->enrollment)
                                        <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $payment->enrollment->courseSchedule->module->name ?? '' }}</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                                        {{ $payment->gateway }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-xs font-mono text-gray-500">{{ $payment->transaction_id ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-center">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold',
                                        'bg-green-100 text-green-700' => $payment->status === 'Completado',
                                        'bg-yellow-100 text-yellow-800' => $payment->status === 'Pendiente',
                                        'bg-red-100 text-red-700' => in_array($payment->status, ['Fallido', 'Rechazado']),
                                    ])>
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right">
                                    <span class="text-sm font-black text-gray-900">RD$ {{ number_format($payment->amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-sm text-gray-500">
                                    No se encontraron transacciones recientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentHistory->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6">
                    {{ $paymentHistory->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- MODAL DE PAGO INTEGRADO (Student Version) --}}
    @if($showPaymentModal)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- CORRECCIÓN AQUI: Cambio de bg-gray-900/80 a bg-gray-500 bg-opacity-75 para mayor compatibilidad --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200" style="margin-top: 28px;">
                        
                        {{-- Header Modal --}}
                        <div class="bg-indigo-600 px-6 py-5 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2" id="modal-title">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-indigo-200">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                                Pasarela de Pago Segura
                            </h3>
                            <button wire:click="closeModal" class="text-indigo-200 hover:text-white bg-indigo-500/20 hover:bg-indigo-500/40 p-2 rounded-full transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-6 py-6">
                            {{-- Resumen de Compra --}}
                            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 mb-6 text-center">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wide mb-1">Concepto de Pago</p>
                                <p class="text-base font-bold text-gray-900 mb-4 px-4 leading-tight">
                                    {{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo' }}
                                </p>
                                <div class="border-t border-gray-200 pt-3 flex justify-center items-baseline gap-1">
                                    <span class="text-sm text-gray-500 font-medium">Total:</span>
                                    <span class="text-3xl font-black text-indigo-600">RD$ {{ number_format($amountToPay, 2) }}</span>
                                </div>
                            </div>

                            {{-- Tabs de Método --}}
                            <div class="grid grid-cols-2 gap-3 mb-6 p-1 bg-gray-100 rounded-lg">
                                <button 
                                    wire:click="$set('paymentMethod', 'card')" 
                                    class="flex items-center justify-center gap-2 py-2.5 text-sm font-bold rounded-md transition-all {{ $paymentMethod === 'card' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700' }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Tarjeta
                                </button>
                                <button 
                                    wire:click="$set('paymentMethod', 'transfer')" 
                                    class="flex items-center justify-center gap-2 py-2.5 text-sm font-bold rounded-md transition-all {{ $paymentMethod === 'transfer' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700' }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                    Transferencia
                                </button>
                            </div>

                            {{-- Formulario Tarjeta --}}
                            @if($paymentMethod === 'card')
                                <div class="space-y-4 animate-fade-in">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Titular de la tarjeta</label>
                                        <input type="text" wire:model="cardName" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11" placeholder="Ej. JUAN PEREZ">
                                        @error('cardName') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Número de Tarjeta</label>
                                        <div class="relative">
                                            <input type="text" wire:model="cardNumber" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10 h-11 font-mono" placeholder="0000 0000 0000 0000">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h20v7a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-7zm20-2V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v2h20z"/></svg>
                                            </div>
                                        </div>
                                        @error('cardNumber') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="grid grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Expiración</label>
                                            <input type="text" wire:model="cardExpiry" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11 text-center" placeholder="MM/YY">
                                            @error('cardExpiry') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">CVC / CVV</label>
                                            <div class="relative">
                                                <input type="text" wire:model="cardCvc" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-9 h-11 text-center" placeholder="123">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-gray-400">
                                                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('cardCvc') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-400 pt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-green-500">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Conexión segura encriptada con SSL 256-bit
                                    </div>
                                </div>
                            @else
                                {{-- Formulario Transferencia --}}
                                <div class="space-y-4 animate-fade-in">
                                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                                        <h4 class="text-sm font-bold text-blue-900 mb-3 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                            </svg>
                                            Cuentas Bancarias Disponibles
                                        </h4>
                                        <ul class="text-sm text-blue-800 space-y-2 pl-1">
                                            <li class="flex justify-between border-b border-blue-200 pb-1">
                                                <span>Banco Popular</span>
                                                <span class="font-mono font-bold">792-45678-9</span>
                                            </li>
                                            <li class="flex justify-between border-b border-blue-200 pb-1">
                                                <span>Banreservas</span>
                                                <span class="font-mono font-bold">220-00456-7</span>
                                            </li>
                                            <li class="flex justify-between pt-1">
                                                <span class="text-blue-600">RNC (Empresa)</span>
                                                <span class="font-mono text-blue-600">1-01-00000-0</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Referencia / No. Comprobante</label>
                                        <input type="text" wire:model="transferReference" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11" placeholder="Ej. 2384920">
                                        @error('transferReference') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex items-start gap-2 text-xs text-amber-700 bg-amber-50 p-3 rounded-lg border border-amber-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 text-amber-500">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        <p><strong>Importante:</strong> Su pago quedará en estado <span class="font-bold">Pendiente</span> hasta que el departamento de contabilidad verifique el depósito.</p>
                                    </div>
                                </div>
                            @endif

                            @error('general') 
                                <div class="mt-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200 font-medium flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-200">
                            <button 
                                type="button" 
                                wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-95"
                            >
                                <span wire:loading.remove wire:target="processPayment">Pagar RD$ {{ number_format($amountToPay, 2) }}</span>
                                <span wire:loading wire:target="processPayment" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>