<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-emerald-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Arqueo de Caja Diario</h2>
        <p class="text-sm text-gray-500">Periodo Analizado: {{ \Carbon\Carbon::parse($data['date_from'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d M, Y') }}</p>
    </div>

    @if(empty($data['summary']))
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No se registraron cobros en el rango de fechas seleccionado.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($data['summary'] as $user => $stats)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h4 class="font-bold text-gray-800 uppercase flex items-center">
                            <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $user }}
                        </h4>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-2 mb-4">
                            @foreach($stats['methods'] as $method => $amount)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 truncate">{{ $method }}</span>
                                    <span class="font-bold text-gray-800">RD$ {{ number_format($amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center bg-emerald-50 -mx-4 -mb-4 px-4 py-3">
                            <span class="text-xs font-bold text-emerald-800 uppercase">Subtotal Recaudado</span>
                            <span class="text-lg font-black text-emerald-700">RD$ {{ number_format($stats['total'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-green-600 text-white rounded-xl p-6 shadow-md flex justify-between items-center mb-8">
            <div>
                <h3 class="text-sm font-bold opacity-80 uppercase tracking-widest">Total Global Recaudado</h3>
                <p class="text-xs opacity-75 mt-1">Este monto debe coincidir con la sumatoria bancaria en este rango.</p>
            </div>
            <div class="text-3xl font-black">
                RD$ {{ number_format($data['grand_total'], 2) }}
            </div>
        </div>

        <!-- Tabla Detallada -->
        <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase mt-8 border-b pb-2">Registro de Transacciones</h3>
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cajero</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['details'] as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y h:i a') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#{{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->student ? $payment->student->full_name : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold bg-gray-50 text-center rounded border">{{ $payment->gateway }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $payment->transaction_id ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">RD$ {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 text-right">{{ $payment->user ? $payment->user->name : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-12 pt-8 border-t border-gray-200 grid grid-cols-2 gap-8 text-center text-xs text-gray-500">
            <div>
                <p>_________________________________________</p>
                <p class="mt-2 font-bold uppercase">Firma del Cajero Responsable</p>
            </div>
            <div>
                <p>_________________________________________</p>
                <p class="mt-2 font-bold uppercase">Aprobación / Contabilidad</p>
            </div>
        </div>
    @endif
</div>
