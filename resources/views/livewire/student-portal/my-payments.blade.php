<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12 bg-gray-50/50">

    {{-- 1. Encabezado Moderno con Gradiente y Glassmorphism --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700 shadow-2xl ring-1 ring-white/10">
        {{-- Efectos de fondo --}}
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-soft-light"></div>
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none mix-blend-overlay"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none mix-blend-overlay"></div>

        <div class="relative px-8 py-12 md:flex md:items-center md:justify-between z-10">
            <div class="min-w-0 flex-1 space-y-2">
                <h2 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl drop-shadow-sm">
                    Mis Finanzas
                </h2>
                <p class="text-lg text-indigo-100 max-w-2xl font-medium leading-relaxed">
                    Visualiza tus compromisos, historial y gestiona tus pagos de forma segura.
                </p>
            </div>
            <div class="mt-8 md:mt-0 flex-shrink-0 hidden md:block">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl shadow-lg">
                    <svg class="h-16 w-16 text-indigo-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas (Diseño Flotante y Animado) --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-6 right-6 z-50">
            <div class="rounded-xl bg-white border border-green-100 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-4 max-w-md backdrop-blur-xl bg-white/90">
                <div class="flex-shrink-0 bg-green-500 rounded-full p-2 shadow-lg shadow-green-500/30">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-gray-900 text-sm">¡Operación Exitosa!</p>
                    <p class="text-sm text-gray-600 leading-tight mt-0.5">{{ session('message') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-md hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- SECCIÓN 1: PENDIENTES DE PAGO --}}
    <section>
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-white p-3 rounded-2xl text-amber-500 shadow-md border border-amber-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Pagos Pendientes</h3>
                    <p class="text-sm text-gray-500 font-medium">Regulariza tus cuentas para continuar accediendo.</p>
                </div>
            </div>
            @if($pendingDebts->count() > 0)
                <span class="inline-flex items-center rounded-full bg-red-50 px-4 py-1.5 text-sm font-bold text-red-600 ring-1 ring-inset ring-red-600/10 shadow-sm">
                    {{ $pendingDebts->count() }} compromiso(s)
                </span>
            @endif
        </div>
        
        @if($pendingDebts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach($pendingDebts as $debt)
                    <div class="group relative bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-1">
                        {{-- Barra superior de estado --}}
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                        
                        <div class="p-7">
                            {{-- Header Card --}}
                            <div class="flex justify-between items-start mb-6">
                                <div class="bg-amber-50/80 backdrop-blur-sm text-amber-700 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-amber-100/50 shadow-sm">
                                    Por Pagar
                                </div>
                                <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md">ID: #{{ $debt->id }}</span>
                            </div>
                            
                            {{-- Contenido Principal --}}
                            <div class="mb-8">
                                <h4 class="text-xl font-bold text-gray-900 leading-snug mb-2 group-hover:text-indigo-600 transition-colors">
                                    {{ $debt->courseSchedule->module->name ?? 'Módulo General' }}
                                </h4>
                                <p class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <span class="p-1 bg-indigo-50 rounded-md text-indigo-500">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </span>
                                    {{ $debt->courseSchedule->module->course->name ?? 'Curso' }}
                                </p>
                            </div>
                            
                            {{-- Detalles --}}
                            <div class="space-y-3 mb-8 bg-gray-50/80 p-5 rounded-2xl border border-gray-100/50">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Sección
                                    </span>
                                    <span class="font-semibold text-gray-900 bg-white px-2 py-0.5 rounded shadow-sm border border-gray-100">{{ $debt->courseSchedule->section_name ?? 'N/A' }}</span>
                                </div>
                                @if($debt->courseSchedule->teacher)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Docente
                                        </span>
                                        <span class="font-semibold text-gray-900 truncate max-w-[150px]">{{ Str::limit($debt->courseSchedule->teacher->name, 15) }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Card --}}
                            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Monto Total</p>
                                    <p class="text-2xl font-black text-gray-900 tracking-tight">
                                        <span class="text-sm font-bold text-gray-400 mr-0.5">RD$</span>{{ number_format($debt->payment->amount ?? $debt->courseSchedule->module->course->registration_fee ?? 0, 2) }}
                                    </p>
                                </div>
                                <button 
                                    wire:click="openPaymentModal({{ $debt->id }})"
                                    class="relative inline-flex group items-center justify-center px-6 py-3 text-sm font-bold text-white transition-all duration-200 bg-gray-900 font-pj rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 hover:bg-indigo-600 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 active:translate-y-0 overflow-hidden"
                                >
                                    <span class="relative z-10 flex items-center gap-2">
                                        Pagar Ahora
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-[2rem] p-12 text-center border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-5"></div>
                <div class="relative z-10">
                    <div class="mx-auto h-24 w-24 bg-green-50 text-green-500 rounded-full flex items-center justify-center mb-6 shadow-inner ring-8 ring-green-50/50 group-hover:scale-110 transition-transform duration-300">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
        <div class="flex items-center gap-4 mb-8 pt-8 border-t border-gray-200/60">
            <div class="bg-white p-3 rounded-2xl text-indigo-600 shadow-md border border-indigo-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Historial de Transacciones</h3>
                <p class="text-sm text-gray-500 font-medium">Registro detallado de tus movimientos.</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl shadow-gray-200/50 ring-1 ring-black ring-opacity-5 rounded-[2rem]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th scope="col" class="py-5 pl-8 pr-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Fecha</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Concepto</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Método</th>
                            <th scope="col" class="px-3 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Referencia</th>
                            <th scope="col" class="px-3 py-5 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Estado</th>
                            <th scope="col" class="px-3 py-5 text-right text-xs font-bold text-gray-400 uppercase tracking-widest pr-8">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($paymentHistory as $payment)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="whitespace-nowrap py-6 pl-8 pr-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ $payment->created_at->format('d M, Y') }}</span>
                                        <span class="text-xs text-gray-400 font-medium mt-0.5">{{ $payment->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-6">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-4 flex-shrink-0 border border-indigo-100 shadow-sm group-hover:scale-105 transition-transform">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ $payment->paymentConcept->name ?? 'Pago General' }}</div>
                                            @if($payment->enrollment)
                                                <div class="text-xs text-gray-500 mt-1 max-w-[200px] truncate bg-gray-50 inline-block px-1.5 py-0.5 rounded border border-gray-100">
                                                    {{ $payment->enrollment->courseSchedule->module->name ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-6 text-sm text-gray-600">
                                    <div class="flex items-center gap-2">
                                        @if(Str::contains(strtolower($payment->gateway), 'tarjeta'))
                                            <span class="p-1.5 bg-gray-100 rounded text-gray-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            </span>
                                        @else
                                            <span class="p-1.5 bg-gray-100 rounded text-gray-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                            </span>
                                        @endif
                                        <span class="font-medium text-gray-700">{{ $payment->gateway }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-6 text-sm text-gray-500">
                                    <span class="font-mono bg-gray-50 px-2.5 py-1 rounded-md text-xs border border-gray-200">{{ $payment->transaction_id ?? '---' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-6 text-center">
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset shadow-sm',
                                        'bg-green-50 text-green-700 ring-green-600/20' => $payment->status === 'Completado',
                                        'bg-amber-50 text-amber-700 ring-amber-600/20' => $payment->status === 'Pendiente',
                                        'bg-red-50 text-red-700 ring-red-600/20' => in_array($payment->status, ['Fallido', 'Rechazado']),
                                    ])>
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $payment->status === 'Completado' ? 'bg-green-400' : ($payment->status === 'Pendiente' ? 'bg-amber-400' : 'bg-red-400') }}"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 {{ $payment->status === 'Completado' ? 'bg-green-500' : ($payment->status === 'Pendiente' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                        </span>
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-6 pl-3 pr-8 text-right">
                                    <span class="text-sm font-black text-gray-900 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 group-hover:border-indigo-100 group-hover:bg-indigo-50 group-hover:text-indigo-700 transition-colors">RD$ {{ number_format($payment->amount, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-50 p-6 rounded-full mb-4 shadow-sm">
                                            <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Sin historial</h3>
                                        <p class="text-gray-500 mt-1">No se encontraron transacciones recientes.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentHistory->hasPages())
                <div class="border-t border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6">
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
                class="fixed inset-0 bg-gray-900/60 bg-opacity-75 backdrop-blur-sm transition-opacity"
                x-data x-init="$el.classList.add('opacity-100')"
            ></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div 
                        class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
                        style="margin-top: 28px;"
                    >
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-gray-900 to-indigo-900 px-6 py-6 flex items-center justify-between relative overflow-hidden">
                            <!-- Decoración de fondo -->
                            <div class="absolute top-0 right-0 -mt-6 -mr-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            
                            <h3 class="text-xl font-bold text-white flex items-center gap-3 relative z-10" id="modal-title">
                                <div class="bg-white/10 p-2.5 rounded-xl border border-white/10 shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span>Pasarela Segura</span>
                            </h3>
                            <button wire:click="closeModal" class="text-gray-300 hover:text-white bg-white/5 hover:bg-white/20 p-2 rounded-full transition-all relative z-10 backdrop-blur-md">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-8 py-8 bg-white">
                            {{-- Resumen de Compra Moderno --}}
                            <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 mb-8 relative group hover:border-indigo-100 hover:bg-indigo-50/30 transition-all duration-300">
                                <div class="absolute top-4 right-4">
                                    <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-bold text-gray-600 shadow-sm border border-gray-100">
                                        DETALLE
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Concepto</p>
                                <p class="text-lg font-bold text-gray-900 mb-6 leading-tight max-w-[85%]">
                                    {{ $selectedEnrollment->courseSchedule->module->name ?? 'Módulo Académico' }}
                                </p>
                                
                                <div class="flex items-end justify-between border-t border-gray-200/60 pt-4 border-dashed">
                                    <span class="text-sm text-gray-500 font-medium pb-1">Total a Pagar</span>
                                    <div class="text-right">
                                        <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-gray-900 to-indigo-800">
                                            <span class="text-xl text-gray-400 font-normal mr-1 align-top relative top-2">RD$</span>{{ number_format($amountToPay, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Selector de Método (Tabs Estilizados) --}}
                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <button 
                                    wire:click="$set('paymentMethod', 'card')" 
                                    class="relative flex flex-col items-center justify-center gap-3 py-5 px-2 rounded-2xl border-2 transition-all duration-300 group {{ $paymentMethod === 'card' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 shadow-sm' : 'border-gray-100 hover:border-gray-300 text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                                >
                                    <div class="{{ $paymentMethod === 'card' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-gray-100 text-gray-400 group-hover:bg-white group-hover:shadow-sm' }} p-2.5 rounded-xl transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wide">Tarjeta</span>
                                    @if($paymentMethod === 'card')
                                        <div class="absolute inset-0 border-2 border-indigo-600 rounded-2xl pointer-events-none"></div>
                                        <div class="absolute -top-1 -right-1 h-5 w-5 bg-indigo-600 rounded-full border-2 border-white flex items-center justify-center shadow-sm">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @endif
                                </button>
                                
                                <button 
                                    wire:click="$set('paymentMethod', 'transfer')" 
                                    class="relative flex flex-col items-center justify-center gap-3 py-5 px-2 rounded-2xl border-2 transition-all duration-300 group {{ $paymentMethod === 'transfer' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 shadow-sm' : 'border-gray-100 hover:border-gray-300 text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                                >
                                    <div class="{{ $paymentMethod === 'transfer' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-gray-100 text-gray-400 group-hover:bg-white group-hover:shadow-sm' }} p-2.5 rounded-xl transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wide">Transferencia</span>
                                    @if($paymentMethod === 'transfer')
                                        <div class="absolute inset-0 border-2 border-indigo-600 rounded-2xl pointer-events-none"></div>
                                        <div class="absolute -top-1 -right-1 h-5 w-5 bg-indigo-600 rounded-full border-2 border-white flex items-center justify-center shadow-sm">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @endif
                                </button>
                            </div>

                            {{-- Formulario Tarjeta --}}
                            @if($paymentMethod === 'card')
                                <div class="space-y-5 animate-fade-in">
                                    <div class="relative group">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-indigo-600 uppercase tracking-widest group-focus-within:text-indigo-600 transition-colors">Titular de la tarjeta</label>
                                        <input type="text" wire:model="cardName" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-2 transition-all placeholder-gray-300" placeholder="Ej. JUAN PEREZ">
                                        @error('cardName') <span class="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1.5 bg-red-50 p-1.5 rounded-lg border border-red-100"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="relative group">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Número de Tarjeta</label>
                                        <div class="relative">
                                            <input type="text" wire:model="cardNumber" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-11 h-12 pt-1 font-mono tracking-wide placeholder-gray-300 transition-all" placeholder="0000 0000 0000 0000">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h20v7a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-7zm20-2V6a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v2h20z"/></svg>
                                            </div>
                                        </div>
                                        @error('cardNumber') <span class="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1.5 bg-red-50 p-1.5 rounded-lg border border-red-100"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-5">
                                        <div class="relative group">
                                            <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Expiración</label>
                                            <input type="text" wire:model="cardExpiry" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-1 text-center font-mono placeholder-gray-300 transition-all" placeholder="MM/YY">
                                            @error('cardExpiry') <span class="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1.5 bg-red-50 p-1.5 rounded-lg border border-red-100"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                        </div>
                                        <div class="relative group">
                                            <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-indigo-600 uppercase tracking-widest">CVC / CVV</label>
                                            <div class="relative">
                                                <input type="text" wire:model="cardCvc" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-9 h-12 pt-1 text-center font-mono placeholder-gray-300 transition-all" placeholder="123">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            @error('cardCvc') <span class="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1.5 bg-red-50 p-1.5 rounded-lg border border-red-100"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-center gap-2 text-[10px] text-gray-400 pt-2 bg-gray-50/50 p-2.5 rounded-xl border border-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-green-500">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Transacción encriptada de extremo a extremo (SSL 256-bit)
                                    </div>
                                </div>
                            @else
                                {{-- Formulario Transferencia --}}
                                <div class="space-y-6 animate-fade-in">
                                    <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100 relative overflow-hidden group hover:bg-blue-50 transition-colors">
                                        <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-100 rounded-full blur-xl group-hover:bg-blue-200/50 transition-colors"></div>
                                        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-widest mb-4 flex items-center gap-2 relative z-10">
                                            <div class="bg-white p-1.5 rounded-lg shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                                </svg>
                                            </div>
                                            Cuentas Oficiales
                                        </h4>
                                        <ul class="text-sm text-blue-800 space-y-3 relative z-10">
                                            <li class="flex justify-between items-center bg-white/80 p-3 rounded-xl shadow-sm border border-blue-100">
                                                <span class="font-medium flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div> Banco Popular</span>
                                                <span class="font-mono font-bold text-gray-700 tracking-wide">792-45678-9</span>
                                            </li>
                                            <li class="flex justify-between items-center bg-white/80 p-3 rounded-xl shadow-sm border border-blue-100">
                                                <span class="font-medium flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Banreservas</span>
                                                <span class="font-mono font-bold text-gray-700 tracking-wide">220-00456-7</span>
                                            </li>
                                            <li class="flex justify-between items-center pt-1 px-2">
                                                <span class="text-blue-600 font-bold text-[10px] uppercase">RNC Empresa</span>
                                                <span class="font-mono text-blue-700 text-xs">1-01-00000-0</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="relative group">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Referencia / Comprobante</label>
                                        <input type="text" wire:model="transferReference" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-12 pt-2 placeholder-gray-300 transition-all" placeholder="Ej. 2384920">
                                        @error('transferReference') <span class="text-xs text-red-500 mt-1.5 font-medium flex items-center gap-1.5 bg-red-50 p-1.5 rounded-lg border border-red-100"><svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-start gap-3 text-xs text-amber-800 bg-amber-50 p-4 rounded-xl border border-amber-100">
                                        <div class="mt-0.5 bg-amber-100 p-1 rounded-full text-amber-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 flex-shrink-0">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed"><strong>Verificación Manual:</strong> Su pago quedará <span class="font-bold bg-amber-200/50 px-1 rounded mx-0.5">Pendiente</span> hasta que contabilidad valide el depósito (aprox. 24h laborables).</p>
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

                        <div class="bg-gray-50/80 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100 backdrop-blur-md">
                            <button 
                                type="button" 
                                wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full sm:w-auto justify-center items-center rounded-xl bg-gray-900 px-8 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-xl hover:bg-gray-800 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 group"
                            >
                                <span wire:loading.remove wire:target="processPayment" class="flex items-center gap-2">
                                    Confirmar Pago
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
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
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-gray-700 border border-gray-200 shadow-sm hover:bg-gray-50 hover:text-gray-900 focus:outline-none transition-all duration-200"
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