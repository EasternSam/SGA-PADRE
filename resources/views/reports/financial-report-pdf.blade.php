@extends('reports.layouts.pdf')

@section('title', 'Reporte Financiero')
@section('subtitle')
Estado: {{ strtoupper($data['filter_status'] == 'all' ? 'Todos' : $data['filter_status']) }} <br>
Periodo: {{ \Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') }}
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Estudiante</th>
                <th style="width: 25%;">Curso / Módulo</th>
                <th style="width: 15%;">Sección</th>
                <th class="text-right" style="width: 10%;">Costo Total</th>
                <th class="text-right" style="width: 10%;">Pagado</th>
                <th class="text-right" style="width: 10%;">Pendiente</th>
                <th class="text-center" style="width: 5%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalCost = 0;
                $grandTotalPaid = 0;
                $grandTotalPending = 0;
            @endphp

            @forelse($data['financials'] as $record)
                @php
                    $totalCost = (float) $record->total_cost ?? (float) $record->expected_cost;
                    $paid = (float) $record->total_paid;
                    $pending = round($totalCost - $paid, 2);

                    $grandTotalCost += $totalCost;
                    $grandTotalPaid += $paid;
                    $grandTotalPending += $pending;
                @endphp
                <tr>
                    <td>
                        <div class="font-bold" style="text-transform: uppercase;">{{ $record->last_name }}, {{ $record->first_name }}</div>
                        <div style="font-size: 8pt; color: #666;">
                            Tel: {{ $record->mobile_phone ?? $record->student_phone ?? 'N/A' }}
                        </div>
                    </td>
                    <td>
                        {{ $record->course_name }}
                        <div style="font-size: 8pt; color: #666;">{{ $record->module_name }}</div>
                    </td>
                    <td>{{ $record->section_name ?? 'N/A' }}</td>
                    
                    <td class="text-right font-mono">
                        {{ number_format($totalCost, 2) }}
                    </td>
                    <td class="text-right font-mono text-green">
                        {{ number_format($paid, 2) }}
                    </td>
                    <td class="text-right font-mono font-bold {{ $pending > 0 ? 'text-red' : 'text-gray' }}">
                        {{ number_format($pending, 2) }}
                    </td>
                    
                    <td class="text-center">
                        @if($pending > 0)
                            <span class="text-red font-bold" style="font-size: 8pt;">DEUDA</span>
                        @elseif($totalCost == 0)
                            <span class="text-gray" style="font-size: 8pt;">N/A</span>
                        @else
                            <span class="text-green font-bold" style="font-size: 8pt;">PAGO</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No se encontraron registros financieros en este periodo.</td>
                </tr>
            @endforelse
        </tbody>
        
        {{-- Pie de tabla con totales --}}
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-right">TOTALES GENERALES</td>
                <td class="text-right font-mono">{{ number_format($grandTotalCost, 2) }}</td>
                <td class="text-right font-mono text-green">{{ number_format($grandTotalPaid, 2) }}</td>
                <td class="text-right font-mono text-red">{{ number_format($grandTotalPending, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection