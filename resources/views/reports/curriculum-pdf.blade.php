<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pensum Académico - {{ $career->code }}</title>
    <style>
        /* CONFIGURACIÓN BÁSICA COMPATIBLE CON DOMPDF */
        @page {
            margin: 0cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f9fafb; /* Gray 50 */
            margin-top: 220px; /* Espacio para el header fijo */
            margin-bottom: 60px; /* Espacio para el footer fijo */
            color: #1e293b; /* Slate 800 */
        }

        /* --- HEADER (Simulación del diseño Flex/Grid usando Tablas) --- */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 220px;
            background-color: white;
            z-index: 1000;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }

        /* Lado Izquierdo (Morado) */
        .header-left {
            width: 35%;
            background-color: #7b1fa2; /* bg-itla-purple */
            color: white;
            vertical-align: middle;
            padding: 30px;
            position: relative;
        }

        /* Lado Derecho (Oscuro con Imagen) */
        .header-right {
            width: 65%;
            background-color: #111827; /* gray-900 */
            color: white;
            vertical-align: middle;
            padding: 30px;
            text-align: right;
            /* Nota: DomPDF soporta background-image limitado, usamos color sólido oscuro como fallback elegante o degradado */
            background: linear-gradient(180deg, #321c46 0%, #111827 100%);
        }

        /* Títulos Header */
        .app-initials {
            font-size: 50px;
            font-weight: 900;
            line-height: 1;
            margin: 0;
            font-style: italic;
            letter-spacing: -2px;
        }
        
        .separator-bar {
            height: 4px;
            width: 50px;
            background-color: #d8b4fe; /* purple-300 */
            margin: 10px 0;
            border-radius: 2px;
        }

        .institution-full {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 3px solid #d8b4fe;
            padding-left: 10px;
            line-height: 1.4;
        }

        .career-name {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.1;
        }

        .career-type {
            color: #e9d5ff; /* purple-200 */
            font-size: 16px;
            font-weight: normal;
        }

        .header-badge {
            background-color: rgba(255,255,255,0.1);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10px;
            display: inline-block;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* --- BARRA SLOGAN --- */
        .slogan-bar {
            background-color: #f3f4f6; /* gray-100 */
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #7b1fa2;
            font-style: italic;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 20px;
        }

        /* --- CONTENIDO --- */
        .content {
            padding: 0 40px;
        }

        /* Tabla de Módulos (Reemplaza grid-cols-12) */
        .modules-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .modules-table th {
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280; /* gray-500 */
            padding: 5px 10px;
            border-bottom: 2px solid #f3e8ff; /* purple-100 */
        }

        .modules-table td {
            padding: 8px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        /* Estilos de Celdas */
        .cell-code {
            width: 15%;
            font-weight: bold;
            color: #374151;
            font-family: monospace;
        }
        
        .cell-name {
            width: 50%;
            font-weight: bold;
            color: #111827;
        }

        .cell-credits {
            width: 10%;
            text-align: center;
            font-weight: bold;
            color: #4b5563;
        }

        .cell-prereq {
            width: 25%;
            text-align: right;
            color: #9ca3af;
            font-size: 10px;
        }

        /* Period Header */
        .period-header {
            margin-top: 25px;
            margin-bottom: 5px;
            page-break-inside: avoid;
        }

        .period-number {
            background-color: #f3e8ff; /* purple-100 */
            color: #7b1fa2;
            display: inline-block;
            width: 20px;
            height: 20px;
            text-align: center;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            line-height: 20px;
            margin-right: 8px;
        }

        .period-title {
            font-size: 14px;
            font-weight: bold;
            color: #7b1fa2;
            text-transform: uppercase;
        }

        .period-total {
            text-align: right;
            font-size: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 5px;
        }

        .total-badge {
            background-color: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            border: 1px solid #e5e7eb;
        }

        /* --- FOOTER --- */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: white;
            border-top: 3px solid #7b1fa2;
            padding: 10px 40px;
            font-size: 9px;
            color: #9ca3af;
        }

        /* Marca de Agua */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(123, 31, 162, 0.05); /* Morado muy muy tenue */
            z-index: -1000;
            font-weight: 900;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <!-- MARCA DE AGUA -->
    <div class="watermark">{{ $career->code }}</div>

    <!-- HEADER -->
    <header>
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <h1 class="app-initials">{{ strtoupper(substr(config('app.name', 'SGA'), 0, 3)) }}</h1>
                    <div class="separator-bar"></div>
                    <div class="institution-full">
                        Centro Educativo<br>Universitario
                    </div>
                    <div style="font-size: 9px; color: #e9d5ff; margin-top: 10px;">
                        {{ config('app.name', 'Sistema de Gestión') }}
                    </div>
                </td>
                <td class="header-right">
                    <div style="text-transform: uppercase; color: #d8b4fe; font-size: 10px; letter-spacing: 2px; margin-bottom: 5px;">
                        Plan de Estudios Oficial
                    </div>
                    <h2 class="career-name">
                        {{ $career->name }}
                    </h2>
                    <div class="career-type">
                        {{ $career->program_type === 'degree' ? 'GRADO ACADÉMICO' : 'CARRERA TÉCNICA' }}
                    </div>
                    <div class="header-badge">
                        CLAVE: <strong>{{ $career->code }}</strong> &nbsp;|&nbsp; 
                        GENERADO: <strong>{{ $generatedAt }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <!-- FOOTER -->
    <footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Institución Educativa') }} - Todos los derechos reservados.
                </td>
                <td style="text-align: right;">
                    Documento Oficial | {{ config('app.name', 'SGA System') }} v1.0
                </td>
            </tr>
        </table>
    </footer>

    <!-- SLOGAN -->
    <div class="slogan-bar">
        "Excelencia académica para el futuro"
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">

        <!-- RESUMEN ENCABEZADO (Tabla oculta en diseño móvil, visible en PDF) -->
        <table class="modules-table" style="margin-top: 20px; border-bottom: 2px solid #e5e7eb;">
            <thead>
                <tr>
                    <th width="20%">CÓDIGO</th>
                    <th width="45%">DESCRIPCIÓN DEL CURSO</th>
                    <th width="15%" style="text-align: center;">CRÉDITOS</th>
                    <th width="20%" style="text-align: right;">PRERREQUISITOS</th>
                </tr>
            </thead>
        </table>

        @php $totalAccumulated = 0; @endphp

        @foreach($modulesByPeriod as $period => $modules)
            @php $periodCredits = 0; @endphp
            
            <div class="period-header">
                <span class="period-number">{{ $period }}</span>
                <span class="period-title">Cuatrimestre {{ $period }}</span>
            </div>

            <table class="modules-table">
                <tbody>
                    @foreach($modules as $module)
                        @php $periodCredits += $module->credits; @endphp
                        <tr>
                            <td class="cell-code">{{ $module->code }}</td>
                            <td class="cell-name">
                                {{ $module->name }}
                                @if($module->is_elective)
                                    <span style="color: #d97706; font-size: 8px; margin-left: 5px; text-transform: uppercase;">(Electiva)</span>
                                @endif
                            </td>
                            <td class="cell-credits">{{ $module->credits }}</td>
                            <td class="cell-prereq">
                                @if($module->prerequisites->count() > 0)
                                    @foreach($module->prerequisites as $pre)
                                        {{ $pre->code }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="period-total">
                <span style="text-transform: uppercase; color: #9ca3af; margin-right: 10px; font-weight: bold;">Créditos del Periodo</span>
                <span class="total-badge">{{ $periodCredits }}</span>
            </div>

            @php $totalAccumulated += $periodCredits; @endphp
        @endforeach

        <!-- TOTAL GENERAL -->
        <div style="background-color: #faf5ff; border: 1px solid #e9d5ff; padding: 15px; border-radius: 8px; margin-top: 20px; page-break-inside: avoid;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 70%;">
                        <div style="color: #7b1fa2; font-weight: bold; font-size: 14px;">Resumen Académico</div>
                        <div style="color: #6b7280; font-size: 10px;">Total acumulado de la carrera</div>
                    </td>
                    <td style="width: 30%; text-align: right;">
                        <div style="font-size: 24px; font-weight: 900; color: #1f2937; line-height: 1;">{{ $totalAccumulated }}</div>
                        <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #9ca3af; letter-spacing: 1px;">Créditos Totales</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #cbd5e1; font-size: 9px;">
            *** Fin del Pensum Académico ***
        </div>

    </div>

    {{-- Script PHP para numeración de páginas --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = 520;
            $y = 810;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = null;
            $size = 8;
            $color = array(0.5, 0.5, 0.5);
            $word_space = 0.0;
            $char_space = 0.0;
            $angle = 0.0;
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

</body>
</html>