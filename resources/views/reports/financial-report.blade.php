<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Reporte Financiero de Estado de Cuenta</h1>
        <p class="text-sm text-gray-600">
            Filtro: {{ strtoupper($data['filter_status'] == 'all' ? 'Todos' : $data['filter_status']) }} | 
            Periodo: {{ \Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') }}
        </p>
    </div>

    @if(isset($data['financials']) && count($data['financials']) > 0)
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 p-2 text-left">Estudiante</th>
                        <th class="border border-gray-300 p-2 text-left">Curso / Módulo</th>
                        <th class="border border-gray-300 p-2 text-left">Sección</th>
                        <th class="border border-gray-300 p-2 text-right">Costo Total</th>
                        <th class="border border-gray-300 p-2 text-right">Pagado</th>
                        <th class="border border-gray-300 p-2 text-right">Pendiente</th>
                        <th class="border border-gray-300 p-2 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalCost = 0;
                        $grandTotalPaid = 0;
                        $grandTotalPending = 0;
                    @endphp

                    @foreach($data['financials'] as $record)
                        @php
                            $totalCost = (float) $record->total_cost; 
                            $paid = (float) $record->total_paid;
                            $pending = round($totalCost - $paid, 2);

                            $grandTotalCost += $totalCost;
                            $grandTotalPaid += $paid;
                            $grandTotalPending += $pending;

                            $statusLabel = 'Al día';
                            $statusClass = 'bg-green-100 text-green-800';

                            if ($pending > 0.00) {
                                $statusLabel = 'Pendiente';
                                $statusClass = 'bg-red-100 text-red-800';
                            } elseif ($totalCost == 0) {
                                $statusLabel = 'Sin Cargos';
                                $statusClass = 'bg-gray-100 text-gray-600';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2 font-medium uppercase">
                                {{ $record->last_name }}, {{ $record->first_name }}
                                <div class="text-[10px] text-gray-500">
                                    {{-- CORRECCIÓN: Si hay teléfono, muéstralo. Si no, muestra vacío o un guion --}}
                                    {{ $record->mobile_phone ?? $record->student_phone ?? '-' }}
                                </div>
                            </td>
                            <td class="border border-gray-300 p-2">
                                {{ $record->course_name }}
                                <div class="text-[10px] text-gray-500">{{ $record->module_name }}</div>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">{{ $record->section_name }}</td>
                            
                            <td class="border border-gray-300 p-2 text-right font-mono">
                                {{ number_format($totalCost, 2) }}
                            </td>
                            <td class="border border-gray-300 p-2 text-right font-mono text-green-700">
                                {{ number_format($paid, 2) }}
                            </td>
                            <td class="border border-gray-300 p-2 text-right font-mono font-bold {{ $pending > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ number_format($pending, 2) }}
                            </td>
                            
                            <td class="border border-gray-300 p-2 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $statusClass }}">
                                    {{ strtoupper($statusLabel) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-200 font-bold border-t-2 border-gray-400">
                    <tr>
                        <td colspan="3" class="border border-gray-300 p-2 text-right">TOTAL GENERAL</td>
                        <td class="border border-gray-300 p-2 text-right">{{ number_format($grandTotalCost, 2) }}</td>
                        <td class="border border-gray-300 p-2 text-right text-green-800">{{ number_format($grandTotalPaid, 2) }}</td>
                        <td class="border border-gray-300 p-2 text-right text-red-800">{{ number_format($grandTotalPending, 2) }}</td>
                        <td class="border border-gray-300 p-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="p-8 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay datos financieros</h3>
            <p class="mt-1 text-sm text-gray-500">No se encontraron inscripciones o pagos con los filtros seleccionados.</p>
        </div>
    @endif

    <div class="mt-8 text-xs text-gray-500">
        <p><strong>Nota:</strong> El "Costo Total" se calcula sumando todos los conceptos de pago generados (pagados y pendientes) para cada matrícula. Si un estudiante no tiene conceptos generados, aparecerá en cero.</p>
    </div>
</div>