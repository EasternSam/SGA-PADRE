<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pensum Académico - {{ $career->code }}</title>
    <style>
        /* --- FUENTES Y GENERAL --- */
        @page {
            margin: 0cm; /* Márgenes manejados internamente */
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin-top: 3.5cm; /* Espacio para el header */
            margin-bottom: 2cm; /* Espacio para el footer */
            margin-left: 1.5cm;
            margin-right: 1.5cm;
            background-color: #ffffff;
            color: #334155; /* Slate 700 */
            font-size: 11px;
            line-height: 1.5;
        }

        /* --- MARCA DE AGUA --- */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 130px;
            color: rgba(203, 213, 225, 0.12); /* Slate 300 muy transparente */
            z-index: -1000;
            font-weight: 800;
            white-space: nowrap;
            text-align: center;
            letter-spacing: 5px;
        }

        /* --- HEADER FIJO --- */
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); /* Slate 900 to 800 */
            color: white;
            z-index: 1000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            padding: 0.8cm 1.5cm;
            display: flex; /* Nota: Flexbox limitado en DomPDF, usamos float */
        }

        .header-left {
            float: left;
            width: 70%;
        }

        .header-right {
            float: right;
            width: 30%;
            text-align: right;
            padding-top: 5px;
        }

        .institution-name {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 4px;
            color: #f8fafc; /* Slate 50 */
        }

        .system-name {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #94a3b8; /* Slate 400 */
            display: block;
        }

        .doc-type {
            font-size: 12px;
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- FOOTER FIJO --- */
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            background-color: #f8fafc; /* Slate 50 */
            border-top: 1px solid #e2e8f0; /* Slate 200 */
            color: #64748b; /* Slate 500 */
            padding: 0 1.5cm;
            line-height: 1.5cm;
            font-size: 9px;
        }

        .footer-content {
            width: 100%;
        }

        .footer-left {
            float: left;
            width: 40%;
        }
        
        .footer-center {
            float: left;
            width: 20%;
            text-align: center;
        }

        .footer-right {
            float: right;
            width: 40%;
            text-align: right;
        }

        /* --- TARJETA DE CARRERA --- */
        .career-card {
            margin-bottom: 30px;
            position: relative;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }

        .career-title {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a; /* Slate 900 */
            margin-bottom: 15px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td {
            padding: 6px 0;
            vertical-align: top;
        }

        .label {
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b; /* Slate 500 */
            font-size: 9px;
            letter-spacing: 0.5px;
            width: 15%;
        }

        .value {
            color: #1e293b; /* Slate 800 */
            font-weight: 500;
            width: 35%;
        }

        .description {
            margin-top: 15px;
            font-size: 11px;
            color: #475569; /* Slate 600 */
            font-style: italic;
            border-left: 3px solid #3b82f6; /* Blue 500 */
            padding-left: 12px;
        }

        /* --- BLOQUES DE PERIODOS --- */
        .period-container {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .period-header {
            display: block;
            margin-bottom: 10px;
            border-bottom: 2px solid #cbd5e1; /* Slate 300 */
            padding-bottom: 5px;
        }

        .period-title {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b; /* Slate 800 */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f1f5f9; /* Slate 100 */
            padding: 4px 12px;
            border-radius: 4px;
            display: inline-block;
        }

        /* --- TABLAS --- */
        table.modules {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.modules th {
            text-align: left;
            text-transform: uppercase;
            font-weight: 700;
            color: #475569; /* Slate 600 */
            border-bottom: 1px solid #94a3b8; /* Slate 400 */
            padding: 8px 10px;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        table.modules td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0; /* Slate 200 */
            color: #334155; /* Slate 700 */
            vertical-align: middle;
        }

        table.modules tr:last-child td {
            border-bottom: none;
        }

        /* Colores Alternos Sutiles */
        table.modules tr:nth-child(even) {
            background-color: #f8fafc; /* Slate 50 */
        }

        /* Columnas */
        .col-code { 
            width: 12%; 
            font-weight: 700; 
            font-family: 'Courier New', monospace;
            color: #0f172a;
        }
        .col-name { width: 48%; font-weight: 600; }
        .col-credits { width: 10%; text-align: center; font-weight: 700; }
        .col-prereq { 
            width: 30%; 
            color: #64748b; 
            font-style: italic; 
            font-size: 9px;
        }

        /* Badge Electiva */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            margin-left: 8px;
            vertical-align: middle;
            letter-spacing: 0.5px;
        }

        .badge-elective {
            background-color: #fff7ed; /* Orange 50 */
            color: #c2410c; /* Orange 700 */
            border: 1px solid #fed7aa; /* Orange 200 */
        }

        /* Resumen Créditos */
        .period-summary {
            text-align: right;
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            padding: 8px 10px;
            background-color: #f1f5f9; /* Slate 100 */
            border-radius: 0 0 4px 4px;
            margin-top: 0;
        }

        /* Paginación */
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

    <!-- Header Fijo -->
    <header>
        <div class="header-content">
            <div class="header-left">
                <span class="institution-name">Centro de Tecnología Universal</span>
                <span class="system-name">{{ config('app.name', 'Laravel') }} Integrado</span>
            </div>
            <div class="header-right">
                <span class="doc-type">Pensum Oficial</span>
            </div>
        </div>
    </header>

    <!-- Footer Fijo -->
    <footer>
        <div class="footer-content">
            <div class="footer-left">
                Generado el {{ $generatedAt }}
            </div>
            <div class="footer-center">
                <!-- Espacio para logo pequeño si se desea -->
                {{ config('app.name', 'Laravel') }} v1.0
            </div>
            <div class="footer-right">
                Página <span class="page-number"></span>
            </div>
        </div>
    </footer>

    <!-- Marca de Agua -->
    <div class="watermark">{{ $career->code }}</div>

    <!-- Contenido Principal -->
    <main>
        
        <!-- Tarjeta de Carrera -->
        <div class="career-card">
            <div class="career-title">{{ $career->name }}</div>
            
            <table class="meta-grid">
                <tr>
                    <td class="label">Código:</td>
                    <td class="value">{{ $career->code }}</td>
                    <td class="label">Modalidad:</td>
                    <td class="value">{{ $career->program_type === 'degree' ? 'Grado Académico' : 'Técnico Superior' }}</td>
                </tr>
                <tr>
                    <td class="label">Total Créditos:</td>
                    <td class="value">{{ $career->total_credits }}</td>
                    <td class="label">Duración:</td>
                    <td class="value">{{ $career->duration_periods }} Cuatrimestres</td>
                </tr>
            </table>

            @if($career->description)
                <div class="description">
                    {{ $career->description }}
                </div>
            @endif
        </div>

        @php $totalAccumulated = 0; @endphp

        <!-- Iteración de Periodos -->
        @foreach($modulesByPeriod as $period => $modules)
            <div class="period-container">
                <div class="period-header">
                    <span class="period-title">Cuatrimestre {{ $period }}</span>
                </div>

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
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="period-summary">
                    Total Créditos: {{ $periodCredits }}
                </div>
                @php $totalAccumulated += $periodCredits; @endphp
            </div>
        @endforeach

        <!-- Fin del Documento -->
        <div style="margin-top: 50px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px dashed #cbd5e1; padding-top: 20px;">
            <p style="text-transform: uppercase; letter-spacing: 1px; font-weight: bold; margin-bottom: 5px;">*** Fin del Documento Oficial ***</p>
            <p>Este pensum está sujeto a cambios institucionales. Validez informativa.</p>
        </div>

    </main>

    {{-- Script PHP para numeración total de páginas (si es necesario con DomPDF avanzado) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $x = 520;
            $y = 820; // Ajustar según margen
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $font = null;
            $size = 9;
            $color = array(0.4, 0.45, 0.55); // Color Slate 500 aprox
            $word_space = 0.0; 
            $char_space = 0.0; 
            $angle = 0.0;
            // $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>