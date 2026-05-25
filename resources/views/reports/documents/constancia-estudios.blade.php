@extends('reports.layouts.pdf')
@section('title', 'Constancia de Estudios')
@section('subtitle', 'Documento Oficial')

@section('content')
    <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
        <h2 style="font-size: 16pt; color: #1e3a8a; text-transform: uppercase; letter-spacing: 2px;">
            CONSTANCIA DE ESTUDIOS
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
            @if($schoolConfig?->regional)
                Regional <strong>{{ $schoolConfig->regional }}</strong>,
                Distrito Educativo <strong>{{ $schoolConfig->district ?? '—' }}</strong>,
            @endif
            hace constar que:
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
            Es estudiante activo/a de este centro educativo, cursando actualmente el grado
            <strong>{{ $student->gradeLevel?->name ?? '—' }}</strong>,
            sección <strong>{{ $section?->name ?? '—' }}</strong>,
            durante el año escolar <strong>{{ $activeYear?->name ?? '—' }}</strong>,
            en la tanda <strong>{{ $schoolConfig?->shift ? ucfirst(str_replace('_', ' ', $schoolConfig->shift)) : 'Regular' }}</strong>.
        </p>

        <p>
            Se expide la presente constancia a solicitud de la parte interesada, para los fines que estime conveniente.
        </p>

        <p>
            Dada en {{ $schoolConfig?->city ?? 'Santo Domingo' }},
            República Dominicana, a los {{ now()->day }} días del mes de {{ now()->translatedFormat('F') }}
            del año {{ now()->year }}.
        </p>
    </div>

    {{-- Firma --}}
    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="text-align: center; width: 50%;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 220px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; color: #374151; margin: 0;">Director/a</p>
                    <p style="font-size: 8pt; color: #6b7280; margin: 3px 0 0;">{{ $schoolConfig?->school_name ?? '' }}</p>
                </div>
            </td>
            <td style="text-align: center; width: 50%;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 220px; padding-top: 8px;">
                    <p style="font-size: 9pt; font-weight: bold; color: #374151; margin: 0;">Sello del Centro</p>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 7pt; color: #9ca3af;">
        Nota: Esta constancia tiene una validez de treinta (30) días a partir de la fecha de emisión.
    </div>
@endsection
