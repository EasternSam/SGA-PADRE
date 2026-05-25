@extends('reports.layouts.pdf')
@section('title', 'Reporte de Asistencia')
@section('subtitle', ($section->gradeLevel?->name ?? '') . ' ' . $section->name . ' | ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'))

@section('styles')
    .status-P { background: #dcfce7; color: #166534; font-weight: bold; }
    .status-A { background: #fee2e2; color: #991b1b; font-weight: bold; }
    .status-T { background: #fef3c7; color: #92400e; font-weight: bold; }
    .status-E { background: #dbeafe; color: #1e40af; }
    .status-empty { color: #d1d5db; }
    .summary-cell { font-size: 7pt; font-weight: bold; }
    .date-header { font-size: 6pt; writing-mode: vertical-rl; text-orientation: mixed; }
    td, th { font-size: 7pt; padding: 3px 2px !important; }
    .name-col { white-space: nowrap; text-align: left; min-width: 120px; }
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="name-col">#</th>
                <th class="name-col">Estudiante</th>
                @foreach($dates as $date)
                    <th class="text-center" style="width: 18px;">
                        <div>{{ \Carbon\Carbon::parse($date)->format('d') }}</div>
                        <div style="font-size: 5pt; font-weight: normal;">{{ strtoupper(substr(\Carbon\Carbon::parse($date)->translatedFormat('D'), 0, 2)) }}</div>
                    </th>
                @endforeach
                <th class="text-center">P</th>
                <th class="text-center">A</th>
                <th class="text-center">T</th>
                <th class="text-center">E</th>
                <th class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="name-col">{{ $student->last_name }}, {{ $student->first_name }}</td>
                    @foreach($dates as $date)
                        @php $status = $matrix[$student->id][$date] ?? null; @endphp
                        <td class="text-center {{ $status ? 'status-' . strtoupper(substr($status, 0, 1)) : 'status-empty' }}">
                            @switch($status)
                                @case('present') P @break
                                @case('absent') A @break
                                @case('late') T @break
                                @case('excused') E @break
                                @default · @break
                            @endswitch
                        </td>
                    @endforeach
                    @php $s = $summaries[$student->id]; @endphp
                    <td class="text-center summary-cell text-green">{{ $s['present'] }}</td>
                    <td class="text-center summary-cell text-red">{{ $s['absent'] }}</td>
                    <td class="text-center summary-cell" style="color: #92400e;">{{ $s['late'] }}</td>
                    <td class="text-center summary-cell" style="color: #1e40af;">{{ $s['excused'] }}</td>
                    <td class="text-center summary-cell">
                        {{ $s['total'] > 0 ? number_format(($s['present'] / $s['total']) * 100, 0) . '%' : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="2" class="text-right font-bold">TOTALES</td>
                @foreach($dates as $date)
                    @php
                        $dayPresent = collect($matrix)->filter(fn($row) => ($row[$date] ?? null) === 'present')->count();
                        $dayTotal = collect($matrix)->filter(fn($row) => ($row[$date] ?? null) !== null)->count();
                    @endphp
                    <td class="text-center" style="font-size: 6pt;">{{ $dayTotal > 0 ? $dayPresent : '' }}</td>
                @endforeach
                @php
                    $totalP = collect($summaries)->sum('present');
                    $totalA = collect($summaries)->sum('absent');
                    $totalT = collect($summaries)->sum('late');
                    $totalE = collect($summaries)->sum('excused');
                    $totalAll = collect($summaries)->sum('total');
                @endphp
                <td class="text-center summary-cell text-green">{{ $totalP }}</td>
                <td class="text-center summary-cell text-red">{{ $totalA }}</td>
                <td class="text-center summary-cell">{{ $totalT }}</td>
                <td class="text-center summary-cell">{{ $totalE }}</td>
                <td class="text-center summary-cell">{{ $totalAll > 0 ? number_format(($totalP / $totalAll) * 100, 0) . '%' : '—' }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 15px; font-size: 7pt;">
        <strong>Leyenda:</strong> P = Presente | A = Ausente | T = Tardanza | E = Excusa | Total estudiantes: {{ $students->count() }}
    </div>
@endsection
