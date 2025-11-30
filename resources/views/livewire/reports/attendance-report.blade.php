<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        /* --- Configuración General --- */
        @page { 
            margin: 0.5cm; 
            margin-top: 0.5cm; 
            size: a4 portrait; /* Hoja Vertical */
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9px; 
            color: #333; 
            line-height: 1.1; 
        }

        /* --- Encabezado --- */
        .header-container {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
        }
        
        .header-layout {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-cell {
            width: 15%;
            vertical-align: middle;
            text-align: left;
        }
        
        .logo-cell img {
            max-width: 100px;
            max-height: 50px;
            height: auto;
        }
        
        .title-cell {
            width: 85%;
            vertical-align: middle;
            text-align: right;
        }

        .institution-name {
            font-size: 10px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 10px;
            color: #2c3e50;
        }

        /* --- Información del Curso --- */
        .info-container {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2px 0;
            margin: 0 -2px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 4px 6px;
            width: 25%;
            vertical-align: top;
        }
        .label {
            display: block;
            font-size: 6px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .value {
            display: block;
            font-size: 9px;
            font-weight: bold;
            color: #34495e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Tabla de Asistencia Ajustada para Vertical --- */
        .table-container {
            width: 100%;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px; /* Fuente pequeña para que quepa */
            table-layout: fixed; /* Crucial para respetar anchos */
        }
        
        /* Cabeceras */
        .attendance-table th {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-weight: normal;
            border: 1px solid #34495e;
            vertical-align: bottom;
            padding: 0;
            height: 65px; /* Altura para rotación */
            overflow: hidden;
        }

        /* Rotación de Texto */
        .rotate-wrapper {
            position: relative;
            height: 60px;
            width: 11px; /* Ancho muy estrecho para que quepan 31 días */
            margin: 0 auto;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left bottom;
            white-space: nowrap;
            position: absolute;
            bottom: 2px;
            left: 8px; 
            width: 60px;
            text-align: left;
            font-size: 6px;
        }

        /* Filas y Celdas */
        .attendance-table td {
            border: 1px solid #bdc3c7;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: #fbfcfc;
        }

        /* --- Dimensiones de Columnas Calculadas (Total ~560px) --- */
        .col-index { 
            width: 15px; 
            color: #7f8c8d; 
        }
        
        .col-student { 
            text-align: left !important; 
            padding: 2px 3px !important;
            font-weight: 600; 
            color: #2c3e50; 
            width: 115px; /* Ancho optimizado para vertical */
            font-size: 7px;
            white-space: normal; /* Permitir salto de línea */
            word-wrap: break-word; /* Romper palabras largas si es necesario */
            line-height: 1;
            text-transform: uppercase;
        }

        /* Celdas de fechas */
        .col-date {
            width: 11px; /* 31 columnas * 11px = 341px */
        }

        .col-summary {
            background-color: #ecf0f1 !important;
            color: #2c3e50 !important;
            font-weight: bold;
            width: 18px; /* 4 columnas * 18px = 72px */
            border-color: #bdc3c7 !important;
            font-size: 7px;
        }
        
        .th-summary {
            background-color: #95a5a6 !important;
            color: white !important;
            border-color: #7f8c8d !important;
            height: auto !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 2px !important;
            font-size: 7px;
        }

        /* --- ESTADOS --- */
        .st-P { background-color: #d5f5e3; color: #196f3d; }
        .st-A { background-color: #fadbd8; color: #c0392b; }
        .st-T { background-color: #fdebd0; color: #d35400; }
        .st-none { color: #ecf0f1; }

        /* --- Footer --- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e5e5;
            padding-top: 4px;
            font-size: 7px;
            color: #999;
            text-align: center;
        }
        .legend {
            margin-top: 5px;
            font-size: 8px;
            text-align: left;
        }
        .legend span {
            display: inline-block;
            margin-right: 10px;
            padding: 1px 4px;
            border-radius: 2px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header-container">
        <table class="header-layout">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('centuu.png') }}" onerror="this.style.display='none'" alt="Logo">
                </td>
                <td class="title-cell">
                    <div class="institution-name">{{ config('app.name', 'Sistema Académico') }}</div>
                    <div class="report-title">Reporte de Asistencia</div>
                    <div class="report-subtitle">{{ $section->module->course->name }} &bull; {{ $section->module->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Información -->
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
                        {{ \Carbon\Carbon::parse($section->start_date)->format('d/m/y') }} - 
                        {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/y') }}
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
                    <!-- Ajuste de ancho a 115px para que quepa en A4 Vertical -->
                    <th style="width: 115px; height: auto; text-align: left; padding-left: 3px;">ESTUDIANTE</th>
                    
                    @foreach($dates as $date)
                        <th class="col-date">
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
    
    <div class="legend">
        <strong>Leyenda:</strong> 
        <span class="st-P">P = Presente</span>
        <span class="st-A">A = Ausente</span>
        <span class="st-T">T = Tardanza</span>
    </div>

    <div class="footer">
        Generado el: {{ now()->format('d/m/Y h:i A') }} &bull; Documento interno de control de asistencia
    </div>

</body>
</html>