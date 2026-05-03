<div class="min-h-screen bg-gray-50 pb-12">
    
    <header class="bg-white shadow-sm mb-6 border-b border-gray-200">
        <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col gap-4">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        {{ __('Reportes Fiscales DGII') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">Generador de archivos TXT para Oficina Virtual (Formato 606 y 607).</p>
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-4 space-y-8">

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 p-8 transition-all hover:shadow-md border border-gray-100">
            <div class="max-w-3xl mx-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    Paso 1: Seleccione el Período a Reportar
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Año y Mes</label>
                        <input type="month" wire:model="yearMonth" class="block w-full max-w-xs rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-orange-600 font-bold text-lg bg-gray-50 hover:bg-white transition-colors">
                    </div>
                    <div class="rounded-xl bg-blue-50 p-4 ring-1 ring-inset ring-blue-100 flex gap-3 items-start">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-900 mb-1">Información importante</p>
                            <p class="text-sm text-blue-800 leading-tight">Los archivos generados filtrarán los NCFs pagados/generados exactamente dentro de este mes y año.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Tarjeta Formato 606 -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden relative group hover:shadow-xl transition-all">
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600"></div>
                
                <div class="p-8 relative">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-orange-100 opacity-30 blur-2xl group-hover:scale-110 transition-transform"></div>
                    
                    <div class="flex items-start gap-4 mb-6 relative">
                        <div class="flex-shrink-0 h-14 w-14 rounded-xl bg-orange-100 flex items-center justify-center ring-4 ring-orange-50 group-hover:ring-orange-100 transition-all">
                            <svg class="h-7 w-7 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 mb-1">Formato 606</h3>
                            <p class="text-sm font-bold text-orange-600 uppercase tracking-wider">Compras de Bienes y Servicios</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 text-sm leading-relaxed mb-6 relative">
                        Genera el archivo TXT con todas las facturas de proveedores, ITBIS pagado y retenciones efectuadas en el período seleccionado.
                    </p>
                    
                    @if (session()->has('warning_606'))
                        <div class="rounded-xl bg-yellow-50 p-4 mb-6 border border-yellow-200">
                            <p class="text-xs font-semibold text-yellow-900">{{ session('warning_606') }}</p>
                        </div>
                    @endif

                    <button wire:click="download606" class="w-full flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:ring-offset-2 transition-all group-hover:-translate-y-0.5 relative">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Descargar TXT 606
                    </button>
                </div>
            </div>

            <!-- Tarjeta Formato 607 -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden relative group hover:shadow-xl transition-all">
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600"></div>
                
                <div class="p-8 relative">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-blue-100 opacity-30 blur-2xl group-hover:scale-110 transition-transform"></div>
                    
                    <div class="flex items-start gap-4 mb-6 relative">
                        <div class="flex-shrink-0 h-14 w-14 rounded-xl bg-blue-100 flex items-center justify-center ring-4 ring-blue-50 group-hover:ring-blue-100 transition-all">
                            <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 mb-1">Formato 607</h3>
                            <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Ventas de Bienes y Servicios</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 text-sm leading-relaxed mb-6 relative">
                        Genera el archivo TXT con todos los recibos de estudiantes (Consumo y Crédito Fiscal) generados en el período.
                    </p>
                    
                    @if (session()->has('warning_607'))
                        <div class="rounded-xl bg-yellow-50 p-4 mb-6 border border-yellow-200">
                            <p class="text-xs font-semibold text-yellow-900">{{ session('warning_607') }}</p>
                        </div>
                    @endif

                    <button wire:click="download607" class="w-full flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition-all group-hover:-translate-y-0.5 relative">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Descargar TXT 607
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
