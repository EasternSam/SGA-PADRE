<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pensum Académico - {{ $career->code }}</title>
    
    {{-- Carga de Fuentes de Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">

    <style>
        /* CONFIGURACIÓN BÁSICA */
        @page {
            margin: 0cm;
        }
        body {
            /* Intentamos usar las fuentes descargadas, fallback a sans-serif */
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            background-color: #f9fafb; /* Gray 50 */
            /* Margen superior suficiente para el header fijo (180px) + espacio (40px) */
            margin-top: 220px; 
            margin-bottom: 60px;
            color: #1e293b; /* Slate 800 */
        }

        /* --- FUENTES ESPECÍFICAS --- */
        h1, h2, h3, .font-heading, .main-title, .period-title, .doc-label, .career-type-badge {
            font-family: 'Montserrat', sans-serif;
        }

        /* --- DECORATIVE BACKGROUND --- */
        .bg-pattern {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;
            background-color: #f9fafb;
        }

        /* --- CONTAINER PRINCIPAL --- */
        .container {
            position: relative;
            z-index: 10;
            background-color: white;
            width: 90%; 
            margin: 0 auto;
            overflow: hidden;
        }

        /* --- HEADER FIJO --- */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 180px; /* Altura fija reducida para ser más estético */
            background-color: white;
            z-index: 1000;
            /* El borde se aplica en las celdas para que pegue perfecto */
        }

        .header-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        /* Lado Izquierdo (Logo) */
        .header-left {
            width: 35%;
            background-color: #7b1fa2; /* Morado base */
            background: linear-gradient(135deg, #7b1fa2 0%, #6a1b9a 100%);
            vertical-align: middle;
            text-align: center;
            padding: 0; /* Sin padding para que el borde pegue */
            border-bottom: 5px solid #4a148c; /* Borde inferior integrado */
        }

        /* Lado Derecho (Información) */
        .header-right {
            width: 65%;
            background-color: #111827; /* Fondo oscuro */
            color: white;
            vertical-align: middle;
            padding: 20px 40px;
            text-align: right;
            border-left: 1px solid rgba(255,255,255,0.1);
            border-bottom: 5px solid #6b21a8; /* Borde inferior integrado - PEGADO */
        }

        /* Imagen Logo */
        .header-logo-container {
            padding: 20px;
            display: block;
        }
        
        .header-logo-img {
            max-width: 90%;
            max-height: 120px;
            object-fit: contain;
            /* Filtro blanco para asegurar visibilidad sobre morado */
            filter: brightness(0) invert(1); 
        }

        /* Textos Header Derecho */
        .doc-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #d8b4fe;
            margin-bottom: 8px;
            display: block;
        }

        .main-title {
            font-size: 22px; 
            line-height: 1.1; 
            font-weight: 800; 
            text-transform: uppercase; 
            margin: 0 0 10px 0;
            color: white;
        }

        .career-type-badge {
            background-color: rgba(255,255,255,0.15);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            text-transform: uppercase;
        }

        .info-row {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 5px;
            font-family: 'Inter', sans-serif;
        }
        .info-row strong {
            color: white;
            font-weight: 600;
        }

        /* --- CONTENT AREA --- */
        .content-padding { padding: 0 40px; }

        /* Grid Header (Estático) */
        .grid-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #6b21a8;
        }
        .grid-header-table th {
            text-align: left;
            font-size: 9px;
            font-weight: 800;
            color: #4b5563;
            text-transform: uppercase;
            padding: 8px 10px;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
        }

        /* --- PERIODOS --- */
        .period-section { 
            margin-bottom: 25px; 
            page-break-inside: avoid; 
        }
        
        .period-header { 
            margin-bottom: 8px; 
            border-bottom: 1px solid #e9d5ff; 
            padding-bottom: 5px;
            display: table;
            width: 100%;
        }
        
        .period-number {
            display: table-cell;
            vertical-align: middle;
            height: 24px; width: 24px; 
            background-color: #7b1fa2; 
            color: white;
            text-align: center; 
            border-radius: 4px;
            font-weight: 800; 
            font-size: 12px;
            line-height: 24px;
            margin-right: 8px;
        }
        
        .period-title-container {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }

        .period-title { 
            color: #4a148c; 
            font-size: 14px; 
            text-transform: uppercase; 
            font-weight: 800; 
            letter-spacing: 0.5px;
        }

        /* Tabla de Materias */
        .modules-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; 
        }
        .modules-table td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .modules-table tr:nth-child(even) {
            background-color: #faf5ff;
        }

        /* Columnas */
        .col-code { 
            width: 15%; 
            font-family: 'Courier New', monospace; 
            font-weight: 700; 
            color: #334155; 
        }
        .col-desc { 
            width: 45%; 
            color: #0f172a; 
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
            color: #64748b; 
            font-style: italic;
        }

        .elective-tag {
            color: #d97706; 
            font-size: 8px; 
            font-weight: 800; 
            margin-left: 5px; 
            text-transform: uppercase;
            background-color: #fffbeb;
            padding: 2px 4px;
            border-radius: 2px;
            border: 1px solid #fcd34d;
        }

        /* Subtotal */
        .subtotal-row {
            text-align: right;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #cbd5e1;
            margin-right: 25%; 
        }
        .subtotal-label {
            font-size: 9px; 
            text-transform: uppercase; 
            color: #64748b; 
            margin-right: 10px; 
            font-weight: 700;
        }
        .subtotal-value {
            background-color: #f3f4f6; 
            color: #1f2937; 
            padding: 2px 8px; 
            border-radius: 4px;
            font-size: 10px; 
            font-weight: 800; 
            border: 1px solid #e2e8f0;
        }

        /* Summary Box */
        .summary-box {
            margin-top: 30px; 
            padding: 15px; 
            background-color: #faf5ff; 
            border-radius: 8px;
            border: 1px solid #e9d5ff; 
            page-break-inside: avoid;
        }
        .summary-table { width: 100%; }
        .summary-total { font-size: 24px; font-weight: 900; color: #4a148c; line-height: 1; font-family: 'Montserrat', sans-serif; }
        .summary-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #7b1fa2; letter-spacing: 1px; }

        /* Footer */
        .main-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 10px 40px;
            font-size: 9px;
            color: #94a3b8;
        }
        .footer-table { width: 100%; }
        .footer-left { text-align: left; }
        .footer-right { text-align: right; }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(123, 31, 162, 0.03);
            z-index: -1000;
            font-weight: 900;
            white-space: nowrap;
            pointer-events: none;
            font-family: 'Montserrat', sans-serif;
        }

    </style>
