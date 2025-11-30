<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #000;
        }
        
        /* Layout del Encabezado */
        .header-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo-wrapper {
            float: left;
            width: 20%;
        }
        .logo-wrapper img {
            max-width: 120px;
            height: auto;
        }
        .title-wrapper {
            float: left;
            width: 80%;
            text-align: right;
        }
        .main-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .sub-title {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        /* Limpiar floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Tabla de Información del Curso */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-table td {
            padding: 3px 5px;
            border: 1px solid #999;
        }
        .info-label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 12%;
        }
        .info-value {
            width: 38%;
        }

        /* Tabla de Asistencia */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-table th, .attendance-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }
        
        /* Encabezados */
        .attendance-table thead th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 9px;
        }

        /* Columnas específicas */
        .col-index { width: 25px; }
        .col-name { 
            text-align: left !important; 
            padding-left: 5px !important; 
            width: 180px; 
            text-transform: uppercase;
        }
        .col-stats { width: 25px; background-color: #f9f9f9; }

        /* Rotación de fechas */
        .rotate-wrapper {
            height: 60px;
            vertical-align: bottom;
            position: relative;
        }
        .rotate {
            transform: rotate(-90deg);
            white-space: nowrap;
            display: block;
            width: 20px; /* Ancho forzado para la celda rotada */
            margin: 0 auto;
            margin-bottom: 5px; 
        }

        /* Estados */
        .status-P { color: #000; font-weight: bold; }
        .status-A { color: red; font-weight: bold; background-color: #ffeeee; }
        .status-T { color: orange; font-weight: bold; }
        .status-empty { color: #ddd; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 9px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header-container clearfix">
        <div class="logo-wrapper">
            <!-- Intenta usar public_path para imágenes locales en DOMPDF -->
            <img src="{{ public_path('centuu.png') }}" alt="Logo" onerror="this.style.display='none'">
        </div>
        <div class="title-wrapper">
            <div class="main-title">Listado de Asistencia</div>
            <div class="sub-title">{{ $section->module->course->name }} - {{ $section->module->name }}</div>
        </div>
    </div>

    <!-- Información del Curso -->
    <table class="info-table">
        <tr>
            <td class="info-label">Profesor:</td>
            <td class="info-value">{{ $section->teacher->name ?? 'Sin asignar' }}</td>
            <td class="info-label">Sección:</td>
            <td class="info-value">{{ $section->section_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Duración:</td>
            <td class="info-value">
                {{ \Carbon\Carbon::parse($section->start_date)->format('d/m/Y') }} - 
                {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/Y') }}
            </td>
            <td class="info-label">Inscritos:</td>
            <td class="info-value">{{ $enrollments->count() }} Estudiantes</td>
        </tr>
    </table>

    <!-- Tabla Principal -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th class="col-index">No.</th>
                <th class="col-name">Estudiante</th>
                
                @foreach($dates as $date)
                    <th class="rotate-wrapper">
                        <span class="rotate">
                            {{ $date->format('d') }}/{{ $date->format('m') }}
                        </span>
                    </th>
                @endforeach
                
                <th class="col-stats">P</th>
                <th class="col-stats">A</th>
                <th class="col-stats">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $index => $enrollment)
                @php
                    $present = 0;
                    $absent = 0;
                    $totalRecorded = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-name">
                        {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                    </td>
                    
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $record = $attendances[$dateStr][$enrollment->id] ?? null;
                            
                            $char = '';
                            $class = 'status-empty';

                            if ($record) {
                                if ($record->status == 'Presente') { 
                                    $present++; $totalRecorded++; 
                                    $char = '•'; // Punto para presente o 'P'
                                    $class = 'status-P';
                                } elseif ($record->status == 'Ausente') { 
                                    $absent++; $totalRecorded++; 
                                    $char = 'A'; 
                                    $class = 'status-A';
                                } elseif ($record->status == 'Tardanza') { 
                                    $present++; $totalRecorded++; 
                                    $char = 'T'; 
                                    $class = 'status-T';
                                }
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $char }}</td>
                    @endforeach

                    {{-- Totales --}}
                    @php
                        // Calcular porcentaje basado en el total de clases registradas hasta ahora
                        // O basado en el total de clases del curso si se prefiere proyección
                        $percentage = $totalRecorded > 0 ? round(($present / $totalRecorded) * 100) : 0;
                    @endphp
                    <td class="col-stats">{{ $present }}</td>
                    <td class="col-stats">{{ $absent }}</td>
                    <td class="col-stats">{{ $percentage }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el: {{ now()->format('d/m/Y H:i A') }} | Sistema de Gestión Académica
    </div>

</body>
</html>