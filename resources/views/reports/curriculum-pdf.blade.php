<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pensum Académico - {{ $career->code }}</title>
    <style>
        /* Configuración de Página */
        @page {
            margin: 0cm 0cm;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin-top: 3cm; /* Espacio para el header fijo */
            margin-left: 1.5cm;
            margin-right: 1.5cm;
            margin-bottom: 2cm; /* Espacio para el footer fijo */
            background-color: #fff;
            color: #333;
            font-size: 12px;
        }

        /* Marca de Agua */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(200, 200, 200, 0.15);
            z-index: -1000;
            font-weight: bold;
            white-space: nowrap;
            text-align: center;
        }

        /* Header Fijo */
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            background-color: #1e1b4b; /* Indigo 950 */
            color: white;
            line-height: 2.5cm;
            padding: 0 1.5cm;
            z-index: 1000;
        }

        .header-content {
            display: block;
            width: 100%;
            height: 100%;
        }

        .institution-name {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            float: left;
        }

        .document-label {
            float: right;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: normal;
            opacity: 0.8;
            letter-spacing: 2px;
        }

        /* Footer Fijo */
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.2cm;
            background-color: #f3f4f6; /* Gray 100 */
            border-top: 3px solid #4338ca; /* Indigo 700 */
            color: #6b7280;
            text-align: center;
            line-height: 1.2cm;
            font-size: 10px;
        }

        /* Tarjeta de Información de la Carrera */
        .career-card {
            background-color: #f9fafb; /* Gray 50 */
            border: 1px solid #e5e7eb;
            border-left: 5px solid #4f46e5; /* Indigo 600 */
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 35px;
            position: relative;
        }

        .career-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827; /* Gray 900 */
            margin-bottom: 15px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td {
            padding: 4px 0;
            font-size: 11px;
            color: #4b5563;
        }

        .label {
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            width: 140px;
            font-size: 10px;
        }

        .value {
            font-weight: bold;
            color: #1f2937;
            font-size: 12px;
        }

        /* Contenedores de Cuatrimestres */
        .period-block {
            margin-bottom: 25px;
            page-break-inside: avoid; /* Evita cortar tablas a la mitad */
        }

        .period-header {
            background-color: #4338ca; /* Indigo 700 */
            color: white;
            padding: 6px 15px;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            border-radius: 4px 4px 0 0;
            display: inline-block;
            margin-bottom: 0;
        }

        .period-header-line {
            height: 2px;
            background-color: #4338ca; /* Indigo 700 */
            width: 100%;
            margin-bottom: 0;
        }

        /* Tablas de Materias */
        table.modules {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 0;
        }

        table.modules th {
            background-color: #eef2ff; /* Indigo 50 */
            color: #3730a3; /* Indigo 800 */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #c7d2fe;
        }

        table.modules td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }

        table.modules tr:last-child td {
            border-bottom: 1px solid #e5e7eb;
        }

        /* Columnas */
        .col-code { 
            width: 12%; 
            font-family: 'Courier New', monospace; 
            font-weight: 700;
            color: #1f2937;
        }
        .col-name { width: 50%; font-weight: 600; }
        .col-credits { width: 10%; text-align: center; font-weight: bold; }
        .col-prereq { 
            width: 28%; 
            color: #6b7280; 
            font-size: 10px; 
            font-style: italic; 
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 6px;
            vertical-align: middle;
        }

        .badge-elective {
            background-color: #fffbeb;
            color: #d97706;
            border: 1px solid #fcd34d;
        }

        /* Resumen por Periodo */
        .period-footer {
            background-color: #f9fafb;
            text-align: right;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #4b5563;
            border: 1px solid #f3f4f6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }

        /* Numeración de Página */
        .page-number:before {
            content: counter(page);
        }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <span class="institution-name">Centro Educativo Universitario</span>
            <span class="document-label">Pensum Oficial</span>
        </div>
    </header>

    <footer>
        Sistema de Gestión Académica - Documento generado el {{ $generatedAt }} | Página <span class="page-number"></span>
    </footer>

    <!-- Marca de agua sutil en el fondo -->
    <div class="watermark">{{ $career->code }}</div>

    <!-- Tarjeta de Información Principal -->
    <div class="career-card">
        <div class="career-title">{{ $career->name }}</div>
        
        <table class="meta-grid">
            <tr>
                <td class="label">Código Oficial:</td>
                <td class="value">{{ $career->code }}</td>
                <td class="label">Modalidad:</td>
                <td class="value">{{ $career->program_type === 'degree' ? 'Grado / Licenciatura' : 'Técnico Superior' }}</td>
            </tr>
            <tr>
                <td class="label">Créditos Totales:</td>
                <td class="value">{{ $career->total_credits }}</td>
                <td class="label">Duración:</td>
                <td class="value">{{ $career->duration_periods }} Periodos Académicos</td>
            </tr>
            @if($career->description)
            <tr>
                <td class="label" style="vertical-align: top; padding-top: 8px;">Descripción:</td>
                <td class="value" colspan="3" style="padding-top: 8px; font-weight: normal; font-style: italic;">
                    {{ $career->description }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    @php $totalAccumulated = 0; @endphp

    @foreach($modulesByPeriod as $period => $modules)
        <div class="period-block">
            <div class="period-header">
                Cuatrimestre {{ $period }}
            </div>
            <div class="period-header-line"></div>

            <table class="modules">
                <thead>
                    <tr>
                        <th class="col-code">Clave</th>
                        <th class="col-name">Asignatura</th>
                        <th class="col-credits">Créditos</th>
                        <th class="col-prereq">Prerrequisitos</th>
                    </tr>
                </thead>
                <tbody>
                    @php $periodCredits = 0; @endphp
                    @foreach($modules as $module)
                        @php $periodCredits += $module->credits; @endphp
                        <tr>
                            <td class="col-code">{{ $module->code }}</td>
                            <td class="col-name">
                                {{ $module->name }}
                                @if($module->is_elective)
                                    <span class="badge badge-elective">Electiva</span>
                                @endif
                            </td>
                            <td class="col-credits">{{ $module->credits }}</td>
                            <td class="col-prereq">
                                @if($module->prerequisites->count() > 0)
                                    @foreach($module->prerequisites as $pre)
                                        {{ $pre->code }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    <span style="color: #d1d5db;">---</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="period-footer">
                Total Créditos del Periodo: {{ $periodCredits }}
            </div>
            @php $totalAccumulated += $periodCredits; @endphp
        </div>
    @endforeach

    <div style="margin-top: 40px; text-align: center; border-top: 1px dashed #d1d5db; padding-top: 20px;">
        <p style="font-size: 10px; color: #9ca3af; text-transform: uppercase; font-weight: bold;">
            *** Fin del Pensum Académico ***
        </p>
        <p style="font-size: 9px; color: #9ca3af;">
            Este documento es de carácter informativo. La institución se reserva el derecho de realizar cambios curriculares.
        </p>
    </div>

</body>
</html>