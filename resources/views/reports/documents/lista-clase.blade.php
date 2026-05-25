@extends('reports.layouts.pdf')
@section('title', 'Lista de Clase')
@section('subtitle', ($section->gradeLevel?->name ?? '') . ' ' . $section->name . ' | ' . ($activeYear?->name ?? ''))

@section('content')
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td><strong>Total Estudiantes:</strong> {{ $students->count() }}</td>
            <td><strong>Tanda:</strong> {{ $schoolConfig?->shift ? ucfirst(str_replace('_', ' ', $schoolConfig->shift)) : 'Regular' }}</td>
            <td style="text-align: right;"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 30%;">Apellido, Nombre</th>
                <th style="width: 12%; text-align: center;">Matrícula</th>
                <th style="width: 10%; text-align: center;">Sexo</th>
                <th style="width: 13%; text-align: center;">Fec. Nacimiento</th>
                <th style="width: 10%; text-align: center;">Edad</th>
                <th style="width: 10%; text-align: center;">% Asist.</th>
                <th style="width: 10%;">Teléfono</th>
            </tr>
        </thead>
        <tbody>
            @php $males = 0; $females = 0; @endphp
            @foreach($students as $i => $s)
                @php
                    $age = $s->birth_date ? \Carbon\Carbon::parse($s->birth_date)->age : null;
                    $gender = strtolower($s->gender ?? '');
                    if (str_contains($gender, 'masc') || $gender === 'm') $males++;
                    elseif (str_contains($gender, 'fem') || $gender === 'f') $females++;
                    $att = $attendanceSummary[$s->id] ?? null;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="font-bold">{{ $s->last_name }}, {{ $s->first_name }}</td>
                    <td class="text-center" style="font-size: 8pt;">{{ $s->student_id ?? '' }}</td>
                    <td class="text-center">{{ strtoupper(substr($s->gender ?? '', 0, 1)) }}</td>
                    <td class="text-center" style="font-size: 8pt;">{{ $s->birth_date ? \Carbon\Carbon::parse($s->birth_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-center">{{ $age ? $age . ' años' : '—' }}</td>
                    <td class="text-center {{ ($att['pct'] ?? 0) < 80 ? 'text-red' : 'text-green' }}" style="font-weight: bold;">
                        {{ $att['pct'] !== null ? $att['pct'] . '%' : '—' }}
                    </td>
                    <td style="font-size: 8pt;">{{ $s->phone ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-right font-bold">RESUMEN:</td>
                <td class="text-center" style="font-size: 8pt;">M: {{ $males }} | F: {{ $females }}</td>
                <td colspan="2" class="text-center">Total: {{ $students->count() }}</td>
                <td class="text-center font-bold">
                    @php
                        $avgAtt = collect($attendanceSummary)->pluck('pct')->filter()->avg();
                    @endphp
                    {{ $avgAtt ? round($avgAtt) . '%' : '—' }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Docente Titular</p>
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
