<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pensum Académico - {{ $career->code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5; /* Indigo 600 */
            padding-bottom: 10px;
        }
        
        .institution-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1f2937; /* Gray 800 */
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 14px;
            font-weight: bold;
            color: #4b5563; /* Gray 600 */
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .career-info {
            background-color: #f3f4f6; /* Gray 100 */
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .career-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827; /* Gray 900 */
            margin-bottom: 5px;
        }

        .career-meta {
            font-size: 11px;
            color: #6b7280; /* Gray 500 */
        }

        .meta-item {
            display: inline-block;
            margin-right: 15px;
        }

        .meta-label {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Tabla de Pensum */
        .curriculum-container {
            width: 100%;
        }

        .period-header {
            background-color: #4f46e5; /* Indigo 600 */
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            margin-top: 15px;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            background-color: white;
        }

        th, td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #e5e7eb; /* Gray 200 */
        }

        th {
            background-color: #f9fafb; /* Gray 50 */
            color: #374151; /* Gray 700 */
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9fafb; /* Gray 50 */
        }

        .col-code { width: 15%; font-weight: bold; font-family: monospace; }
        .col-name { width: 50%; }
        .col-credits { width: 10%; text-align: center; }
        .col-prereq { width: 25%; font-size: 10px; color: #6b7280; }

        .total-credits-row {
            background-color: #eef2ff; /* Indigo 50 */
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af; /* Gray 400 */
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="institution-name">Centro Educativo Universitario</div>
        <div class="document-title">Pensum Académico Oficial</div>
    </div>

    <div class="career-info">
        <div class="career-name">{{ $career->name }}</div>
        <div class="career-meta">
            <span class="meta-item"><span class="meta-label">Código:</span> {{ $career->code }}</span>
            <span class="meta-item"><span class="meta-label">Créditos Totales:</span> {{ $career->total_credits }}</span>
            <span class="meta-item"><span class="meta-label">Duración:</span> {{ $career->duration_periods }} Periodos</span>
        </div>
    </div>

    <div class="curriculum-container">
        @php $accumulatedCredits = 0; @endphp

        @foreach($modulesByPeriod as $period => $modules)
            <div class="period-header">
                Cuatrimestre {{ $period }}
            </div>
            <table>
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
                    <tr class="total-credits-row">
                        <td colspan="2">Total Créditos Cuatrimestre {{ $period }}</td>
                        <td class="col-credits">{{ $periodCredits }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            @php $accumulatedCredits += $periodCredits; @endphp
        @endforeach
    </div>

    <div class="footer">
        Documento generado el {{ $generatedAt }} | Página <span class="page-number"></span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $x = 520;
            $y = 820;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = null;
            $size = 9;
            $color = array(0.5, 0.5, 0.5);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>