</head>
<body>

    <!-- MARCA DE AGUA -->
    <div class="watermark">{{ $career->code }}</div>

    <!-- HEADER (DISEÑO PERFECCIONADO) -->
    <header>
        <table class="header-table">
            <tr>
                <!-- Lado Izquierdo: LOGO -->
                <td class="header-left">
                    <div class="header-logo-container">
                        <!-- Logo Centuu reemplaza el texto -->
                        <img src="{{ public_path('centuu.png') }}" class="header-logo-img" alt="Logo Centuu">
                    </div>
                </td>

                <!-- Lado Derecho: Información Clara y Contrastada -->
                <td class="header-right">
                    <span class="doc-label">Plan de Estudios Oficial</span>
                    
                    <h2 class="main-title">
                        {{ $career->name }}
                    </h2>
                    
                    <div class="career-type-badge">
                        {{ $career->program_type === 'degree' ? 'GRADO ACADÉMICO' : 'CARRERA TÉCNICA' }}
                    </div>
                    
                    <div class="info-row">
                        Clave: <strong>{{ $career->code }}</strong> &nbsp;|&nbsp; 
                        Generado: <strong>{{ $generatedAt }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <!-- FOOTER -->
    <footer class="main-footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Institución Educativa') }}
                </td>
                <td class="footer-right">
                    Documento Oficial | {{ config('app.name', 'SGA System') }} v1.0
                </td>
            </tr>
        </table>
    </footer>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <div class="content-padding">
            
            <!-- Encabezados de Tabla (Estáticos) -->
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

            <!-- Grid de Contenido -->
            <div>
                @php $totalAccumulated = 0; @endphp

                @foreach($modulesByPeriod as $period => $modules)
                    @php $periodCredits = 0; @endphp
                    
                    <div class="period-section">
                        <!-- Título del Periodo -->
                        <div class="period-header">
                            <div class="period-number">{{ $period }}</div>
                            <div class="period-title-container">
                                <span class="period-title">Cuatrimestre {{ $period }}</span>
                            </div>
                        </div>
                        
                        <!-- Tabla de Materias del Periodo -->
                        <table class="modules-table">
                            <tbody>
                                @foreach($modules as $module)
                                    @php $periodCredits += $module->credits; @endphp
                                    <tr>
                                        <td class="col-code">{{ $module->code }}</td>
                                        <td class="col-desc">
                                            {{ $module->name }}
                                            @if($module->is_elective)
                                                <span class="elective-tag">Electiva</span>
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

                        <!-- Subtotal del Periodo -->
                        <div class="subtotal-row">
                            <span class="subtotal-label">Créditos del Periodo</span>
                            <span class="subtotal-value">{{ $periodCredits }}</span>
                        </div>
                        
                        @php $totalAccumulated += $periodCredits; @endphp
                    </div>
                @endforeach
            </div>

            <!-- Resumen Total -->
            <div class="summary-box">
                <table class="summary-table">
                    <tr>
                        <td style="text-align: left; vertical-align: middle;">
                            <h4 style="margin: 0; color: #4a148c; font-size: 16px; font-weight: 800; text-transform: uppercase;">Resumen Académico</h4>
                            <p style="margin: 5px 0 0 0; font-size: 11px; color: #64748b;">Total acumulado de créditos para la carrera</p>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            <span class="summary-total">{{ $totalAccumulated }}</span><br>
                            <span class="summary-label">Créditos Totales</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 40px; color: #cbd5e1; font-size: 9px; text-transform: uppercase; letter-spacing: 2px;">
                *** Fin del Pensum Académico ***
            </div>

        </div>
    </div>

    {{-- Numeración de Páginas --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = 520;
            $y = 810; // Posición Y cerca del footer
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