@extends('reports.layouts.pdf')
@section('title', 'Carta de Buena Conducta')
@section('subtitle', 'Documento Oficial')

@section('content')
    <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
        <h2 style="font-size: 16pt; color: #1e3a8a; text-transform: uppercase; letter-spacing: 2px;">
            CARTA DE BUENA CONDUCTA
        </h2>
        <div style="width: 60px; height: 3px; background: #1e3a8a; margin: 10px auto;"></div>
    </div>

    <div style="line-height: 2; font-size: 11pt; text-align: justify; padding: 0 30px;">
        <p>
            Quien suscribe, <strong>Director/a</strong> del centro educativo
            <strong>{{ $schoolConfig?->school_name ?? \App\Models\Setting::get('institution_name', 'Centro Educativo') }}</strong>,
            @if($schoolConfig?->minerd_code)
                código MINERD <strong>{{ $schoolConfig->minerd_code }}</strong>,
            @endif
            certifica que:
        </p>

        <div style="text-align: center; margin: 25px 0; padding: 15px; border: 1px solid #cbd5e0; border-radius: 8px; background: #f8fafc;">
            <p style="font-size: 14pt; font-weight: bold; color: #1e3a8a; margin: 0;">
                {{ $student->first_name }} {{ $student->last_name }}
            </p>
            @if($student->student_id)
                <p style="font-size: 10pt; color: #6b7280; margin: 5px 0 0;">Matrícula: {{ $student->student_id }}</p>
            @endif
        </div>

        <p>
            Estudiante del grado <strong>{{ $student->gradeLevel?->name ?? '—' }}</strong>,
            sección <strong>{{ $section?->name ?? '—' }}</strong>,
            del año escolar <strong>{{ $activeYear?->name ?? '—' }}</strong>,
            ha mantenido una <strong>conducta {{ $incidencias === 0 ? 'EXCELENTE' : ($incidencias <= 2 ? 'BUENA' : 'REGULAR') }}</strong>
            durante su permanencia en este centro educativo.
        </p>

        @if($incidencias === 0)
            <p>
                El/La estudiante <strong>no registra incidencias disciplinarias</strong> durante el presente año escolar,
                demostrando un comportamiento ejemplar, respeto a las normas de convivencia y compromiso con los valores institucionales.
            </p>
        @elseif($incidencias <= 2)
            <p>
                El/La estudiante registra {{ $incidencias }} incidencia(s) menor(es) durante el presente año escolar,
                manteniendo en general un buen comportamiento y respeto a las normas de convivencia.
            </p>
        @endif

        <p>
            Se expide la presente carta a solicitud de la parte interesada, para los fines que estime conveniente.
        </p>

        <p>
            Dada en {{ $schoolConfig?->city ?? 'Santo Domingo' }},
            República Dominicana, a los {{ now()->day }} días del mes de {{ now()->translatedFormat('F') }}
            del año {{ now()->year }}.
        </p>
    </div>

    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="text-align: center; width: 33%;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 180px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Orientador/a</p>
                </div>
            </td>
            <td style="text-align: center; width: 33%;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 180px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Director/a</p>
                </div>
            </td>
            <td style="text-align: center; width: 33%;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 180px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">Sello</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
