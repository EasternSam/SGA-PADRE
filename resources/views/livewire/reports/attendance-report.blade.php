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

        /* --- Encabezado con Tabla para centrado perfecto --- */
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
            width: 15%; /* Espacio para el logo a la izquierda */
            vertical-align: middle; /* Centrado vertical */
            text-align: left;
        }
        
        .logo-cell img {
            max-width: 100px;
            max-height: 60px;
            height: auto;
        }
        
        .title-cell {
            width: 85%;
            vertical-align: middle;
            text-align: right; /* Títulos alineados a la derecha (o center si prefieres) */
        }

        .institution-name {
            font-size: 12px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 11px;
            color: #2c3e50;
        }

        /* --- Tarjetas de Información (Diseño Anterior) --- */
        .info-container {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin: 0 -5px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 5px 8px;
            width: 25%;
            vertical-align: top;
        }
        .label {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .value {
            display: block;
            font-size: 10px;
            font-weight: bold;
            color: #34495e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Tabla de Asistencia Compacta --- */
        .table-container {
            width: 100%;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
        }
        
        /* Cabeceras */
        .attendance-table th {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-weight: normal;
            border: 1px solid #34495e;
            vertical-align: bottom;
            padding: 1px;
            height: 70px;
            overflow: hidden;
        }

        /* Rotación de Texto */
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

        /* Filas y Celdas */
        .attendance-table td {
            border: 1px solid #bdc3c7;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            height: 14px;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: #fbfcfc;
        }

        /* Columnas Específicas */
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

    <!-- Encabezado Restaurado (Con Logo a la izquierda y centrado) -->
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

    <!-- Información (Estilo Anterior: Cajas separadas) -->
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