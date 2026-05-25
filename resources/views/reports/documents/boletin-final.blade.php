@extends('reports.layouts.pdf')
@section('title', 'Boletín Final Anual')
@section('subtitle', ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' | ' . ($activeYear?->name ?? ''))

@section('styles')
    td, th { font-size: 7.5pt; padding: 3px 5px !important; }
    .period-header { background: #1e3a8a; color: white; text-align: center; font-size: 9pt; padding: 5px !important; }
    .subject-col { min-width: 100px; }
    .score-pass { color: #166534; }
    .score-fail { color: #991b1b; font-weight: bold; }
    .final-avg { background: #eff6ff; font-weight: bold; }
@endsection

@section('content')
    {{-- Student Info --}}
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td style="width: 50%;"><strong>Estudiante:</strong> {{ $student->full_name }}</td>
            <td style="width: 25%;"><strong>Matrícula:</strong> {{ $student->student_id ?? '' }}</td>
            <td style="width: 25%;"><strong>Grado:</strong> {{ $student->gradeLevel?->name ?? '' }} {{ $section?->name ?? '' }}</td>
        </tr>
    </table>

    {{-- Consolidated Grade Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="subject-col">Asignatura</th>
                @foreach($periods as $p)
                    <th class="text-center" style="font-size: 7pt;">{{ Str::limit($p->name, 10) }}</th>
                @endforeach
                <th class="text-center" style="background: #1e3a8a; color: white;">FINAL</th>
                <th class="text-center" style="font-size: 7pt;">Nivel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjectData as $sd)
                <tr>
                    <td class="font-bold">{{ $sd['subject_name'] }}</td>
                    @foreach($periods as $p)
                        @php $score = $sd['scores'][$p->id] ?? null; @endphp
                        <td class="text-center {{ $score !== null ? ($score >= 70 ? 'score-pass' : 'score-fail') : '' }}">
                            {{ $score ?? '—' }}
                        </td>
                    @endforeach
                    <td class="text-center final-avg {{ ($sd['final'] ?? 0) >= 70 ? 'score-pass' : 'score-fail' }}">
                        {{ $sd['final'] ?? '—' }}
                    </td>
                    <td class="text-center" style="font-size: 7pt;">
                        @if($sd['final'])
                            {{ $sd['final'] >= 90 ? 'Excelente' : ($sd['final'] >= 80 ? 'Muy Bueno' : ($sd['final'] >= 70 ? 'Bueno' : 'Insuficiente')) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td class="text-right font-bold">PROMEDIO GENERAL</td>
                @foreach($periods as $p)
                    <td class="text-center font-bold">{{ $periodAvgs[$p->id] ?? '—' }}</td>
                @endforeach
                <td class="text-center font-bold" style="background: {{ ($generalAvg ?? 0) >= 70 ? '#dcfce7' : '#fee2e2' }}; font-size: 10pt;">
                    {{ $generalAvg ?? '—' }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Attendance Summary --}}
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%;">
                <table class="data-table" style="width: 100%;">
                    <tr><th colspan="2" style="background: #059669; color: white;">Asistencia Anual</th></tr>
                    <tr><td>Presencias</td><td class="text-center font-bold text-green">{{ $attendance['present'] ?? 0 }}</td></tr>
                    <tr><td>Ausencias</td><td class="text-center font-bold text-red">{{ $attendance['absent'] ?? 0 }}</td></tr>
                    <tr><td>Tardanzas</td><td class="text-center font-bold">{{ $attendance['late'] ?? 0 }}</td></tr>
                    <tr><td>% Asistencia</td><td class="text-center font-bold">{{ $attendance['pct'] ?? '—' }}%</td></tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <table class="data-table" style="width: 100%;">
                    <tr><th colspan="2" style="background: #7c3aed; color: white;">Resultado Final</th></tr>
                    <tr>
                        <td>Decisión</td>
                        <td class="text-center font-bold" style="font-size: 10pt; {{ ($promotion?->result === 'promoted' || $promotion?->result === 'graduated') ? 'color: #166534;' : 'color: #991b1b;' }}">
                            {{ $promotion ? (\App\Models\PromotionRecord::RESULTS[$promotion->result] ?? $promotion->result) : 'Pendiente' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Promedio Final</td>
                        <td class="text-center font-bold" style="font-size: 10pt;">{{ $generalAvg ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Signatures --}}
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="text-align: center; width: 25%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 130px; padding-top: 5px; font-size: 7pt;">Docente Titular</div>
            </td>
            <td style="text-align: center; width: 25%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 130px; padding-top: 5px; font-size: 7pt;">Padre/Madre/Tutor</div>
            </td>
            <td style="text-align: center; width: 25%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 130px; padding-top: 5px; font-size: 7pt;">Director/a</div>
            </td>
            <td style="text-align: center; width: 25%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 130px; padding-top: 5px; font-size: 7pt;">Sello</div>
            </td>
        </tr>
    </table>
@endsection
