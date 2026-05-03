<div class="min-h-screen bg-gray-50 pb-12">
    
    <header class="bg-white shadow-sm mb-6 border-b border-gray-200">
        <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col gap-4">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        {{ __('Cierre de Período Contable') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Bloquea los registros contables hasta una fecha específica para asegurar la integridad fiscal e impedir modificaciones a períodos que ya fueron reportados o auditados.
                    </p>
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-4 space-y-8">

        @if (session()->has('success'))
            <div class="rounded-2xl bg-green-50 p-6 border border-green-200 shadow-sm animate-fade-in-up">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 bg-green-500 rounded-full p-1.5 shadow-sm">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-green-800 uppercase tracking-widest">Cierre Actualizado</h3>
                        <p class="text-base font-medium text-green-900 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow-xl ring-1 ring-gray-200 rounded-3xl overflow-hidden relative">
            <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-red-500 via-rose-500 to-red-600"></div>
            
            <div class="px-8 py-12 md:px-16 md:py-16">
                <div class="max-w-2xl mx-auto">
                    <div class="flex items-center justify-center mb-8">
                        <div class="rounded-full bg-red-100 p-6 shadow-lg ring-4 ring-red-50">
                            <svg class="h-12 w-12 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-center text-2xl font-black text-gray-900 mb-3">Bloqueo de Transacciones</h3>
                    <p class="text-center text-sm text-gray-600 mb-10 leading-relaxed">
                        Establece la <strong class="text-red-600">Fecha de Cierre</strong>. El motor contable rechazará automáticamente cualquier asiento, pago o factura que se intente crear o modificar con una fecha igual o anterior a la fecha seleccionada.
                    </p>

                    <form wire:submit.prevent="save" class="space-y-8">
                        <div class="bg-gray-50 rounded-2xl p-8 border border-gray-200">
                            <label for="lock_date" class="block text-sm font-bold leading-6 text-gray-900 mb-3">Fecha de Cierre (Cerrado Hasta)</label>
                            <input type="date" id="lock_date" wire:model="lock_date" class="block w-full rounded-xl border-0 py-4 px-5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-red-600 sm:text-lg sm:leading-6 font-mono text-center bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                            @error('lock_date') <span class="text-red-500 font-medium text-xs block mt-2 text-center">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <button type="button" wire:click="$set('lock_date', null)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 text-sm font-semibold leading-6 text-gray-700 px-6 py-3 bg-white hover:bg-gray-100 rounded-xl transition-all shadow-sm ring-1 ring-inset ring-gray-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Abrir Período
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-8 py-3 text-sm font-bold shadow-lg text-white hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 transition-all hover:shadow-xl hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Aplicar Cierre y Bloquear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
