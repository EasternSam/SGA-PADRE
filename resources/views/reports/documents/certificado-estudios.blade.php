@extends('reports.layouts.pdf')
@section('title', 'Certificado de Estudios')
@section('subtitle', 'Documento Oficial')

@section('styles')
    .certificate-border {
        border: 3px solid #1e3a8a;
        padding: 30px;
        margin: 10px;
        border-radius: 5px;
        position: relative;
    }
    .certificate-border::before {
        content: '';
        position: absolute;
        top: 5px; left: 5px; right: 5px; bottom: 5px;
        border: 1px solid #93c5fd;
    }
@endsection

@section('content')
    <div class="certificate-border">
        <div style="text-align: center; margin-bottom: 25px;">
            <p style="font-size: 10pt; color: #6b7280; letter-spacing: 3px; text-transform: uppercase;">REPÚBLICA DOMINICANA</p>
            <p style="font-size: 10pt; color: #6b7280; letter-spacing: 3px; text-transform: uppercase;">MINISTERIO DE EDUCACIÓN</p>
            <h2 style="font-size: 18pt; color: #1e3a8a; margin: 15px 0; letter-spacing: 4px;">CERTIFICADO DE ESTUDIOS</h2>
            <div style="width: 80px; height: 3px; background: #1e3a8a; margin: 0 auto;"></div>
        </div>

        <div style="line-height: 2.2; font-size: 11pt; text-align: justify; padding: 0 15px;">
            <p>
                El/La Director/a del centro educativo
                <strong>{{ $schoolConfig?->school_name ?? \App\Models\Setting::get('institution_name', 'Centro Educativo') }}</strong>
                @if($schoolConfig?->minerd_code)
                    (Código: <strong>{{ $schoolConfig->minerd_code }}</strong>)
                @endif
                certifica que:
            </p>

            <div style="text-align: center; margin: 20px 0; padding: 15px; border: 2px solid #1e3a8a; background: #eff6ff;">
                <p style="font-size: 16pt; font-weight: bold; color: #1e3a8a; margin: 0;">
                    {{ $student->first_name }} {{ $student->last_name }}
                </p>
                <p style="font-size: 10pt; color: #4b5563; margin: 5px 0 0;">
                    @if($student->student_id) Matrícula: {{ $student->student_id }} | @endif
                    @if($student->birth_date) Fecha Nac.: {{ \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') }} | @endif
                    @if($student->identity_number) Documento: {{ $student->identity_number }} @endif
                </p>
            </div>

            <p>
                Ha cursado y
                @if($promotion?->result === 'promoted')
                    <strong style="color: #166534;">APROBADO</strong>
                @elseif($promotion?->result === 'graduated')
                    <strong style="color: #b45309;">COMPLETADO</strong>
                @else
                    cursado
                @endif
                el grado <strong>{{ $student->gradeLevel?->name ?? '—' }}</strong>
                del nivel {{ $student->gradeLevel?->level ?? 'Básico' }},
                durante el año escolar <strong>{{ $activeYear?->name ?? '—' }}</strong>,
                @if($finalAvg)
                    obteniendo un promedio general de <strong>{{ $finalAvg }}</strong> puntos
                    sobre una escala de 100.
                @endif
            </p>

            @if($finalAvg)
                <div style="text-align: center; margin: 15px 0;">
                    <span style="display: inline-block; padding: 8px 25px; border-radius: 20px; font-size: 14pt; font-weight: bold;
                        {{ $finalAvg >= 90 ? 'background: #dcfce7; color: #166534;' : ($finalAvg >= 70 ? 'background: #fef3c7; color: #92400e;' : 'background: #fee2e2; color: #991b1b;') }}">
                        Promedio: {{ $finalAvg }}
                        @if($finalAvg >= 90) — Excelente
                        @elseif($finalAvg >= 80) — Muy Bueno
                        @elseif($finalAvg >= 70) — Bueno
                        @else — Debe Mejorar
                        @endif
                    </span>
                </div>
            @endif

            <p>
                Se expide el presente certificado en {{ $schoolConfig?->city ?? 'Santo Domingo' }},
                República Dominicana, a los {{ now()->day }} días del mes de {{ now()->translatedFormat('F') }}
                del año {{ now()->year }}.
            </p>
        </div>

        <table style="width: 100%; margin-top: 40px;">
            <tr>
                <td style="text-align: center; width: 33%; padding-top: 25px;">
                    <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                        <p style="font-size: 8pt; font-weight: bold; margin: 0;">Docente Titular</p>
                    </div>
                </td>
                <td style="text-align: center; width: 33%; padding-top: 25px;">
                    <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                        <p style="font-size: 8pt; font-weight: bold; margin: 0;">Director/a</p>
                    </div>
                </td>
                <td style="text-align: center; width: 33%; padding-top: 25px;">
                    <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                        <p style="font-size: 8pt; font-weight: bold; margin: 0;">Sello</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
