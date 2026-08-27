@extends('reports.layouts.pdf')
@section('title', 'RE-1: Registro de Estudiantes')
@section('subtitle', 'Formulario MINERD | ' . ($activeYear?->name ?? ''))

@section('styles')
    td, th { font-size: 7pt; padding: 3px !important; }
    .section-header { background: #1e3a8a; color: white; font-size: 9pt; padding: 6px !important; }
    .gender-m { color: #2563eb; }
    .gender-f { color: #db2777; }
@endsection

@section('content')
    <table style="width: 100%; margin-bottom: 10px; font-size: 8pt;">
        <tr>
            <td><strong>Centro Educativo:</strong> {{ $schoolConfig?->school_name ?? '' }}</td>
            <td><strong>Código MINERD:</strong> {{ $schoolConfig?->minerd_code ?? '' }}</td>
            <td><strong>Regional:</strong> {{ $schoolConfig?->regional ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Distrito:</strong> {{ $schoolConfig?->district ?? '' }}</td>
            <td><strong>Año Escolar:</strong> {{ $activeYear?->name ?? '' }}</td>
            <td><strong>Matrícula Total:</strong> {{ $totalStudents }} (M:{{ $totalMales }} / F:{{ $totalFemales }})</td>
        </tr>
    </table>

    @foreach($data as $sectionBlock)
        <table class="data-table" style="margin-bottom: 10px; page-break-inside: avoid;">
            <tr>
                <td colspan="8" class="section-header">
                    {{ $sectionBlock['section']->gradeLevel?->name ?? '' }} {{ $sectionBlock['section']->name }}
                    — Total: {{ $sectionBlock['total'] }} (M:{{ $sectionBlock['males'] }} / F:{{ $sectionBlock['females'] }})
                </td>
            </tr>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 25%;">Apellido, Nombre</th>
                <th style="width: 10%;">Matrícula</th>
                <th style="width: 10%;">Fec. Nac.</th>
                <th style="width: 5%;">Sexo</th>
                <th style="width: 8%;">Edad</th>
                <th style="width: 15%;">Nacionalidad</th>
                <th style="width: 12%;">Documento</th>
            </tr>
            @foreach($sectionBlock['students'] as $i => $student)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="font-bold">{{ $student->last_name }}, {{ $student->first_name }}</td>
                    <td class="text-center">{{ $student->student_id ?? '' }}</td>
                    <td class="text-center">{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-center {{ in_array(strtolower($student->gender ?? ''), ['m','masculino']) ? 'gender-m' : 'gender-f' }}">
                        {{ strtoupper(substr($student->gender ?? '', 0, 1)) }}
                    </td>
                    <td class="text-center">{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->age : '—' }}</td>
                    <td>{{ $student->nationality ?? 'Dominicana' }}</td>
                    <td>{{ $student->identity_number ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px; font-size: 8pt;">Director/a</div>
            </td>
            <td style="text-align: center; width: 50%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 200px; padding-top: 8px; font-size: 8pt;">Sello</div>
            </td>
        </tr>
    </table>
@endsection
