<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nómina de Estudiantes - {{ $data['schedule']->section_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            background: white;
        }
        @page {
            margin: 1.5cm;
        }
        /* Encabezado */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .header-logo-cell {
            width: 180px;
            vertical-align: middle;
            text-align: left;
        }
        .header-text-cell {
            text-align: right;
            vertical-align: middle;
        }
        .logo {
            width: 150px;
            height: auto;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin: 5px 0 0 0;
            color: #374151;
        }
        
        /* Información */
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

        /* Tabla de Datos */
        .list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }
        .list-table th, 
        .list-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .list-table th {
            background-color: #f9fafb;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        
        /* Alineaciones */
        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: monospace; }
        .uppercase { text-transform: uppercase; }
        
        .student-name {
            text-align: left !important;
            padding-left: 10px;
            font-weight: bold;
            color: #1f2937;
        }
        
        /* Colores Estado Pago */
        .payment-pending { color: #991b1b; font-weight: bold; font-size: 8pt; }
        .payment-paid { color: #166534; font-size: 9pt; }

        .footer { margin-top: 50px; width: 100%; }
        .signature-box { float: right; width: 250px; text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 9pt; }
        .meta-info { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 5px; text-align: right; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <img src="{{ public_path('centuu.png') }}" class="logo" alt="Logo">
            </td>
            <td class="header-text-cell">
                <div class="header">
                    <h1>Nómina de Estudiantes</h1>
                    <h2>
                        {{ $data['schedule']->module->course->name ?? 'Curso' }} - 
                        {{ $data['schedule']->module->name ?? 'Módulo' }}
                    </h2>
                </div>
            </td>
        </tr>
    </table>

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
                <th style="width: 80px;">Fecha Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['enrollments'] as $index => $enrollment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left font-mono" style="font-size: 9pt;">
                        {{ $enrollment->student->student_code ?? $enrollment->student->cedula ?? $enrollment->student->id }}
                    </td>
                    <td class="student-name uppercase">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    <td style="font-size: 9pt;">
                        {{ $enrollment->student->mobile_phone ?? $enrollment->student->phone ?? '-' }}
                    </td>
                    <td style="font-size: 9pt;">
                        {{ \Carbon\Carbon::parse($enrollment->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        {{-- Lógica para Fecha de Pago --}}
                        @if($enrollment->payment && ($enrollment->payment->status == 'Paid' || $enrollment->payment->status == 'Pagado' || $enrollment->payment->payment_date))
                            <span class="payment-paid">
                                {{ \Carbon\Carbon::parse($enrollment->payment->payment_date ?? $enrollment->payment->updated_at)->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="payment-pending">PENDIENTE</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; color: #666;">No hay estudiantes inscritos en esta sección.</td>
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

    <div class="meta-info">
        Documento Oficial | Sistema de Gestión Académica | Generado: {{ now()->format('d/m/Y h:i A') }}
    </div>
</body>
</html>