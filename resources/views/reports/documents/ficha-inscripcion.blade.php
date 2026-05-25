@extends('reports.layouts.pdf')
@section('title', 'Ficha de Inscripción')
@section('subtitle', ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))

@section('styles')
    .field-label { font-size: 7pt; color: #6b7280; text-transform: uppercase; font-weight: bold; }
    .field-value { font-size: 9pt; color: #1e293b; border-bottom: 1px solid #cbd5e0; padding: 2px 0 3px; min-height: 15px; }
    .section-title { background: #1e3a8a; color: white; padding: 6px 10px; font-weight: bold; font-size: 9pt; text-transform: uppercase; margin-top: 10px; }
    td { vertical-align: top; padding: 3px 5px !important; }
@endsection

@section('content')
    {{-- Datos del Estudiante --}}
    <div class="section-title">1. DATOS DEL ESTUDIANTE</div>
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <td style="width: 33%;">
                <div class="field-label">Nombres</div>
                <div class="field-value">{{ $student->first_name ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="field-label">Apellidos</div>
                <div class="field-value">{{ $student->last_name ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="field-label">Matrícula</div>
                <div class="field-value">{{ $student->student_code ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Fecha de Nacimiento</div>
                <div class="field-value">{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '' }}</div>
            </td>
            <td>
                <div class="field-label">Sexo</div>
                <div class="field-value">{{ $student->gender ?? '' }}</div>
            </td>
            <td>
                <div class="field-label">Nacionalidad</div>
                <div class="field-value">{{ $student->nationality ?? 'Dominicana' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">No. Documento (NUI/Cédula)</div>
                <div class="field-value">{{ $student->cedula ?? '' }}</div>
            </td>
            <td colspan="2">
                <div class="field-label">Dirección</div>
                <div class="field-value">{{ $student->address ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Teléfono</div>
                <div class="field-value">{{ $student->mobile_phone ?? $student->home_phone ?? '' }}</div>
            </td>
            <td>
                <div class="field-label">Email</div>
                <div class="field-value">{{ $student->email ?? '' }}</div>
            </td>
            <td>
                <div class="field-label">Tipo de Sangre</div>
                <div class="field-value">{{ $student->blood_type ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- Datos Académicos --}}
    <div class="section-title">2. DATOS ACADÉMICOS</div>
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <td style="width: 33%;">
                <div class="field-label">Grado</div>
                <div class="field-value">{{ $student->gradeLevel?->name ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="field-label">Sección</div>
                <div class="field-value">{{ $section?->name ?? '' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="field-label">Año Escolar</div>
                <div class="field-value">{{ $activeYear?->name ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Escuela de Procedencia</div>
                <div class="field-value">{{ $student->previous_school ?? '' }}</div>
            </td>
            <td>
                <div class="field-label">Condición</div>
                <div class="field-value">{{ $student->enrollment_type ?? 'Nuevo Ingreso' }}</div>
            </td>
            <td>
                <div class="field-label">Tanda</div>
                <div class="field-value">{{ $schoolConfig?->shift ? ucfirst(str_replace('_', ' ', $schoolConfig->shift)) : '' }}</div>
            </td>
        </tr>
    </table>

    {{-- Datos del Padre/Madre/Tutor --}}
    <div class="section-title">3. DATOS DEL PADRE / MADRE / TUTOR</div>
    @forelse($guardians as $g)
        <table style="width: 100%; margin-top: 5px; {{ !$loop->last ? 'margin-bottom: 5px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 5px;' : '' }}">
            <tr>
                <td style="width: 33%;">
                    <div class="field-label">Nombre Completo</div>
                    <div class="field-value">{{ $g->full_name }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="field-label">Relación</div>
                    <div class="field-value">{{ \App\Models\Guardian::RELATIONSHIPS[$g->relationship] ?? $g->relationship }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="field-label">Cédula</div>
                    <div class="field-value">{{ $g->cedula ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-label">Teléfono</div>
                    <div class="field-value">{{ $g->phone ?? '' }}</div>
                </td>
                <td>
                    <div class="field-label">Ocupación</div>
                    <div class="field-value">{{ $g->occupation ?? '' }}</div>
                </td>
                <td>
                    <div class="field-label">Lugar de Trabajo</div>
                    <div class="field-value">{{ $g->workplace ?? '' }}</div>
                </td>
            </tr>
        </table>
    @empty
        <table style="width: 100%; margin-top: 5px;">
            <tr>
                <td style="width: 33%;"><div class="field-label">Nombre</div><div class="field-value">&nbsp;</div></td>
                <td style="width: 33%;"><div class="field-label">Relación</div><div class="field-value">&nbsp;</div></td>
                <td style="width: 33%;"><div class="field-label">Cédula</div><div class="field-value">&nbsp;</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Teléfono</div><div class="field-value">&nbsp;</div></td>
                <td><div class="field-label">Ocupación</div><div class="field-value">&nbsp;</div></td>
                <td><div class="field-label">Dirección</div><div class="field-value">&nbsp;</div></td>
            </tr>
        </table>
    @endforelse

    {{-- Documentos --}}
    <div class="section-title">4. DOCUMENTOS ENTREGADOS</div>
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <td style="width: 50%;"><input type="checkbox" /> Acta de Nacimiento</td>
            <td style="width: 50%;"><input type="checkbox" /> Fotos 2x2</td>
        </tr>
        <tr>
            <td><input type="checkbox" /> Récord de Notas</td>
            <td><input type="checkbox" /> Certificado Médico</td>
        </tr>
        <tr>
            <td><input type="checkbox" /> Carta de Buena Conducta</td>
            <td><input type="checkbox" /> Copia Cédula Padre/Madre</td>
        </tr>
        <tr>
            <td><input type="checkbox" /> Carta de Transferencia</td>
            <td><input type="checkbox" /> Tarjeta de Vacunación</td>
        </tr>
    </table>

    {{-- Firmas --}}
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                    <p style="font-size: 8pt; margin: 0;">Padre/Madre/Tutor</p>
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                    <p style="font-size: 8pt; margin: 0;">Registro Académico</p>
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 150px; padding-top: 8px;">
                    <p style="font-size: 8pt; margin: 0;">Director/a</p>
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 7pt; color: #9ca3af; margin-top: 10px;">
        Fecha de inscripción: {{ now()->format('d/m/Y') }}
    </div>
@endsection
