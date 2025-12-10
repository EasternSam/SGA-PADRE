<div class="space-y-8">
    
    {{-- Header de la sección --}}
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Mis Finanzas
            </h2>
            <p class="mt-1 text-sm text-gray-500">Administra tus pagos pendientes y revisa tu historial.</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            {{-- Botón opcional para recargar o soporte --}}
        </div>
    </div>

    {{-- Alertas --}}
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 border-l-4 border-green-400 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
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
    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-5 sm:px-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Pendientes de Pago
            </h3>
        </div>
        
        @if($pendingDebts->count() > 0)
            <ul role="list" class="divide-y divide-gray-100">
                @foreach($pendingDebts as $debt)
                    <li class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-x-6 gap-y-4 py-5 px-4 sm:px-6 hover:bg-gray-50 transition-colors">
                        <div class="min-w-0">
                            <div class="flex items-start gap-x-3">
                                <p class="text-sm font-semibold leading-6 text-gray-900">
                                    {{ $debt->courseSchedule->module->name ?? 'Módulo sin nombre' }}
                                </p>
                                <span class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-yellow-50 text-yellow-800 ring-yellow-600/20">
                                    Pendiente
                                </span>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                <p class="truncate">{{ $debt->courseSchedule->module->course->name ?? 'Curso General' }}</p>
                                <svg viewBox="0 0 2 2" class="h-0.5 w-0.5 fill-current"><circle cx="1" cy="1" r="1" /></svg>
                                <p class="whitespace-nowrap">Sección: {{ $debt->courseSchedule->section_name }}</p>
                            </div>
                        </div>
                        <div class="flex flex-none items-center gap-x-4">
                            <div class="text-right">
                                <p class="text-sm leading-6 text-gray-900 font-bold">RD$ {{ number_format($debt->courseSchedule->module->price ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-500">Total a pagar</p>
                            </div>
                            <button 
                                wire:click="openPaymentModal({{ $debt->id }})"
                                class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform active:scale-95"
                            >
                                Pagar Ahora
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">¡Estás al día!</h3>
                <p class="mt-1 text-sm text-gray-500">No tienes pagos pendientes en este momento.</p>
            </div>
        @endif
    </div>

    {{-- SECCIÓN 2: HISTORIAL DE PAGOS --}}
    <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-5 sm:px-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Historial de Transacciones
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Fecha</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Concepto</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Método</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Referencia</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:pr-6">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($paymentHistory as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                                {{ $payment->created_at->format('d M, Y') }}
                                <div class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <div class="font-medium text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>
                                @if($payment->enrollment)
                                    <div class="text-xs">{{ $payment->enrollment->courseSchedule->module->name ?? '' }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->gateway }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-mono text-gray-500">{{ $payment->transaction_id ?? '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span @class([
                                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                    'bg-green-50 text-green-700 ring-green-600/20' => $payment->status === 'Completado',
                                    'bg-yellow-50 text-yellow-800 ring-yellow-600/20' => $payment->status === 'Pendiente',
                                    'bg-red-50 text-red-700 ring-red-600/10' => in_array($payment->status, ['Fallido', 'Rechazado']),
                                ])>
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-bold text-gray-900 sm:pr-6">
                                RD$ {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                Aún no tienes historial de pagos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6">
            {{ $paymentHistory->links() }}
        </div>
    </div>

    {{-- 
        MODAL DE PAGO (Student Version) 
        Diseño centrado y limpio para el estudiante.
    --}}
    @if($showPaymentModal)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                        
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Realizar Pago</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Estás pagando: <span class="font-bold text-gray-800">{{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo' }}</span>
                                        </p>
                                        <p class="text-2xl font-bold text-indigo-600 mt-2 mb-4">
                                            RD$ {{ number_format($amountToPay, 2) }}
                                        </p>

                                        {{-- Tabs de Método --}}
                                        <div class="grid grid-cols-2 gap-2 mb-4">
                                            <button wire:click="$set('paymentMethod', 'card')" class="py-2 text-sm font-medium rounded-md border {{ $paymentMethod === 'card' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                                Tarjeta Crédito/Débito
                                            </button>
                                            <button wire:click="$set('paymentMethod', 'transfer')" class="py-2 text-sm font-medium rounded-md border {{ $paymentMethod === 'transfer' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                                Transferencia
                                            </button>
                                        </div>

                                        {{-- Formulario Tarjeta --}}
                                        @if($paymentMethod === 'card')
                                            <div class="space-y-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Nombre en la tarjeta</label>
                                                    <input type="text" wire:model="cardName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej. Juan Perez">
                                                    @error('cardName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Número de Tarjeta</label>
                                                    <div class="relative">
                                                        <input type="text" wire:model="cardNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10" placeholder="0000 0000 0000 0000">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h20v7a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-7zm20-2V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v2h20z"/></svg>
                                                        </div>
                                                    </div>
                                                    @error('cardNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700">Expiración (MM/YY)</label>
                                                        <input type="text" wire:model="cardExpiry" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="MM/YY">
                                                        @error('cardExpiry') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700">CVC</label>
                                                        <input type="text" wire:model="cardCvc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="123">
                                                        @error('cardCvc') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <p class="text-[10px] text-gray-400 text-center pt-1"><i class="fas fa-lock"></i> Transacción segura encriptada</p>
                                            </div>
                                        @else
                                            {{-- Formulario Transferencia --}}
                                            <div class="space-y-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                                <div class="text-xs text-gray-600 mb-2">
                                                    <p><strong>Banco:</strong> Banco Popular</p>
                                                    <p><strong>Cuenta:</strong> 123-45678-9</p>
                                                    <p><strong>RNC:</strong> 1-23-45678-9</p>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Referencia de Transferencia / Comprobante</label>
                                                    <input type="text" wire:model="transferReference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej. 23489230">
                                                    @error('transferReference') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <p class="text-[10px] text-amber-600 mt-2">Nota: Su pago quedará pendiente hasta que validemos la transferencia.</p>
                                            </div>
                                        @endif

                                        @error('general') 
                                            <div class="mt-2 text-sm text-red-600 bg-red-50 p-2 rounded">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button 
                                type="button" 
                                wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="processPayment">Pagar RD$ {{ number_format($amountToPay, 2) }}</span>
                                <span wire:loading wire:target="processPayment">Procesando...</span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
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