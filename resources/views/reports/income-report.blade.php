<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-blue-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Ingresos por Concepto</h2>
        <p class="text-sm text-gray-500">Periodo Analizado: {{ \Carbon\Carbon::parse($data['date_from'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d M, Y') }}</p>
    </div>

    @if(empty($data['summary']))
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No se generaron ingresos en el rango de fechas seleccionado.
        </div>
    @else
        <div class="bg-blue-50 text-blue-800 rounded-xl p-6 shadow-sm border border-blue-200 flex justify-between items-center mb-8">
            <div>
                <h3 class="text-sm font-bold opacity-80 uppercase tracking-widest">Recaudación Bruta por Conceptos</h3>
                <p class="text-xs opacity-75 mt-1">Este balance representa el dinero efectivamente pagado e ingresado al sistema.</p>
            </div>
            <div class="text-3xl font-black">
                RD$ {{ number_format($data['grand_total'], 2) }}
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <!-- Tabla Principal -->
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto Financiero</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Frecuencia (Cant.)</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-800 uppercase tracking-wider">Beneficio Neto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data['summary'] as $concept => $stats)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 border-l-4 border-blue-500">
                                        {{ $concept }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        <span class="bg-gray-100 px-2 py-1 rounded">{{ $stats['count'] }} recibos</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-blue-700 text-right">
                                        RD$ {{ number_format($stats['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 h-full">
                    <h4 class="font-bold text-gray-800 uppercase mb-4 flex items-center text-sm border-b pb-2">
                        <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Resumen Visual
                    </h4>
                    
                    <div class="space-y-4">
                        @foreach($data['summary'] as $concept => $stats)
                            @php
                                $percent = $data['grand_total'] > 0 ? ($stats['total'] / $data['grand_total']) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 truncate w-2/3">{{ $concept }}</span>
                                    <span class="text-gray-500">{{ round($percent, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
        </div>
    @endif
</div>
