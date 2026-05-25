@extends('reports.layouts.pdf')
@section('title', 'Historial de Pagos')
@section('subtitle', ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))

@section('content')
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td><strong>Estudiante:</strong> {{ $student->full_name }}</td>
            <td><strong>Matrícula:</strong> {{ $student->student_id ?? $student->id }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 25%;">Concepto</th>
                <th style="width: 12%; text-align: right;">Monto</th>
                <th style="width: 12%; text-align: right;">Pagado</th>
                <th style="width: 12%; text-align: right;">Balance</th>
                <th style="width: 8%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $i => $p)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                    <td>{{ \App\Models\StudentPayment::TYPES[$p->type] ?? $p->type }}</td>
                    <td>{{ $p->concept }}</td>
                    <td class="text-right font-mono">{{ number_format($p->amount, 2) }}</td>
                    <td class="text-right font-mono text-green">{{ number_format($p->paid, 2) }}</td>
                    <td class="text-right font-mono {{ ($p->amount - $p->paid) > 0 ? 'text-red' : 'text-green' }}">
                        {{ number_format($p->amount - $p->paid, 2) }}
                    </td>
                    <td class="text-center" style="font-size: 7pt;">
                        {{ \App\Models\StudentPayment::STATUSES[$p->status] ?? $p->status }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-right font-bold">TOTALES</td>
                <td class="text-right font-bold font-mono">{{ number_format($totalDue, 2) }}</td>
                <td class="text-right font-bold font-mono text-green">{{ number_format($totalPaid, 2) }}</td>
                <td class="text-right font-bold font-mono {{ ($totalDue - $totalPaid) > 0 ? 'text-red' : 'text-green' }}">
                    {{ number_format($totalDue - $totalPaid, 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px; font-size: 8pt;">Administración</div>
            </td>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px; font-size: 8pt;">Director/a</div>
            </td>
        </tr>
    </table>
@endsection
