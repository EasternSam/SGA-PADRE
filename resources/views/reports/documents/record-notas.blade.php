@extends('reports.layouts.pdf')
@section('title', 'Récord de Notas')
@section('subtitle', ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' | Historial Académico')

@section('styles')
    td, th { font-size: 8pt; padding: 4px !important; }
    .year-header { background: #1e3a8a; color: white; font-size: 10pt; padding: 8px !important; }
    .period-header { background: #e2e8f0; font-weight: bold; }
    .score-high { color: #166534; }
    .score-low { color: #991b1b; font-weight: bold; }
@endsection

@section('content')
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%;"><strong>Estudiante:</strong> {{ $student->full_name }}</td>
            <td style="width: 50%;"><strong>Matrícula:</strong> {{ $student->student_code ?? $student->id }}</td>
        </tr>
        <tr>
            <td><strong>Grado Actual:</strong> {{ $student->gradeLevel?->name ?? '—' }} {{ $section?->name ?? '' }}</td>
            <td><strong>Fecha de Nacimiento:</strong> {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }}</td>
        </tr>
    </table>

    @forelse($academicHistory as $history)
        <table class="data-table" style="margin-bottom: 15px;">
            <tr>
                <td colspan="20" class="year-header">
                    {{ $history['year']->name }}
                    @if($history['promotion'])
                        — {{ \App\Models\PromotionRecord::RESULTS[$history['promotion']->result] ?? '' }}
                        @if($history['promotion']->final_average)
                            (Prom: {{ $history['promotion']->final_average }})
                        @endif
                    @endif
                </td>
            </tr>
            @foreach($history['periods'] as $pg)
                <tr class="period-header">
                    <td colspan="20" style="font-size: 9pt;">{{ $pg['period']->name }} — Promedio: <strong>{{ $pg['avg'] ?? '—' }}</strong></td>
                </tr>
                <tr>
                    <th style="width: 50%;">Asignatura</th>
                    <th style="width: 25%; text-align: center;">Calificación</th>
                    <th style="width: 25%; text-align: center;">Nivel</th>
                </tr>
                @foreach($pg['grades'] as $g)
                    @php
                        $score = $g->score;
                        $level = match(true) {
                            $score >= 90 => 'Excelente',
                            $score >= 80 => 'Muy Bueno',
                            $score >= 70 => 'Bueno',
                            $score >= 60 => 'Aceptable',
                            default => 'Debajo del Esperado',
                        };
                    @endphp
                    <tr>
                        <td>{{ $g->sectionSubject?->subject?->name ?? '—' }}</td>
                        <td class="text-center {{ $score >= 70 ? 'score-high' : 'score-low' }}" style="font-weight: bold;">{{ $score ?? '—' }}</td>
                        <td class="text-center" style="font-size: 7pt;">{{ $level }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    @empty
        <p style="text-align: center; color: #9ca3af; padding: 30px;">Sin historial académico registrado</p>
    @endforelse

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Registro Académico</p>
                </div>
            </td>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Director/a</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
