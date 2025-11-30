<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Calificaciones - {{ $data['schedule']->section_name }}</title>
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
        /* Encabezado usando tabla para alineación perfecta en PDF */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a8a; /* Línea separadora opcional */
            padding-bottom: 10px;
        }
        .header-logo-cell {
            width: 100px; /* Espacio reservado para el logo */
            vertical-align: middle;
        }
        .header-text-cell {
            text-align: center;
            vertical-align: middle;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a; /* Azul oscuro */
            margin: 0;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin: 5px 0 0 0;
            color: #374151; /* Gris oscuro */
        }
        
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
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }
        .grades-table th, 
        .grades-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .grades-table th {
            background-color: #f9fafb;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .student-name {
            text-align: left;
            padding-left: 10px;
        }
        .status-aprobado { color: #166534; font-weight: bold; }
        .status-reprobado { color: #991b1b; font-weight: bold; }
        
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9pt;
        }
        .meta-info {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            text-align: right;
        }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    {{-- Encabezado con Tabla para mejor alineación logo-texto --}}
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <img src="{{ public_path('centuu.png') }}" class="logo" alt="Logo">
            </td>
            <td class="header-text-cell">
                <div class="header">
                    <h1>Reporte de Calificaciones</h1>
                    <h2>
                        {{ $data['schedule']->module->course->name ?? 'Curso' }} - 
                        {{ $data['schedule']->module->name ?? 'Módulo' }}
                    </h2>
                </div>
            </td>
            <td style="width: 100px;"></td> {{-- Celda vacía para balancear si es necesario --}}
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
            <td class="label">Periodo</td>
            <td>
                {{ \Carbon\Carbon::parse($data['schedule']->start_date)->format('d/m/Y') }} - 
                {{ \Carbon\Carbon::parse($data['schedule']->end_date)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td class="label">Inscritos</td>
            <td>{{ count($data['enrollments']) }}</td>
            <td class="label">Generado</td>
            <td>{{ now()->format('d/m/Y h:i A') }}</td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th style="width: 100px;">Matrícula</th>
                <th>Estudiante</th>
                <th style="width: 120px;">Estado</th>
                <th style="width: 80px;">Nota Final</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['enrollments'] as $index => $enrollment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 10pt;">
                        {{-- Prioridad: student_code > cedula > id --}}
                        {{ $enrollment->student->student_code ?? $enrollment->student->cedula ?? $enrollment->student->id }}
                    </td>
                    <td class="student-name" style="text-transform: uppercase;">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    <td>
                        {{ $enrollment->status }}
                    </td>
                    <td>
                        @php $grade = $enrollment->final_grade ?? 0; @endphp
                        @if($enrollment->final_grade !== null)
                            <span class="{{ $grade < 70 ? 'status-reprobado' : 'status-aprobado' }}">
                                {{ $enrollment->final_grade }}
                            </span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 20px; color: #666;">No hay estudiantes inscritos en esta sección.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line">
                Firma del Docente
            </div>
        </div>
    </div>

    <div class="meta-info">
        Documento Oficial | Sistema de Gestión Académica
    </div>
</body>
</html>