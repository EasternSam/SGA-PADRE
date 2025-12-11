<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
    
    {{-- 1. Encabezado Moderno con Gradiente --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-700 shadow-xl">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-3xl"></div>
        <div class="relative px-8 py-12 md:flex md:items-center md:justify-between z-10">
            <div class="min-w-0 flex-1">
                <h2 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Mis Finanzas
                </h2>
                <p class="mt-4 text-lg text-indigo-100 max-w-2xl font-light">
                    Visualiza tus compromisos pendientes, historial de transacciones y gestiona tus pagos de forma segura y rápida.
                </p>
            </div>
            <div class="mt-6 md:mt-0 flex-shrink-0 hidden md:block opacity-80">
                <svg class="h-24 w-24 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        {{-- Decoración de fondo --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- Alertas (Diseño Flotante) --}}
    @if (session()->has('message'))
        <div class="fixed bottom-5 right-5 z-50 animate-fade-in-up">
            <div class="rounded-xl bg-white border-l-4 border-green-500 p-4 shadow-2xl flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-2">
                    <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900">¡Operación Exitosa!</p>
                    <p class="text-sm text-gray-600">{{ session('message') }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- SECCIÓN 1: PENDIENTES DE PAGO --}}
    <section>
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-amber-100 p-3 rounded-2xl text-amber-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Pagos Pendientes</h3>
                    <p class="text-sm text-gray-500">Regulariza tus cuentas para continuar accediendo al contenido.</p>
                </div>
            </div>
            @if($pendingDebts->count() > 0)
                <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                    {{ $pendingDebts->count() }} compromiso(s)
                </span>
            @endif
        </div>
        
        @if($pendingDebts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach($pendingDebts as $debt)
                    <div class="group relative bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                        {{-- Barra superior de estado --}}
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>
                        
                        <div class="p-6">
                            {{-- Header Card --}}
                            <div class="flex justify-between items-start mb-6">
                                <div class="bg-amber-50 text-amber-700 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border border-amber-100">
                                    Por Pagar
                                </div>
                                <span class="text-xs font-mono text-gray-400">ID: #{{ $debt->id }}</span>
                            </div>
                            
                            {{-- Contenido Principal --}}
                            <div class="mb-6">
                                <h4 class="text-xl font-bold text-gray-900 leading-tight mb-2 group-hover:text-indigo-600 transition-colors">
                                    {{ $debt->courseSchedule->module->name ?? 'Módulo General' }}
                                </h4>
                                <p class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $debt->courseSchedule->module->course->name ?? 'Curso' }}
                                </p>
                            </div>
                            
                            {{-- Detalles --}}
                            <div class="space-y-3 mb-8 bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Sección
                                    </span>
                                    <span class="font-semibold text-gray-900">{{ $debt->courseSchedule->section_name ?? 'N/A' }}</span>
                                </div>
                                @if($debt->courseSchedule->teacher)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Docente
                                        </span>
                                        <span class="font-semibold text-gray-900 truncate max-w-[150px]">{{ $debt->courseSchedule->teacher->name }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Card --}}
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Monto Total</p>
                                    <p class="text-3xl font-black text-gray-900 tracking-tight">
                                        <span class="text-lg font-normal text-gray-500 mr-1">RD$</span>{{ number_format($debt->payment->amount ?? $debt->courseSchedule->module->course->registration_fee ?? 0, 2) }}
                                    </p>
                                </div>
                                <button 
                                    wire:click="openPaymentModal({{ $debt->id }})"
                                    class="relative inline-flex group items-center justify-center px-6 py-3 text-sm font-bold text-white transition-all duration-200 bg-indigo-600 font-pj rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0"
                                >
                                    <span>Pagar Ahora</span>
                                    <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))]"></div>
                <div class="relative z-10">
                    <div class="mx-auto h-24 w-24 bg-green-50 text-green-500 rounded-full flex items-center justify-center mb-6 shadow-inner ring-8 ring-green-50/50">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">¡Todo al día!</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">No tienes deudas pendientes en este momento. Disfruta de tus clases sin preocupaciones.</p>
                </div>
            </div>
        @endif
    </section>

    {{-- SECCIÓN 2: HISTORIAL DE PAGOS --}}
    <section>
        <div class="flex items-center gap-4 mb-8 pt-8 border-t border-gray-200">
            <div class="bg-indigo-100 p-3 rounded-2xl text-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Historial de Transacciones</h3>
                <p class="text-sm text-gray-500">Registro detallado de todos tus movimientos financieros.</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl ring-1 ring-black ring-opacity-5 rounded-3xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th scope="col" class="py-5 pl-8 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Método</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Referencia</th>
                            <th scope="col" class="px-3 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-3 py-5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider pr-8">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($paymentHistory as $payment)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="whitespace-nowrap py-5 pl-8 pr-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ $payment->created_at->format('d M, Y') }}</span>
                                        <span class="text-xs text-gray-400 font-medium">{{ $payment->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-5">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3 flex-shrink-0">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>
                                            @if($payment->enrollment)
                                                <div class="text-xs text-gray-500 mt-0.5 max-w-xs truncate" title="{{ $payment->enrollment->courseSchedule->module->name ?? '' }}">
                                                    {{ $payment->enrollment->courseSchedule->module->name ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        @if(Str::contains(strtolower($payment->gateway), 'tarjeta'))
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @else
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                        @endif
                                        <span class="font-medium">{{ $payment->gateway }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                    <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">{{ $payment->transaction_id ?? '---' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-center">
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset',
                                        'bg-green-50 text-green-700 ring-green-600/20' => $payment->status === 'Completado',
                                        'bg-yellow-50 text-yellow-700 ring-yellow-600/20' => $payment->status === 'Pendiente',
                                        'bg-red-50 text-red-700 ring-red-600/20' => in_array($payment->status, ['Fallido', 'Rechazado']),
                                    ])>
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $payment->status === 'Completado' ? 'bg-green-400' : ($payment->status === 'Pendiente' ? 'bg-yellow-400' : 'bg-red-400') }}"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 {{ $payment->status === 'Completado' ? 'bg-green-500' : ($payment->status === 'Pendiente' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                        </span>
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-5 pl-3 pr-8 text-right">
                                    <span class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">RD$ {{ number_format($payment->amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-50 p-4 rounded-full mb-3">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="text-gray-500 font-medium">No se encontraron transacciones recientes.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentHistory->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
                    {{ $paymentHistory->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- MODAL DE PAGO INTEGRADO (Student Version) - REDISEÑADO --}}
    @if($showPaymentModal)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop con Blur --}}
            <div 
                class="fixed inset-0 bg-gray-600 bg-opacity-75 backdrop-blur-sm transition-opacity"
                x-data x-init="$el.classList.add('opacity-100')"
            ></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div 
                        class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200 animate-fade-in-up"
                        style="margin-top: 28px;"
                    >
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-6 flex items-center justify-between relative overflow-hidden">
                            <!-- Decoración de fondo -->
                            <div class="absolute top-0 right-0 -mt-2 -mr-2 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
                            
                            <h3 class="text-xl font-bold text-white flex items-center gap-3 relative z-10" id="modal-title">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span>Pasarela de Pago Segura</span>
                            </h3>
                            <button wire:click="closeModal" class="text-indigo-100 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all relative z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-8 py-8">
                            {{-- Resumen de Compra Moderno --}}
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-8 relative group hover:border-indigo-200 transition-colors">
                                <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-xl rounded-tr-xl">DETALLE</div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-2">Concepto a Pagar</p>
                                <p class="text-lg font-bold text-gray-900 mb-6 leading-tight">
                                    {{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo Académico' }}
                                </p>
                                
                                <div class="flex items-end justify-between border-t border-gray-200 pt-4">
                                    <span class="text-sm text-gray-500 font-medium pb-1">Monto Total</span>
                                    <div class="text-right">
                                        <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                                            RD$ {{ number_format($amountToPay, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Selector de Método (Tabs Estilizados) --}}
                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <button 
                                    wire:click="$set('paymentMethod', 'card')" 
                                    class="relative flex flex-col items-center justify-center gap-2 py-4 px-2 rounded-2xl border-2 transition-all duration-200 {{ $paymentMethod === 'card' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-100 hover:border-gray-300 text-gray-500 hover:bg-gray-50' }}"
                                >
                                    <div class="{{ $paymentMethod === 'card' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400' }} p-2 rounded-full transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold">Tarjeta Crédito/Débito</span>
                                    @if($paymentMethod === 'card')
                                        <div class="absolute -top-1 -right-1 h-4 w-4 bg-indigo-600 rounded-full border-2 border-white"></div>
                                    @endif
                                </button>
                                
                                <button 
                                    wire:click="$set('paymentMethod', 'transfer')" 
                                    class="relative flex flex-col items-center justify-center gap-2 py-4 px-2 rounded-2xl border-2 transition-all duration-200 {{ $paymentMethod === 'transfer' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-gray-100 hover:border-gray-300 text-gray-500 hover:bg-gray-50' }}"
                                >
                                    <div class="{{ $paymentMethod === 'transfer' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400' }} p-2 rounded-full transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold">Transferencia</span>
                                    @if($paymentMethod === 'transfer')
                                        <div class="absolute -top-1 -right-1 h-4 w-4 bg-indigo-600 rounded-full border-2 border-white"></div>
                                    @endif
                                </button>
                            </div>

                            {{-- Formulario Tarjeta --}}
                            @if($paymentMethod === 'card')
                                <div class="space-y-5 animate-fade-in">
                                    <div class="relative">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-xs font-bold text-indigo-600 uppercase tracking-wider">Titular de la tarjeta</label>
                                        <input type="text" wire:model="cardName" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-2 transition-shadow" placeholder="Ej. JUAN PEREZ">
                                        @error('cardName') <span class="text-xs text-red-500 mt-1 font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-xs font-bold text-indigo-600 uppercase tracking-wider">Número de Tarjeta</label>
                                        <div class="relative">
                                            <input type="text" wire:model="cardNumber" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-11 h-12 pt-1 font-mono tracking-wide" placeholder="0000 0000 0000 0000">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h20v7a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-7zm20-2V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v2h20z"/></svg>
                                            </div>
                                        </div>
                                        @error('cardNumber') <span class="text-xs text-red-500 mt-1 font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-5">
                                        <div class="relative">
                                            <label class="absolute -top-2 left-3 bg-white px-1 text-xs font-bold text-indigo-600 uppercase tracking-wider">Expiración</label>
                                            <input type="text" wire:model="cardExpiry" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-1 text-center font-mono" placeholder="MM/YY">
                                            @error('cardExpiry') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="relative">
                                            <label class="absolute -top-2 left-3 bg-white px-1 text-xs font-bold text-indigo-600 uppercase tracking-wider">CVC / CVV</label>
                                            <div class="relative">
                                                <input type="text" wire:model="cardCvc" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-9 h-12 pt-1 text-center font-mono" placeholder="123">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-400">
                                                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('cardCvc') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-400 pt-2 bg-gray-50 p-2 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-green-500">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Transacción encriptada de extremo a extremo (SSL 256-bit)
                                    </div>
                                </div>
                            @else
                                {{-- Formulario Transferencia --}}
                                <div class="space-y-6 animate-fade-in">
                                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 relative overflow-hidden">
                                        <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-200/30 rounded-full blur-xl"></div>
                                        <h4 class="text-sm font-bold text-blue-900 mb-4 flex items-center gap-2 relative z-10">
                                            <div class="bg-white p-1.5 rounded-lg shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-blue-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                                </svg>
                                            </div>
                                            Cuentas Bancarias Oficiales
                                        </h4>
                                        <ul class="text-sm text-blue-800 space-y-3 relative z-10">
                                            <li class="flex justify-between items-center bg-white/60 p-2 rounded-lg">
                                                <span class="font-medium">Banco Popular</span>
                                                <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-blue-100">792-45678-9</span>
                                            </li>
                                            <li class="flex justify-between items-center bg-white/60 p-2 rounded-lg">
                                                <span class="font-medium">Banreservas</span>
                                                <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-blue-100">220-00456-7</span>
                                            </li>
                                            <li class="flex justify-between items-center pt-1 px-2">
                                                <span class="text-blue-600 font-bold text-xs uppercase">RNC Empresa</span>
                                                <span class="font-mono text-blue-700">1-01-00000-0</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-xs font-bold text-indigo-600 uppercase tracking-wider">Referencia / No. Comprobante</label>
                                        <input type="text" wire:model="transferReference" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-2" placeholder="Ej. 2384920">
                                        @error('transferReference') <span class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-start gap-3 text-xs text-amber-800 bg-amber-50 p-4 rounded-xl border border-amber-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 text-amber-500 mt-0.5">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        <p><strong>Aviso Importante:</strong> Su pago quedará en estado <span class="font-bold bg-amber-200/50 px-1 rounded">Pendiente de Verificación</span> hasta que nuestro departamento de contabilidad valide el depósito en la cuenta seleccionada. Esto puede tomar hasta 24h laborables.</p>
                                    </div>
                                </div>
                            @endif

                            @error('general') 
                                <div class="mt-6 text-sm text-red-600 bg-red-50 p-4 rounded-xl border border-red-200 font-medium flex items-center gap-3 animate-pulse">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 flex-shrink-0">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <button 
                                type="button" 
                                wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full sm:w-auto justify-center items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            >
                                <span wire:loading.remove wire:target="processPayment">Pagar RD$ {{ number_format($amountToPay, 2) }}</span>
                                <span wire:loading wire:target="processPayment" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 hover:text-gray-900 focus:outline-none transition-colors"
                            >
                                Cancelar Operación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>