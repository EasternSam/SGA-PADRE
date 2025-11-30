<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt; /* Letra un poco más pequeña para ajustar más columnas */
            color: #333;
            background: white;
        }
        @page {
            margin: 1cm; /* Márgenes más estrechos para landscape */
        }
        /* Encabezado */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .header-logo-cell {
            width: 180px;
            vertical-align: middle;
            text-align: left;
        }
        .header-text-cell {
            text-align: right;
            vertical-align: middle;
        }
        .logo {
            width: 150px;
            height: auto;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10pt;
            margin: 5px 0 0 0;
            color: #374151;
        }

        /* Tabla de Datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, 
        .data-table td {
            border: 1px solid #cbd5e0;
            padding: 6px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f7fafc;
            color: #2d3748;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            text-align: left;
        }
        
        /* Alineaciones */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }
        
        /* Colores de Estado */
        .text-green { color: #166534; }
        .text-red { color: #991b1b; }
        .text-gray { color: #718096; }
        
        /* Totales */
        .totals-row td {
            background-color: #edf2f7;
            font-weight: bold;
            border-top: 2px solid #4a5568;
        }

        .meta-info {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            text-align: right;
        }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <img src="{{ public_path('centuu.png') }}" class="logo" alt="Logo">
            </td>
            <td class="header-text-cell">
                <div class="header">
                    <h1>Reporte Financiero</h1>
                    <p>
                        Estado: {{ strtoupper($data['filter_status'] == 'all' ? 'Todos' : $data['filter_status']) }} <br>
                        Periodo: {{ \Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

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
                    $totalCost = (float) $record->total_cost;
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
                    <td>{{ $record->section_name }}</td>
                    
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

    <div class="meta-info">
        Generado el: {{ now()->format('d/m/Y h:i A') }} | Documento Oficial
    </div>
</body>
</html>