@extends('reports.layouts.pdf')
@section('title', 'Registro de Calificaciones')
@section('subtitle', ($section->gradeLevel?->name ?? '') . ' ' . $section->name . ' | ' . ($period->name ?? ''))

@section('styles')
    td, th { font-size: 7pt; padding: 3px !important; }
    .score-high { background: #dcfce7; color: #166534; font-weight: bold; }
    .score-mid { background: #fef3c7; color: #92400e; }
    .score-low { background: #fee2e2; color: #991b1b; font-weight: bold; }
    .avg-cell { font-weight: bold; font-size: 8pt; }
@endsection

@section('content')
    <table class="data-table" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="text-align: left; min-width: 120px;">Estudiante</th>
                @foreach($sectionSubjects as $ss)
                    <th style="text-align: center; font-size: 6pt;">{{ Str::limit($ss->subject?->name ?? '', 12) }}</th>
                @endforeach
                <th style="text-align: center; background: #1e3a8a; color: white;">PROM.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
                @php $data = $matrix[$student->id]; @endphp
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                    @foreach($sectionSubjects as $ss)
                        @php $score = $data['grades'][$ss->id] ?? null; @endphp
                        <td class="text-center {{ $score !== null ? ($score >= 90 ? 'score-high' : ($score >= 70 ? 'score-mid' : 'score-low')) : '' }}">
                            {{ $score ?? '—' }}
                        </td>
                    @endforeach
                    <td class="text-center avg-cell {{ $data['average'] >= 90 ? 'score-high' : ($data['average'] >= 70 ? 'score-mid' : 'score-low') }}">
                        {{ $data['average'] ?? '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="2" style="text-align: right; font-weight: bold;">PROMEDIO POR ASIGNATURA</td>
                @foreach($sectionSubjects as $ss)
                    @php $sa = $subjectAverages[$ss->id] ?? null; @endphp
                    <td class="text-center avg-cell">{{ $sa ? $sa['avg'] : '—' }}</td>
                @endforeach
                <td class="text-center avg-cell" style="background: #1e3a8a; color: white;">
                    @php
                        $allAvgs = collect($matrix)->pluck('average')->filter();
                        $generalAvg = $allAvgs->count() > 0 ? round($allAvgs->avg(), 1) : null;
                    @endphp
                    {{ $generalAvg ?? '—' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 12px; font-size: 7pt;">
        <strong>Total estudiantes:</strong> {{ $students->count() }} |
        <strong>Aprobados (≥70):</strong> {{ collect($matrix)->filter(fn($m) => ($m['average'] ?? 0) >= 70)->count() }} |
        <strong>Reprobados (<70):</strong> {{ collect($matrix)->filter(fn($m) => $m['average'] !== null && $m['average'] < 70)->count() }} |
        <strong>Promedio general:</strong> {{ $generalAvg ?? '—' }}
    </div>

    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Docente Titular
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Coordinador/a
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Director/a
                </div>
            </td>
        </tr>
    </table>
@endsection
