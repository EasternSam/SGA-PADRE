<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-12">
            <h1 class="text-3xl font-semibold text-gray-900">Reportes Fiscales DGII</h1>
            <p class="mt-3 text-gray-600 max-w-2xl">
                Genera archivos TXT en formato 606 y 607 para declaraciones mensuales en la Oficina Virtual.
            </p>
        </div>

        <div class="bg-white border border-gray-200 mb-8">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-1">
                        <label for="yearMonth" class="block text-sm font-medium text-gray-700 mb-2">
                            Período a reportar
                        </label>
                        <input 
                            type="month" 
                            id="yearMonth"
                            wire:model="yearMonth" 
                            class="block w-64 border-gray-300 focus:border-gray-900 focus:ring-gray-900 sm:text-sm"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Formato 606 -->
            <div class="bg-white border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-medium text-gray-900">Formato 606</h3>
                    <p class="mt-1 text-sm text-gray-600">Compras y gastos</p>
                </div>
                
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600 mb-6">
                        Facturas de proveedores, ITBIS pagado y retenciones del período seleccionado.
                    </p>
                    
                    @if (session()->has('warning_606'))
                        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-yellow-800">{{ session('warning_606') }}</p>
                        </div>
                    @endif

                    <button 
                        wire:click="download606" 
                        class="w-full px-4 py-2 bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
                    >
                        Descargar 606.txt
                    </button>
                </div>
            </div>

            <!-- Formato 607 -->
            <div class="bg-white border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-medium text-gray-900">Formato 607</h3>
                    <p class="mt-1 text-sm text-gray-600">Ventas e ingresos</p>
                </div>
                
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600 mb-6">
                        Recibos de estudiantes y NCFs emitidos durante el período.
                    </p>
                    
                    @if (session()->has('warning_607'))
                        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-yellow-800">{{ session('warning_607') }}</p>
                        </div>
                    @endif

                    <button 
                        wire:click="download607" 
                        class="w-full px-4 py-2 bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
                    >
                        Descargar 607.txt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
