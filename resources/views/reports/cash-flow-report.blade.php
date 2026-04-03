<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-slate-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Flujo de Efectivo Simplificado</h2>
        <p class="text-sm text-gray-500">Periodo Analizado: {{ \Carbon\Carbon::parse($data['date_from'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d M, Y') }}</p>
    </div>

    <div class="max-w-4xl mx-auto mt-12 mb-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Header Flujo -->
            <div class="bg-slate-800 px-6 py-6 text-white text-center">
                <h3 class="text-lg font-medium opacity-80 uppercase tracking-widest">Estado de Resultados General</h3>
                <p class="text-xs mt-2 opacity-60">Rentabilidad Bruta Calculada</p>
            </div>

            <div class="p-8">
                <!-- Ingresos -->
                <div class="flex justify-between items-center py-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-emerald-100 p-2 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 uppercase">Ingresos Operativos Totales</span>
                            <span class="text-xs text-gray-500">Incluye todos los cobros pagados</span>
                        </div>
                    </div>
                    <span class="text-xl font-black text-emerald-600">+ RD$ {{ number_format($data['summary']['incomes'], 2) }}</span>
                </div>

                <!-- Gastos -->
                <div class="flex justify-between items-center py-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-2 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 uppercase">Egresos / Cuentas por Pagar</span>
                            <span class="text-xs text-gray-500">Gastos confirmados como pagados</span>
                        </div>
                    </div>
                    <span class="text-xl font-bold text-red-500">- RD$ {{ number_format($data['summary']['expenses'], 2) }}</span>
                </div>

                <!-- Balance / Ganancia Neta -->
                <div class="flex justify-between items-center py-6 mt-4 {{ $data['summary']['balance'] >= 0 ? 'bg-emerald-50' : 'bg-red-50' }} rounded-xl px-6 border {{ $data['summary']['balance'] >= 0 ? 'border-emerald-200' : 'border-red-200' }}">
                    <div>
                        <span class="block text-base font-black text-gray-900 uppercase">Balance Neto de Caja</span>
                        <span class="text-xs text-gray-600">Diferencia entre ingresos brutos y egresos pagados</span>
                    </div>
                    <span class="text-3xl font-black {{ $data['summary']['balance'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                        RD$ {{ number_format($data['summary']['balance'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
