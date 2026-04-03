<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-indigo-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Subsidios y Becas Otorgadas</h2>
        <p class="text-sm text-gray-500">Periodo Analizado: {{ \Carbon\Carbon::parse($data['date_from'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d M, Y') }}</p>
    </div>

    @if($data['details']->isEmpty())
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No se otorgaron descuentos ni becas en el rango de fechas seleccionado.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-600 rounded-xl p-6 shadow-md border border-transparent flex flex-col justify-center text-white">
                <h4 class="text-xs font-bold uppercase tracking-widest opacity-80 mb-2">Total Subsidio Acumulado (Gasto Institucional)</h4>
                <p class="text-3xl font-black">RD$ {{ number_format($data['summary']['total_discount'], 2) }}</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-6 shadow-sm border border-gray-200 flex flex-col justify-center text-gray-800">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Ingreso Real con Beca</h4>
                <p class="text-2xl font-black">RD$ {{ number_format($data['summary']['total_paid'], 2) }}</p>
            </div>
            
            <div class="bg-indigo-50 rounded-xl p-6 shadow-sm border border-indigo-200 flex flex-col justify-center text-indigo-900 border-l-4 border-l-indigo-500">
                <h4 class="text-xs font-bold uppercase tracking-widest opacity-80 mb-2">Frecuencia de Uso</h4>
                <p class="text-2xl font-black">{{ $data['summary']['count'] }} Transacciones con Beca</p>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo / Concepto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Original</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-indigo-600 uppercase tracking-wider">Descuento Beca</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pagado Neto</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['details'] as $payment)
                        <tr class="hover:bg-indigo-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $payment->student ? $payment->student->full_name : 'No Identificado' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700">
                                <span class="font-mono text-gray-400 block mb-1">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                                {{ $payment->paymentConcept ? $payment->paymentConcept->name : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                RD$ {{ number_format($payment->original_amount ?? $payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 text-right bg-indigo-50/30">
                                - RD$ {{ number_format($payment->discount_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                RD$ {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
