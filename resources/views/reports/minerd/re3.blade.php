@extends('reports.layouts.pdf')
@section('title', 'RE-3: Reporte de Asistencia')
@section('subtitle', $monthName . ' | ' . ($activeYear?->name ?? ''))

@section('styles')
    td, th { font-size: 7pt; padding: 3px !important; }
    .section-header { background: #059669; color: white; font-size: 9pt; padding: 6px !important; }
    .pct-good { color: #166534; font-weight: bold; }
    .pct-warn { color: #92400e; }
    .pct-bad { color: #991b1b; font-weight: bold; }
@endsection

@section('content')
    <table style="width: 100%; margin-bottom: 8px; font-size: 8pt;">
        <tr>
            <td><strong>Centro:</strong> {{ $schoolConfig?->school_name ?? '' }}</td>
            <td><strong>Código:</strong> {{ $schoolConfig?->minerd_code ?? '' }}</td>
            <td><strong>Mes:</strong> {{ $monthName }}</td>
        </tr>
    </table>

    @foreach($sectionData as $sd)
        <table class="data-table" style="margin-bottom: 10px; page-break-inside: avoid;">
            <tr>
                <td colspan="8" class="section-header">
                    {{ $sd['section']->gradeLevel?->name ?? '' }} {{ $sd['section']->name }}
                    — P:{{ $sd['stats']['present'] }} A:{{ $sd['stats']['absent'] }} T:{{ $sd['stats']['late'] }} E:{{ $sd['stats']['excused'] }}
                </td>
            </tr>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 30%;">Estudiante</th>
                <th class="text-center" style="width: 10%;">Presencias</th>
                <th class="text-center" style="width: 10%;">Ausencias</th>
                <th class="text-center" style="width: 10%;">Tardanzas</th>
                <th class="text-center" style="width: 10%;">Excusas</th>
                <th class="text-center" style="width: 8%;">Total</th>
                <th class="text-center" style="width: 10%;">% Asist.</th>
            </tr>
            @foreach($sd['students'] as $i => $sr)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $sr['student']->last_name }}, {{ $sr['student']->first_name }}</td>
                    <td class="text-center">{{ $sr['stats']['present'] }}</td>
                    <td class="text-center {{ $sr['stats']['absent'] > 3 ? 'pct-bad' : '' }}">{{ $sr['stats']['absent'] }}</td>
                    <td class="text-center">{{ $sr['stats']['late'] }}</td>
                    <td class="text-center">{{ $sr['stats']['excused'] }}</td>
                    <td class="text-center">{{ $sr['stats']['total'] }}</td>
                    <td class="text-center {{ ($sr['pct'] ?? 0) >= 90 ? 'pct-good' : (($sr['pct'] ?? 0) >= 80 ? 'pct-warn' : 'pct-bad') }}">
                        {{ $sr['pct'] !== null ? $sr['pct'] . '%' : '—' }}
                    </td>
                </tr>
            @endforeach
            @if(count($sd['students']) > 0)
                <tr class="totals-row">
                    <td colspan="2" class="text-right font-bold">TOTAL SECCIÓN</td>
                    <td class="text-center font-bold">{{ $sd['stats']['present'] }}</td>
                    <td class="text-center font-bold">{{ $sd['stats']['absent'] }}</td>
                    <td class="text-center font-bold">{{ $sd['stats']['late'] }}</td>
                    <td class="text-center font-bold">{{ $sd['stats']['excused'] }}</td>
                    <td class="text-center font-bold">{{ $sd['stats']['total'] }}</td>
                    <td class="text-center font-bold">
                        {{ $sd['stats']['total'] > 0 ? round(($sd['stats']['present'] / $sd['stats']['total']) * 100, 1) . '%' : '—' }}
                    </td>
                </tr>
            @endif
        </table>
    @endforeach

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 5px; font-size: 8pt;">Director/a</div>
            </td>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 5px; font-size: 8pt;">Sello</div>
            </td>
        </tr>
    </table>
@endsection
