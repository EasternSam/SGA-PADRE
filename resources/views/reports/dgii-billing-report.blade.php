<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-purple-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Facturación Electrónica DGII (Formato 607)</h2>
        <p class="text-sm text-gray-500">Periodo Analizado: {{ \Carbon\Carbon::parse($data['date_from'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d M, Y') }}</p>
    </div>

    @if($data['details']->isEmpty())
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No se generaron recibos fiscales (NCF) en el rango de fechas seleccionado.
        </div>
    @else
        <!-- Tarjetas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200 shadow-sm flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-purple-800 uppercase tracking-widest">Original Bruto</h4>
                    <p class="text-xl font-black text-purple-900 mt-1">RD$ {{ number_format($data['summary']['total_original'], 2) }}</p>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Descuentos</h4>
                    <p class="text-xl font-black text-gray-800 mt-1 text-red-500">- RD$ {{ number_format($data['summary']['total_discount'], 2) }}</p>
                </div>
            </div>

            <div class="bg-purple-600 rounded-xl p-4 border border-transparent shadow-md flex items-center justify-between text-white">
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-widest opacity-80">Monto Neto Facturado</h4>
                    <p class="text-2xl font-black mt-1">RD$ {{ number_format($data['summary']['total_paid'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Tabla Relación de Ventas 607 -->
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trx ID / Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobante Fiscal (NCF)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RNC / Cédula Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Cliente / Estudiante</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Original</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Facturado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['details'] as $payment)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900 font-mono">
                                {{ $payment->ncf }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ $payment->rnc_client ?? ($payment->student ? $payment->student->identification_number : 'N/A') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-700">
                                {{ $payment->company_name ?? ($payment->student ? $payment->student->full_name : 'No Identificado') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                RD$ {{ number_format($payment->original_amount ?? $payment->amount, 2) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-red-500 text-right">
                                {{ $payment->discount_amount > 0 ? '- RD$ ' . number_format($payment->discount_amount, 2) : '-' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-purple-700 text-right">
                                RD$ {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
