@extends('reports.layouts.pdf')

@section('title', 'Listado de Estudiantes')
@section('subtitle')
{{ $data['schedule']->module->course->name ?? 'Curso' }} - {{ $data['schedule']->module->name ?? 'Módulo' }}
@endsection

@section('styles')
<style>
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        font-size: 9pt;
    }
    .info-table td {
        padding: 6px 8px;
        border: 1px solid #ddd;
    }
    .info-table .label {
        font-weight: bold;
        background-color: #f9fafb;
        width: 15%;
    }
    .student-name {
        text-align: left !important;
        padding-left: 10px;
        font-weight: bold;
        color: #1f2937;
    }
    .payment-pending { color: #991b1b; font-weight: bold; font-size: 8pt; }
    .payment-paid { color: #166534; font-weight: bold; font-size: 9pt; }
    .footer { margin-top: 50px; width: 100%; }
    .signature-box { float: right; width: 250px; text-align: center; }
    .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 9pt; }
</style>
@endsection

@section('content')
    <table class="info-table">
        <tr>
            <td class="label">Sección</td>
            <td>{{ $data['schedule']->section_name }}</td>
            <td class="label">Profesor</td>
            <td>{{ $data['schedule']->teacher->name ?? 'No asignado' }}</td>
        </tr>
        <tr>
            <td class="label">Horario</td>
            <td>
                {{ is_array($data['schedule']->days_of_week) ? implode(', ', $data['schedule']->days_of_week) : ($data['schedule']->days_of_week ?? '') }} | 
                {{ \Carbon\Carbon::parse($data['schedule']->start_time)->format('h:i A') }} - 
                {{ \Carbon\Carbon::parse($data['schedule']->end_time)->format('h:i A') }}
            </td>
            <td class="label">Inscritos</td>
            <td>{{ count($data['enrollments']) }}</td>
        </tr>
    </table>

    <table class="list-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 90px;" class="text-left">Matrícula</th>
                <th class="text-left">Nombre del Estudiante</th>
                <th style="width: 90px;">Teléfono</th>
                <th style="width: 80px;">Inscripción</th>
                <th style="width: 80px;">Estado Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['enrollments'] as $index => $enrollment)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left font-mono" style="font-size: 9pt;">
                        {{ $enrollment->student->student_code ?? $enrollment->student->cedula ?? $enrollment->student->id }}
                    </td>
                    <td class="student-name uppercase">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    <td class="text-center" style="font-size: 9pt;">
                        {{ $enrollment->student->mobile_phone ?? $enrollment->student->phone ?? '-' }}
                    </td>
                    <td class="text-center" style="font-size: 9pt;">
                        {{ \Carbon\Carbon::parse($enrollment->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        @if($enrollment->is_paid)
                            <span class="payment-paid">PAGADO</span>
                        @else
                            <span class="payment-pending">PENDIENTE</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; color: #666; text-align: center;">No hay estudiantes inscritos en esta sección.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line">
                Firma Autorizada
            </div>
        </div>
    </div>
@endsection