@extends('reports.layouts.pdf')
@section('title', 'RE-2: Calificaciones por Período')
@section('subtitle', ($period->name ?? '') . ' | ' . ($activeYear?->name ?? ''))

@section('styles')
    td, th { font-size: 6pt; padding: 2px !important; }
    .section-header { background: #1e3a8a; color: white; font-size: 9pt; padding: 6px !important; }
    .score-pass { color: #166534; }
    .score-fail { color: #991b1b; font-weight: bold; }
    .stats-row td { background: #f1f5f9; font-weight: bold; }
@endsection

@section('content')
    <table style="width: 100%; margin-bottom: 8px; font-size: 8pt;">
        <tr>
            <td><strong>Centro:</strong> {{ $schoolConfig?->school_name ?? '' }}</td>
            <td><strong>Código:</strong> {{ $schoolConfig?->minerd_code ?? '' }}</td>
            <td><strong>Período:</strong> {{ $period->name ?? '' }}</td>
        </tr>
    </table>

    @foreach($sectionData as $sd)
        <table class="data-table" style="margin-bottom: 10px; page-break-inside: avoid;">
            <tr>
                <td colspan="{{ 3 + $sd['subjects']->count() }}" class="section-header">
                    {{ $sd['section']->gradeLevel?->name ?? '' }} {{ $sd['section']->name }}
                    — Aprob: {{ $sd['approved'] }} | Reprob: {{ $sd['failed'] }}
                </td>
            </tr>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="min-width: 90px;">Estudiante</th>
                @foreach($sd['subjects'] as $ss)
                    <th class="text-center" style="font-size: 5pt;">{{ Str::limit($ss->subject?->name ?? '', 10) }}</th>
                @endforeach
                <th class="text-center" style="background: #1e3a8a; color: white;">PROM</th>
            </tr>
            @foreach($sd['matrix'] as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row['student']->last_name }}, {{ $row['student']->first_name }}</td>
                    @foreach($sd['subjects'] as $ss)
                        @php $sc = $row['scores'][$ss->id] ?? null; @endphp
                        <td class="text-center {{ $sc !== null ? ($sc >= 70 ? 'score-pass' : 'score-fail') : '' }}">{{ $sc ?? '—' }}</td>
                    @endforeach
                    <td class="text-center font-bold {{ ($row['avg'] ?? 0) >= 70 ? 'score-pass' : 'score-fail' }}">{{ $row['avg'] ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt;">Coordinador/a</div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt;">Director/a</div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt;">Sello</div>
            </td>
        </tr>
    </table>
@endsection
