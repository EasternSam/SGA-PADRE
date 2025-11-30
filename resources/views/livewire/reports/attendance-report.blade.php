<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        /* --- Configuración General --- */
        @page { margin: 0.5cm; margin-top: 0.5cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.2; 
        }

        /* --- Encabezado Moderno --- */
        .header-wrapper {
            width: 100%;
            border-bottom: 2px solid #2c3e50; /* Línea separadora gruesa */
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-cell {
            width: 20%;
            vertical-align: middle;
        }
        
        .title-cell {
            width: 60%;
            text-align: center;
            vertical-align: middle;
        }
        
        .meta-cell {
            width: 20%;
            text-align: right;
            vertical-align: bottom;
            font-size: 8px;
            color: #7f8c8d;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        
        .institution-name {
            font-size: 10px;
            color: #7f8c8d;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        /* --- Bloque de Información del Curso (Estilo Tarjeta) --- */
        .course-info-panel {
            background-color: #f4f6f7;
            border: 1px solid #e5e7e9;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-grid td {
            vertical-align: top;
            padding: 0 10px;
            border-right: 1px solid #d0d3d4; /* Separador vertical */
        }
        
        .info-grid td:last-child {
            border-right: none;
        }

        .label {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #7f8c8d;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .value {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .sub-value {
            display: block;
            font-size: 9px;
            color: #555;
            margin-top: 1px;
        }

        /* --- Tabla de Asistencia --- */
        .table-container {
            width: 100%;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
        }
        
        .attendance-table th {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-weight: normal;
            border: 1px solid #34495e;
            vertical-align: bottom;
            padding: 1px;
            height: 70px; /* Altura para rotación */
            overflow: hidden;
        }

        .rotate-wrapper {
            position: relative;
            height: 65px;
            width: 16px;
            margin: 0 auto;
        }
        
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left bottom;
            white-space: nowrap;
            position: absolute;
            bottom: 2px;
            left: 10px;
            width: 65px;
            text-align: left;
            font-size: 7px;
        }

        .attendance-table td {
            border: 1px solid #bdc3c7;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            height: 14px;
        }
        
        .attendance-table tr:nth-child(even) { background-color: #fbfcfc; }

        /* Columnas */
        .col-index { width: 15px; color: #7f8c8d; font-size: 7px; }
        .col-student { 
            text-align: left !important; 
            padding-left: 4px !important; 
            font-weight: 600; 
            color: #2c3e50; 
            width: 130px;
            white-space: normal;
            line-height: 1;
            overflow: hidden; 
            text-transform: uppercase;
        }
        
        .col-summary {
            background-color: #ecf0f1 !important;
            color: #2c3e50 !important;
            font-weight: bold;
            width: 20px;
            border-color: #bdc3c7 !important;
            font-size: 8px;
        }
        
        .th-summary {
            background-color: #95a5a6 !important;
            color: white !important;
            border-color: #7f8c8d !important;
            height: auto !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 2px !important;
        }

        /* --- Estados --- */
        .st-P { background-color: #d5f5e3; color: #196f3d; font-weight: bold; }
        .st-A { background-color: #fadbd8; color: #c0392b; font-weight: bold; }
        .st-T { background-color: #fdebd0; color: #d35400; font-weight: bold; }
        .st-none { color: #ecf0f1; }

        /* --- Footer --- */
        .footer-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding-top: 5px;
            border-top: 1px solid #e5e5e5;
        }
        
        .legend span {
            display: inline-block;
            margin-right: 10px;
            font-size: 8px;
            color: #555;
        }
        
        .legend-box {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 3px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
        
        .page-info {
            float: right;
            font-size: 7px;
            color: #999;
        }
    </style>
</head>
<body>

    <!-- Encabezado Estilizado -->
    <div class="header-wrapper">
        <table class="header-table">
            <tr>
                <!-- Columna Logo -->
                <td class="logo-cell">
                    <img src="{{ public_path('centuu.png') }}" onerror="this.style.display='none'" style="max-height: 50px; width: auto;">
                </td>
                
                <!-- Columna Títulos -->
                <td class="title-cell">
                    <div class="institution-name">{{ config('app.name', 'Sistema Académico') }}</div>
                    <div class="report-title">Control de Asistencia</div>
                </td>
                
                <!-- Columna Meta Info (Fecha impresión) -->
                <td class="meta-cell">
                    <div>Generado el:</div>
                    <div>{{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Bloque de Información del Curso (Estilo Tarjeta con separadores) -->
    <div class="course-info-panel">
        <table class="info-grid">
            <tr>
                <td style="width: 35%;">
                    <span class="label">Curso / Módulo</span>
                    <span class="value">{{ $section->module->course->name }}</span>
                    <span class="sub-value">{{ $section->module->name }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Profesor</span>
                    <span class="value">{{ $section->teacher->name ?? 'Sin Asignar' }}</span>
                </td>
                <td style="width: 15%;">
                    <span class="label">Sección</span>
                    <span class="value">{{ $section->section_name }}</span>
                    <span class="sub-value">{{ $enrollments->count() }} Estudiantes</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Duración</span>
                    <span class="value">
                        {{ \Carbon\Carbon::parse($section->start_date)->format('d M') }} - 
                        {{ \Carbon\Carbon::parse($section->end_date)->format('d M, Y') }}
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
                    <th style="width: 15px; height: auto; text-align: center;">No.</th>
                    <th style="width: 130px; height: auto; text-align: left; padding-left: 4px;">ESTUDIANTE</th>
                    
                    @foreach($dates as $date)
                        <th style="width: 16px;">
                            <div class="rotate-wrapper">
                                <div class="rotate">
                                    {{ $date->format('d') }}-{{ substr($date->translatedFormat('M'), 0, 3) }}
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
                                        $tardy++; $totalRecorded++; 
                                        $char = 'T'; 
                                        $class = 'st-T';
                                    }
                                }
                            @endphp
                            <td class="{{ $class }}">{{ $char }}</td>
                        @endforeach

                        @php
                            $totalPresent = $present + $tardy;
                            $percentage = $totalRecorded > 0 ? round(($totalPresent / $totalRecorded) * 100) : 0;
                            
                            $percentColor = 'black';
                            if($percentage < 70) $percentColor = '#c0392b'; // Rojo si es bajo
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
    
    <!-- Footer y Leyenda -->
    <div class="footer-wrapper">
        <div style="float: left;" class="legend">
            <strong>Leyenda:</strong>
            <span><div class="legend-box" style="background-color: #d5f5e3;"></div> Presente</span>
            <span><div class="legend-box" style="background-color: #fadbd8;"></div> Ausente</span>
            <span><div class="legend-box" style="background-color: #fdebd0;"></div> Tardanza</span>
        </div>
        <div class="page-info">
            Documento Oficial
        </div>
    </div>

</body>
</html>