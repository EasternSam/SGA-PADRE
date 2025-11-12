<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia - {{ $section->module->course->name }} - {{ $section->section_name ?? 'Sección ' . $section->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }
        @page {
            margin: 1.5cm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a; /* Azul oscuro */
            margin: 0;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            margin: 5px 0;
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
            width: 25%;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8pt;
        }
        .attendance-table th, 
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }
        .attendance-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .student-name {
            text-align: left;
            width: 180px; /* Ancho fijo para nombres */
        }
        /* Encabezados de fecha rotados */
        .date-header {
            height: 80px;
            width: 25px;
            min-width: 25px;
            max-width: 25px;
            vertical-align: bottom;
            padding: 5px;
            position: relative;
        }
        .date-header > div {
            transform: rotate(-90deg);
            white-space: nowrap;
            position: absolute;
            left: 0;
            bottom: 30px;
            width: 80px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        /* Colores para estados */
        .status-P { background-color: #dcfce7; } /* Verde claro */
        .status-A { background-color: #fee2e2; } /* Rojo claro */
        .status-T { background-color: #fef3c7; } /* Amarillo claro */
        .status-NA { background-color: #f3f4f6; } /* Gris claro */

        .legend {
            margin-top: 20px;
            font-size: 9pt;
        }
        .legend-item {
            display: inline-block;
            margin-right: 15px;
        }
        .legend-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #ccc;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        /* Para impresión: evitar que las filas de la tabla se corten entre páginas */
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="header">
            <h1>Reporte de Asistencia</h1>
            <h2>{{ $section->module->course->name ?? 'N/A' }} - {{ $section->module->name ?? 'N/A' }}</h2>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Sección</td>
                <td>{{ $section->section_name ?? $section->id }}</td>
                <td class="label">Profesor</td>
                <td>{{ $section->teacher->name ?? 'No asignado' }}</td>
            </tr>
            <tr>
                <td class="label">Horario</td>
                <td>{{ implode(', ', $section->days_of_week ?? []) }} | {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}</td>
                <td class="label">Duración</td>
                <td>{{ \Carbon\Carbon::parse($section->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Inscritos</td>
                <td>{{ $enrollments->count() }}</td>
                <td class="label">Total Días</td>
                <td>{{ $dates->count() }}</td>
            </tr>
        </table>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th classs="student-name" style="text-align: left; vertical-align: bottom;">Estudiante</th>
                    @foreach ($dates as $date)
                        @php
                            $dateString = $date->format('Y-m-d');
                            $headerClass = $attendances->has($dateString) ? '' : 'status-NA';
                        @endphp
                        <th class="date-header {{ $headerClass }}">
                            <div>{{ $date->format('d-M') }}</div>
                        </th>
                    @endforeach
                    <th style="width: 50px;">Total P</th>
                    <th style="width: 50px;">Total A</th>
                    <th style="width: 50px;">Total T</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $enrollment)
                    @php
                        $presentCount = 0;
                        $absentCount = 0;
                        $tardyCount = 0;
                    @endphp
                    <tr>
                        <td class="student-name">{{ $enrollment->student->fullName ?? 'N/A' }}</td>
                        
                        @foreach ($dates as $date)
                            @php
                                $dateString = $date->format('Y-m-d');
                                $status = 'N/A'; // Default: No se pasó lista
                                $class = 'status-NA';

                                if ($attendances->has($dateString)) {
                                    $studentAttendance = $attendances[$dateString]->get($enrollment->id);

                                    if ($studentAttendance) {
                                        $statusRaw = $studentAttendance->status;
                                        if ($statusRaw == 'Presente') {
                                            $status = 'P';
                                            $class = 'status-P';
                                            $presentCount++;
                                        } elseif ($statusRaw == 'Ausente') {
                                            $status = 'A';
                                            $class = 'status-A';
                                            $absentCount++;
                                        } elseif ($statusRaw == 'Tardanza') {
                                            $status = 'T';
                                            $class = 'status-T';
                                            $tardyCount++;
                                        } else {
                                            $status = '?'; // Estado desconocido
                                            $class = '';
                                        }
                                    } else {
                                        $status = '-'; 
                                        $class = '';
                                    }
                                }
                            @endphp
                            <td class-="status {{ $class }}">{{ $status }}</td>
                        @endforeach
                        
                        {{-- Totales por estudiante --}}
                        <td style="font-weight: bold;">{{ $presentCount }}</td>
                        <td style="font-weight: bold;">{{ $absentCount }}</td>
                        <td style="font-weight: bold;">{{ $tardyCount }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $dates->count() + 4 }}" style="text-align: center; padding: 20px;">
                            No hay estudiantes inscritos en esta sección.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="legend">
            <p>
                <span class="legend-item"><span class="legend-box status-P">&nbsp;</span> P: Presente</span>
                <span class="legend-item"><span class="legend-box status-A">&nbsp;</span> A: Ausente</span>
                <span class="legend-item"><span class="legend-box status-T">&nbsp;</span> T: Tardanza</span>
                <span class="legend-item"><span class="legend-box status-NA">&nbsp;</span> N/A: No se pasó asistencia</span>
                <span class="legend-item"><span class="legend-box">&nbsp;</span> - : Sin registro (ej. inscrito después)</span>
            </p>
        </div>
    </div>
</body>
</html>