<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        /* --- Configuración General --- */
        @page { margin: 1cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.3; 
        }

        /* --- Encabezado --- */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        .header-logo {
            float: left;
            width: 80px;
        }
        .header-logo img {
            max-width: 100%;
            height: auto;
        }
        .header-content {
            float: left;
            margin-left: 15px;
            padding-top: 5px;
        }
        .institution-name {
            font-size: 14px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 12px;
            color: #2c3e50;
        }

        /* Limpiar floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* --- Tarjetas de Información --- */
        .info-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0; /* Espacio horizontal entre celdas */
            margin: 0 -10px; /* Compensar el spacing externo */
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 8px 12px;
            width: 25%; /* 4 columnas */
            vertical-align: top;
        }
        .label {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .value {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #34495e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Tabla de Asistencia --- */
        .table-container {
            width: 100%;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        /* Cabeceras */
        .attendance-table th {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-weight: normal;
            border: 1px solid #34495e;
            vertical-align: bottom;
            padding: 4px;
            height: 90px; /* Altura para la rotación */
            white-space: nowrap;
        }

        /* Rotación de Texto */
        .rotate-wrapper {
            position: relative;
            height: 80px;
            width: 20px; /* Ancho fijo para columnas de fecha */
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left bottom;
            white-space: nowrap;
            position: absolute;
            bottom: 5px;
            left: 12px;
            width: 80px; /* Debe coincidir con height de rotate-wrapper aprox */
            text-align: left;
        }

        /* Filas y Celdas */
        .attendance-table td {
            border: 1px solid #bdc3c7;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: #fbfcfc;
        }

        /* Columnas Específicas */
        .col-index { width: 20px; color: #7f8c8d; }
        .col-student { 
            text-align: left !important; 
            padding-left: 8px !important; 
            font-weight: 600; 
            color: #2c3e50; 
            width: 180px;
        }
        .col-summary {
            background-color: #ecf0f1 !important;
            color: #2c3e50 !important;
            font-weight: bold;
            width: 25px;
            border-color: #bdc3c7 !important;
        }
        .th-summary {
            background-color: #95a5a6 !important;
            color: white !important;
            border-color: #7f8c8d !important;
            height: auto !important; /* No rotar estos */
            text-align: center !important;
            vertical-align: middle !important;
        }

        /* --- ESTADOS (La parte clave) --- */
        .status-cell {
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Presente: Verde */
        .st-P {
            background-color: #d5f5e3; /* Fondo verde muy claro */
            color: #196f3d; /* Texto verde oscuro */
        }
        /* Ausente: Rojo */
        .st-A {
            background-color: #fadbd8; /* Fondo rojo muy claro */
            color: #c0392b; /* Texto rojo oscuro */
        }
        /* Tardanza: Naranja */
        .st-T {
            background-color: #fdebd0; /* Fondo naranja claro */
            color: #d35400; /* Texto naranja */
        }
        /* Vacío/Sin registro */
        .st-none {
            color: #ecf0f1;
        }

        /* --- Footer --- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e5e5;
            padding-top: 8px;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
        .legend {
            margin-top: 10px;
            font-size: 9px;
            text-align: left;
        }
        .legend span {
            display: inline-block;
            margin-right: 15px;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header clearfix">
        <div class="header-logo">
            <img src="{{ public_path('centuu.png') }}" onerror="this.style.display='none'" alt="Logo">
        </div>
        <div class="header-content">
            <div class="institution-name">{{ config('app.name', 'SGA') }}</div>
            <div class="report-title">Reporte de Asistencia</div>
            <div class="report-subtitle">{{ $section->module->course->name }} &bull; {{ $section->module->name }}</div>
        </div>
    </div>

    <!-- Información en Cuadrícula -->
    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="info-box">
                    <span class="label">Profesor</span>
                    <span class="value">{{ $section->teacher->name ?? 'Sin Asignar' }}</span>
                </td>
                <td class="info-box">
                    <span class="label">Sección</span>
                    <span class="value">{{ $section->section_name }}</span>
                </td>
                <td class="info-box">
                    <span class="label">Estudiantes</span>
                    <span class="value">{{ $enrollments->count() }} Inscritos</span>
                </td>
                <td class="info-box">
                    <span class="label">Periodo</span>
                    <span class="value">
                        {{ \Carbon\Carbon::parse($section->start_date)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/Y') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla Principal -->
    <div class="table-container">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th style="width: 20px; height: auto; text-align: center;">#</th>
                    <th style="width: 180px; height: auto; text-align: left; padding-left: 8px;">Estudiante</th>
                    
                    @foreach($dates as $date)
                        <th>
                            <div class="rotate-wrapper">
                                <div class="rotate">
                                    {{ $date->format('d') }}-{{ substr($date->translatedFormat('F'), 0, 3) }}
                                </div>
                            </div>
                        </th>
                    @endforeach
                    
                    <th class="th-summary">P</th>
                    <th class="th-summary">A</th>
                    <th class="th-summary">T</th>
                    <th class="th-summary">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $index => $enrollment)
                    @php
                        $present = 0;
                        $absent = 0;
                        $tardy = 0;
                        $totalRecorded = 0;
                    @endphp
                    <tr>
                        <td class="col-index">{{ $index + 1 }}</td>
                        <td class="col-student">
                            {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                        </td>
                        
                        @foreach($dates as $date)
                            @php
                                $dateStr = $date->format('Y-m-d');
                                $record = $attendances[$dateStr][$enrollment->id] ?? null;
                                
                                $char = '';
                                $class = 'st-none';

                                if ($record) {
                                    if ($record->status == 'Presente') { 
                                        $present++; $totalRecorded++; 
                                        $char = 'P'; 
                                        $class = 'st-P';
                                    } elseif ($record->status == 'Ausente') { 
                                        $absent++; $totalRecorded++; 
                                        $char = 'A'; 
                                        $class = 'st-A';
                                    } elseif ($record->status == 'Tardanza') { 
                                        $tardy++; $totalRecorded++; // Tardanza cuenta como asistencia generalmente
                                        $char = 'T'; 
                                        $class = 'st-T';
                                    }
                                }
                            @endphp
                            <td class="status-cell {{ $class }}">{{ $char }}</td>
                        @endforeach

                        {{-- Cálculos finales --}}
                        @php
                            // Total asistencias (Presente + Tardanza)
                            $totalPresent = $present + $tardy;
                            $percentage = $totalRecorded > 0 ? round(($totalPresent / $totalRecorded) * 100) : 0;
                            
                            // Color del porcentaje
                            $percentColor = 'black';
                            if($percentage < 70) $percentColor = '#c0392b';
                            elseif($percentage < 85) $percentColor = '#d35400';
                        @endphp

                        <td class="col-summary">{{ $totalPresent }}</td>
                        <td class="col-summary" style="{{ $absent > 0 ? 'color:#c0392b;' : '' }}">{{ $absent }}</td>
                        <td class="col-summary">{{ $tardy }}</td>
                        <td class="col-summary" style="color: {{ $percentColor }}">{{ $percentage }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Leyenda -->
    <div class="legend">
        <strong>Leyenda:</strong> 
        <span class="st-P">P = Presente</span>
        <span class="st-A">A = Ausente</span>
        <span class="st-T">T = Tardanza</span>
    </div>

    <!-- Footer -->
    <div class="footer">
        Generado el: {{ now()->format('d/m/Y h:i A') }} &bull; Documento interno de control de asistencia
    </div>

</body>
</html>