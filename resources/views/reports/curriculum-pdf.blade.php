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
            font-family: 'Helvetica', 'Arial', sans-serif; /* Fallback seguro para DomPDF */
            background-color: #f9fafb; /* Gray 50 */
            margin-top: 0px; 
            margin-bottom: 0px;
            color: #1e293b; /* Slate 800 */
        }

        /* --- FONTS --- */
        h1, h2, h3, .font-heading {
            font-family: sans-serif;
            font-weight: bold;
        }

        /* --- DECORATIVE BACKGROUND PATTERN --- */
        .bg-pattern {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;
            /* DomPDF tiene soporte limitado para gradientes complejos, usaremos un color sólido muy suave */
            background-color: #f9fafb;
        }

        /* --- CONTAINER PRINCIPAL --- */
        .container {
            position: relative;
            z-index: 10;
            background-color: white;
            /* box-shadow no soportado en DomPDF para bloques grandes, se omite */
            width: 90%; 
            margin: 40px auto;
            overflow: hidden;
        }

        /* --- HEADER LAYOUT (Tabla para simular Flex) --- */
        header {
            width: 100%;
            border-bottom: 4px solid #6b21a8; /* purple-800 */
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Logo Area (Left) */
        .logo-area {
            background-color: #7b1fa2; /* itla-purple */
            color: white;
            padding: 2rem;
            width: 35%;
            vertical-align: middle;
            position: relative;
        }

        /* Logo Decoration (Simulado con borde o imagen de fondo simple si es posible) */
        .logo-decoration {
            /* DomPDF no soporta blur o formas complejas absolutas bien, simplificamos */
        }

        .logo-siglas {
            font-size: 3.5rem; 
            line-height: 1; 
            font-style: italic; 
            font-weight: 900;
            margin: 0;
            margin-bottom: 5px;
        }
        
        .logo-divider { 
            height: 4px; 
            width: 50px; 
            background-color: #d8b4fe; /* purple-300 */
            margin-bottom: 1rem; 
            border-radius: 2px;
        }
        
        .logo-subtitle {
            font-size: 10px; 
            font-weight: 500; 
            text-transform: uppercase; 
            line-height: 1.4; 
            letter-spacing: 1px;
            border-left: 3px solid #d8b4fe; 
            padding-left: 10px;
        }
        
        .logo-english { 
            margin-top: 10px; 
            font-size: 9px; 
            color: #e9d5ff; 
            font-style: italic; 
            font-weight: 300; 
        }

        /* Title Area (Right) */
        .title-area {
            width: 65%;
            background-color: #1f1f1f; /* Gray 900 */
            /* Gradiente simple soportado */
            background: linear-gradient(to top, #321c46 0%, #111827 100%);
            vertical-align: bottom;
            color: white;
            padding: 2rem;
            text-align: right;
        }

        .title-badge { 
            font-size: 10px; 
            font-weight: 700; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            color: #d8b4fe; 
            margin-bottom: 5px; 
        }
        
        .main-title {
            font-size: 24px; 
            line-height: 1.1; 
            font-weight: 900; 
            text-transform: uppercase; 
            margin-bottom: 15px;
            color: white;
        }

        .gradient-text {
            color: #e9d5ff; /* Fallback color for gradient text */
        }

        .info-pill {
            background-color: rgba(0,0,0,0.3); 
            padding: 8px 15px; 
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-block;
            font-size: 10px;
        }

        /* --- SLOGAN BAR --- */
        .slogan-bar {
            background-color: #f3f4f6; 
            padding: 10px;
            text-align: center; 
            border-bottom: 1px solid #e5e7eb;
        }
        .slogan-text {
            color: #7b1fa2; 
            font-size: 14px; 
            font-style: italic; 
            font-weight: 600;
            margin: 0;
        }

        /* --- CONTENT AREA --- */
        .content-padding { padding: 40px; }

        /* Grid Header (Simulado con tabla) */
        .grid-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #f3e8ff;
        }
        .grid-header-table th {
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            padding: 5px 10px;
        }

        /* --- PERIODOS --- */
        .period-section { 
            margin-bottom: 25px; 
            page-break-inside: avoid; 
        }
        
        .period-header { 
            margin-bottom: 10px; 
            border-bottom: 1px solid #f3e8ff;
            padding-bottom: 5px;
        }
        
        .period-number {
            display: inline-block;
            height: 20px; width: 20px; 
            background-color: #f3e8ff; 
            color: #7b1fa2;
            text-align: center; 
            border-radius: 4px;
            font-weight: 700; 
            font-size: 12px;
            line-height: 20px;
            margin-right: 8px;
        }
        
        .period-title { 
            color: #7b1fa2; 
            font-size: 14px; 
            text-transform: uppercase; 
            font-weight: 700; 
        }

        /* Course Row (Tabla) */
        .modules-table {
            width: 100%;
            border-collapse: collapse;
        }
        .modules-table td {
            padding: 6px 8px;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: bottom;
        }
        .modules-table tr:nth-child(even) {
            background-color: #faf5ff; /* Alternating row color */
        }

        .col-code { 
            width: 15%; 
            font-family: monospace; 
            font-weight: 600; 
            color: #374151; 
        }
        .col-desc { 
            width: 45%; 
            color: #111827; 
            font-weight: 600;
        }
        .col-credits { 
            width: 10%; 
            text-align: center; 
            font-weight: 700; 
            color: #4b5563; 
        }
        .col-prereq { 
            width: 30%; 
            text-align: right; 
            font-size: 9px; 
            color: #9ca3af; 
            font-family: monospace; 
        }

        .dotted-leader {
            border-bottom: 1px dotted #cbd5e1;
            display: inline-block;
            width: 20px; /* Placeholder width, DomPDF struggles with flex-grow dots */
            margin-left: 5px;
        }

        /* Subtotal */
        .subtotal-row {
            text-align: right;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #e5e7eb;
            margin-right: 25%;
        }
        .subtotal-label {
            font-size: 9px; 
            text-transform: uppercase; 
            color: #9ca3af; 
            margin-right: 10px; 
            font-weight: 700;
        }
        .subtotal-value {
            background-color: #f3f4f6; 
            color: #1f2937; 
            padding: 2px 8px; 
            border-radius: 4px;
            font-size: 11px; 
            font-weight: 700; 
            border: 1px solid #e5e7eb;
        }

        /* Summary Box */
        .summary-box {
            margin-top: 3rem; 
            padding: 1.5rem; 
            background-color: #faf5ff; 
            border-radius: 8px;
            border: 1px solid #f3e8ff; 
        }
        .summary-table { width: 100%; }
        .summary-total { font-size: 24px; font-weight: 900; color: #1f2937; }
        .summary-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #9ca3af; }

        /* Footer */
        .main-footer {
            margin-top: 4rem; 
            border-top: 2px solid #f3e8ff; 
            padding-top: 2rem;
            text-align: center;
            color: #9ca3af; 
            font-size: 10px;
        }
        .footer-table { width: 100%; }
        .footer-left { text-align: left; }
        .footer-right { text-align: right; }

    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header Section -->
        <header>
            <table class="header-table">
                <tr>
                    <!-- Logo Area -->
                    <td class="logo-area">
                        <div>
                            <h1 class="logo-siglas font-heading">
                                {{ strtoupper(substr(config('app.name', 'SGA'), 0, 3)) }}
                            </h1>
                            <div class="logo-divider"></div>
                            <div class="logo-subtitle">
                                Centro Educativo<br>Universitario
                            </div>
                            <p class="logo-english">{{ config('app.name', 'Sistema de Gestión') }}</p>
                        </div>
                    </td>

                    <!-- Title Area -->
                    <td class="title-area">
                        <div>
                            <p class="title-badge font-heading">Plan de Estudios Oficial</p>
                            <h2 class="main-title font-heading">
                                {{ $career->name }}<br>
                                <span class="gradient-text" style="font-size: 16px; font-weight: normal;">
                                    {{ $career->program_type === 'degree' ? 'GRADO ACADÉMICO' : 'CARRERA TÉCNICA' }}
                                </span>
                            </h2>
                            
                            <div class="info-pill">
                                <span style="color: #4ade80; font-weight: bold;">●</span> Clave: <strong>{{ $career->code }}</strong>
                                &nbsp;|&nbsp;
                                <span style="color: #d8b4fe;">📅</span> Generado: <strong>{{ $generatedAt }}</strong>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </header>

        <!-- Slogan Bar -->
        <div class="slogan-bar">
            <p class="slogan-text font-heading">"Excelencia académica para el futuro"</p>
        </div>

        <!-- Main Content Container -->
        <div class="content-padding">
            
            <!-- Table Headers -->
            <table class="grid-header-table">
                <thead>
                    <tr>
                        <th width="15%">Código</th>
                        <th width="45%">Descripción del Curso</th>
                        <th width="10%" style="text-align: center;">Créditos</th>
                        <th width="30%" style="text-align: right;">Prerrequisitos</th>
                    </tr>
                </thead>
            </table>

            <!-- Content Grid -->
            <div>
                @php $totalAccumulated = 0; @endphp

                @foreach($modulesByPeriod as $period => $modules)
                    @php $periodCredits = 0; @endphp
                    
                    <div class="period-section">
                        <div class="period-header">
                            <span class="period-number">{{ $period }}</span>
                            <span class="period-title font-heading">Cuatrimestre {{ $period }}</span>
                        </div>
                        
                        <table class="modules-table">
                            <tbody>
                                @foreach($modules as $module)
                                    @php $periodCredits += $module->credits; @endphp
                                    <tr>
                                        <td class="col-code">{{ $module->code }}</td>
                                        <td class="col-desc">
                                            {{ $module->name }}
                                            @if($module->is_elective)
                                                <span style="color: #d97706; font-size: 9px; font-weight: bold; margin-left: 5px;">(ELECTIVA)</span>
                                            @endif
                                        </td>
                                        <td class="col-credits">{{ $module->credits }}</td>
                                        <td class="col-prereq">
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

                        <!-- Subtotal Footer -->
                        <div class="subtotal-row">
                            <span class="subtotal-label">Créditos del Periodo</span>
                            <span class="subtotal-value">{{ $periodCredits }}</span>
                        </div>
                        
                        @php $totalAccumulated += $periodCredits; @endphp
                    </div>
                @endforeach
            </div>

            <!-- Summary Box -->
            <div class="summary-box">
                <table class="summary-table">
                    <tr>
                        <td style="text-align: left;">
                            <h4 class="text-itla-purple font-bold text-lg" style="margin: 0; color: #7b1fa2; font-size: 18px;">Resumen Académico</h4>
                            <p class="text-sm text-gray-500" style="margin: 0; font-size: 12px; color: #6b7280;">Total acumulado de la carrera</p>
                        </td>
                        <td style="text-align: right;">
                            <span class="summary-total">{{ $totalAccumulated }}</span><br>
                            <span class="summary-label">Créditos Totales</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="main-footer">
                <table class="footer-table">
                    <tr>
                        <td class="footer-left">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Institución Educativa') }}
                        </td>
                        <td class="footer-right">
                            Documento generado automáticamente | SGA System v1.0
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

    {{-- Script PHP para numeración de páginas --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = 500;
            $y = 800;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = null;
            $size = 9;
            $color = array(0.5, 0.5, 0.5);
            $word_space = 0.0;
            $char_space = 0.0;
            $angle = 0.0;
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

</body>
</html>