<div class="p-4 sm:p-8 lg:p-10">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 ring-1 ring-inset ring-orange-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </span>
                Reportes Fiscales DGII
            </h1>
            <p class="mt-2 text-base text-gray-600">Generador de archivos TXT para Oficina Virtual (Formato 606 y 607).</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-gray-900/5 p-8 mb-8">
        <div class="max-w-md mx-auto sm:max-w-none">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Paso 1: Seleccione el Período a Reportar</h2>
            <div class="flex items-center gap-4">
                <input type="month" wire:model="yearMonth" class="block w-full max-w-xs rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-orange-600 font-bold text-lg bg-gray-50">
            </div>
            <p class="text-xs text-gray-500 mt-2">Los archivos generados filtrarán los NCFs pagados/generados exactamente dentro de este mes y año.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Tarjeta Formato 606 -->
        <div class="bg-gradient-to-br from-white to-orange-50/30 rounded-3xl shadow-lg ring-1 ring-gray-200 p-8 transform transition hover:-translate-y-1 hover:shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-orange-100 opacity-50 blur-2xl"></div>
            
            <h3 class="text-2xl font-black text-gray-900 mb-2">Formato 606</h3>
            <p class="text-sm font-semibold text-orange-600 uppercase tracking-widest mb-4">Compras de Bienes y Servicios</p>
            <p class="text-gray-600 text-sm mb-6 h-10">Genera el archivo TXT con todas las facturas de proveedores, ITBIS pagado y retenciones efectuadas en el período seleccionado.</p>
            
            @if (session()->has('warning_606'))
                <div class="rounded-lg bg-yellow-50 p-3 mb-4 text-xs font-medium text-yellow-800 border border-yellow-200">
                    {{ session('warning_606') }}
                </div>
            @endif

            <button wire:click="download606" class="w-full flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-bold text-white shadow-md hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:ring-offset-2 transition-all">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Descargar TXT 606
            </button>
        </div>

        <!-- Tarjeta Formato 607 -->
        <div class="bg-gradient-to-br from-white to-blue-50/30 rounded-3xl shadow-lg ring-1 ring-gray-200 p-8 transform transition hover:-translate-y-1 hover:shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-100 opacity-50 blur-2xl"></div>
            
            <h3 class="text-2xl font-black text-gray-900 mb-2">Formato 607</h3>
            <p class="text-sm font-semibold text-blue-600 uppercase tracking-widest mb-4">Ventas de Bienes y Servicios</p>
            <p class="text-gray-600 text-sm mb-6 h-10">Genera el archivo TXT con todos los recibos de estudiantes (Consumo y Crédito Fiscal) generados en el período.</p>
            
            @if (session()->has('warning_607'))
                <div class="rounded-lg bg-yellow-50 p-3 mb-4 text-xs font-medium text-yellow-800 border border-yellow-200">
                    {{ session('warning_607') }}
                </div>
            @endif

            <button wire:click="download607" class="w-full flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-md hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition-all">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Descargar TXT 607
            </button>
        </div>
    </div>
</div>
