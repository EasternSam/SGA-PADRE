<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px 0; font-size: 18px; color: #2c3e50; }
        .header h2 { margin: 0; font-size: 14px; font-weight: normal; color: #7f8c8d; }
        
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 4px; }
        .label { font-weight: bold; width: 100px; }

        .attendance-table { width: 100%; border-collapse: collapse; }
        .attendance-table th, .attendance-table td { border: 1px solid #ccc; padding: 4px; text-align: center; }
        .attendance-table th { background-color: #f2f2f2; font-size: 10px; }
        .student-col { text-align: left !important; padding-left: 8px !important; width: 200px; }
        
        /* Rotar encabezados de fecha para ahorrar espacio */
        .rotate { height: 80px; white-space: nowrap; }
        .rotate > div { transform: rotate(-90deg); width: 30px; }

        .status-P { color: green; font-weight: bold; }
        .status-A { color: red; font-weight: bold; }
        .status-T { color: orange; font-weight: bold; }
        .status-NA { color: #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Asistencia</h1>
        <h2>{{ $section->module->course->name }} - {{ $section->module->name }} ({{ $section->section_name }})</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Profesor:</td>
            <td>{{ $section->teacher->name ?? 'N/A' }}</td>
            <td class="label">Periodo:</td>
            <td>{{ \Carbon\Carbon::parse($section->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="student-col">Estudiante</th>
                @foreach($dates as $date)
                    <th class="rotate">
                        <div>{{ $date->format('d-M') }}</div>
                    </th>
                @endforeach
                <th width="30">P</th>
                <th width="30">A</th>
                <th width="30">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $enrollment)
                @php
                    $present = 0;
                    $absent = 0;
                    $totalRecorded = 0;
                @endphp
                <tr>
                    <td class="student-col">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</td>
                    
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date->format('Y-m-d');
                            // Usamos la estructura agrupada que definimos en el controlador
                            // $attendances[$dateStr][$enrollmentId]
                            $record = $attendances[$dateStr][$enrollment->id] ?? null;
                            $status = $record ? $record->status : '-';
                            $class = 'status-NA';

                            if ($status == 'Presente') { $present++; $totalRecorded++; $class = 'status-P'; $char = 'P'; }
                            elseif ($status == 'Ausente') { $absent++; $totalRecorded++; $class = 'status-A'; $char = 'A'; }
                            elseif ($status == 'Tardanza') { $present++; $totalRecorded++; $class = 'status-T'; $char = 'T'; } // T cuenta como P o medio P según regla
                            else { $char = '-'; }
                        @endphp
                        <td class="{{ $class }}">{{ $char }}</td>
                    @endforeach

                    {{-- Totales --}}
                    <td>{{ $present }}</td>
                    <td>{{ $absent }}</td>
                    <td>{{ $totalRecorded > 0 ? round(($present / $totalRecorded) * 100) . '%' : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>