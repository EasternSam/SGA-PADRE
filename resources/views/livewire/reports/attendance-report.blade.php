<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Asistencia</title>
    <style>
        /* --- Configuración de Página: Carta Horizontal --- */
        @page { 
            margin: 0.5in; /* Margen estándar de 1.27cm */
            size: letter landscape; /* Formato Carta Horizontal */
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.2; 
        }

        /* --- Encabezado --- */
        .header-container {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
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
            max-width: 120px;
            max-height: 60px;
            height: auto;
        }
        
        .title-cell {
            width: 85%;
            vertical-align: middle;
            text-align: right;
        }

        .institution-name {
            font-size: 12px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 4px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 12px;
            color: #2c3e50;
        }

        /* --- Información del Curso --- */
        .info-container {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 -10px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px 12px;
            width: 25%;
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
            table-layout: fixed; /* Mantiene las columnas firmes */
        }
        
        /* Cabeceras */
        .attendance-table th {
            background-color: #2c3e50;
            color: #ecf0f1;
            font-weight: normal;
            border: 1px solid #34495e;
            vertical-align: bottom;
            padding: 0;
            height: 80px;
            overflow: hidden;
        }

        /* Rotación de Texto */
        .rotate-wrapper {
            position: relative;
            height: 75px;
            width: 18px; /* Ancho ajustado para Letter */
            margin: 0 auto;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left bottom;
            white-space: nowrap;
            position: absolute;
            bottom: 4px;
            left: 10px; 
            width: 75px;
            text-align: left;
            font-size: 8px;
        }

        /* Filas y Celdas */
        .attendance-table td {
            border: 1px solid #bdc3c7;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: #fbfcfc;
        }

        /* --- Columnas (Ajustadas para ~950px útiles de Letter Landscape) --- */
        .col-index { 
            width: 25px; 
            color: #7f8c8d; 
            font-size: 8px;
        }
        
        .col-student { 
            text-align: left !important; 
            padding: 4px 6px !important;
            font-weight: 600; 
            color: #2c3e50; 
            width: 240px; /* Suficiente para nombres largos */
            font-size: 9px;
            white-space: normal;
            word-wrap: normal; /* No partir palabras si es posible */
            line-height: 1.1;
            text-transform: uppercase;
        }

        /* 31 días * 18px = 558px */
        .col-date {
            width: 18px; 
        }

        /* 4 columnas * 25px = 100px */
        .col-summary {
            background-color: #ecf0f1 !important;
            color: #2c3e50 !important;
            font-weight: bold;
            width: 25px;
            border-color: #bdc3c7 !important;
            font-size: 9px;
        }
        
        .th-summary {
            background-color: #95a5a6 !important;
            color: white !important;
            border-color: #7f8c8d !important;
            height: auto !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 4px !important;
            font-size: 8px;
        }

        /* --- ESTADOS --- */
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
        
        .legend {
            float: left;
            font-size: 8px;
            color: #555;
        }
        
        .legend span {
            display: inline-block;
            margin-right: 15px;
        }
        
        .legend-box {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 4px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
        
        .page-info {
            float: right;
            font-size: 8px;
            color: #999;
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
                    <th style="width: 25px; height: auto; text-align: center;">No.</th>
                    <th style="width: 240px; height: auto; text-align: left; padding-left: 8px;">ESTUDIANTE</th>
                    
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
    
    <!-- Footer -->
    <div class="footer-wrapper">
        <div class="legend">
            <strong>Leyenda:</strong>
            <span><div class="legend-box" style="background-color: #d5f5e3;"></div> Presente</span>
            <span><div class="legend-box" style="background-color: #fadbd8;"></div> Ausente</span>
            <span><div class="legend-box" style="background-color: #fdebd0;"></div> Tardanza</span>
        </div>
        <div class="page-info">
            Generado el: {{ now()->format('d/m/Y h:i A') }}
        </div>
    </div>

</body>
</html>