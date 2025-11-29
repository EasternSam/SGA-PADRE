<div>
    @if(empty($reportData))
        <div class="p-4 text-center text-gray-500">
            <p>Seleccione los filtros y haga clic en "Generar Reporte" para ver los resultados.</p>
        </div>
    @else
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reporte de Asistencia - {{ $reportData['schedule']->section_name }}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 10pt;
                    color: #333;
                    background: white; /* Asegurar fondo blanco */
                }
                /* Eliminamos margin global del body para que no afecte si está embebido, 
                   pero mantenemos @page para impresión */
                @page {
                    margin: 1.5cm;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .header h1 {
                    font-size: 18pt;
                    font-weight: bold;
                    color: #1e3a8a; /* Azul oscuro */
                    margin: 0;
                }
                .header h2 {
                    font-size: 14pt;
                    font-weight: normal;
                    margin: 5px 0;
                    color: #374151; /* Gris oscuro */
                }
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                    font-size: 9pt;
                }
                .info-table td {
                    padding: 6px 8px;
                    border: 1px solid #ddd;
                }
                .info-table .label {
                    font-weight: bold;
                    background-color: #f9fafb;
                    width: 15%; /* Ajustado para mejor distribución */
                }
                .attendance-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                    font-size: 8pt;
                }
                .attendance-table th, 
                .attendance-table td {
                    border: 1px solid #ddd;
                    padding: 5px;
                    text-align: center;
                }
                .attendance-table th {
                    background-color: #f9fafb;
                    font-weight: bold;
                }
                .student-name {
                    text-align: left;
                    width: 200px; /* Un poco más ancho */
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                /* Encabezados de fecha rotados */
                .date-header {
                    height: 80px;
                    width: 25px;
                    min-width: 25px;
                    max-width: 25px;
                    vertical-align: bottom;
                    padding: 5px;
                    position: relative;
                }
                .date-header > div {
                    transform: rotate(-90deg);
                    white-space: nowrap;
                    position: absolute;
                    left: 0;
                    bottom: 30px;
                    width: 80px; /* Debe coincidir con height de th */
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-end; /* Alinea el texto al borde inferior visual */
                    transform-origin: center left; /* Ajuste para la rotación */
                    bottom: 10px; left: 12px; /* Ajuste fino de posición */
                }

                /* Colores para estados */
                .status-P { background-color: #dcfce7; color: #166534; } /* Verde claro */
                .status-A { background-color: #fee2e2; color: #991b1b; } /* Rojo claro */
                .status-T { background-color: #fef3c7; color: #92400e; } /* Amarillo claro */
                .status-NA { background-color: #f3f4f6; color: #9ca3af; } /* Gris claro */
                .status-unknown { background-color: #e5e7eb; }

                .legend {
                    margin-top: 20px;
                    font-size: 9pt;
                }
                .legend-item {
                    display: inline-block;
                    margin-right: 15px;
                }
                .legend-box {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    border: 1px solid #ccc;
                    margin-right: 5px;
                    vertical-align: middle;
                }
                
                tr {
                    page-break-inside: avoid;
                }
            </style>
        </head>
        <body>
            
            <div class="container">
                <div class="header">
                    <h1>Reporte de Asistencia</h1>
                    <h2>
                        {{ $reportData['schedule']->module->course->name ?? 'Curso' }} - 
                        {{ $reportData['schedule']->module->name ?? 'Módulo' }}
                    </h2>
                </div>

                <table class="info-table">
                    <tr>
                        <td class="label">Sección</td>
                        <td>{{ $reportData['schedule']->section_name }}</td>
                        <td class="label">Profesor</td>
                        <td>{{ $reportData['schedule']->teacher->name ?? 'No asignado' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Horario</td>
                        <td>
                            {{ implode(', ', $reportData['schedule']->days_of_week ?? []) }} | 
                            {{ \Carbon\Carbon::parse($reportData['schedule']->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($reportData['schedule']->end_time)->format('h:i A') }}
                        </td>
                        <td class="label">Duración</td>
                        <td>
                            {{ \Carbon\Carbon::parse($reportData['schedule']->start_date)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($reportData['schedule']->end_date)->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Inscritos</td>
                        <td>{{ $reportData['students']->count() }}</td>
                        <td class="label">Días Reporte</td>
                        <td>{{ count($reportData['dates']) }}</td>
                    </tr>
                </table>

                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th class="student-name" style="text-align: left; vertical-align: bottom;">Estudiante</th>
                            @foreach ($reportData['dates'] as $dateStr)
                                <th class="date-header">
                                    {{-- Parseamos el string de fecha solo para visualización --}}
                                    <div>{{ \Carbon\Carbon::parse($dateStr)->format('d-M') }}</div>
                                </th>
                            @endforeach
                            <th style="width: 40px;" title="Total Presentes">P</th>
                            <th style="width: 40px;" title="Total Ausentes">A</th>
                            <th style="width: 40px;" title="Total Tardanzas">T</th>
                            <th style="width: 50px;" title="Porcentaje de Asistencia">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData['students'] as $student)
                            @php
                                $presentCount = 0;
                                $absentCount = 0;
                                $tardyCount = 0;
                                $totalRecorded = 0;
                            @endphp
                            <tr>
                                <td class="student-name">
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                </td>
                                
                                @foreach ($reportData['dates'] as $dateStr)
                                    @php
                                        // Acceso O(1) a la matriz en memoria
                                        $statusRaw = $reportData['matrix'][$student->id][$dateStr] ?? null;
                                        
                                        $statusChar = '-'; 
                                        $class = '';

                                        if ($statusRaw) {
                                            $totalRecorded++;
                                            // Normalizamos la comparación (ajusta los strings según tu BD real)
                                            if ($statusRaw == 'Presente' || $statusRaw == 'present') {
                                                $statusChar = 'P';
                                                $class = 'status-P';
                                                $presentCount++;
                                            } elseif ($statusRaw == 'Ausente' || $statusRaw == 'absent') {
                                                $statusChar = 'A';
                                                $class = 'status-A';
                                                $absentCount++;
                                            } elseif ($statusRaw == 'Tardanza' || $statusRaw == 'late' || $statusRaw == 'tardy') {
                                                $statusChar = 'T';
                                                $class = 'status-T';
                                                $tardyCount++;
                                            } elseif ($statusRaw == 'Justificado' || $statusRaw == 'excused') {
                                                $statusChar = 'J';
                                                $class = 'status-NA'; // Usamos gris para justificado por ahora
                                            } else {
                                                $statusChar = '?';
                                                $class = 'status-unknown';
                                            }
                                        } else {
                                            $class = 'status-NA'; // Sin registro ese día
                                        }
                                    @endphp
                                    <td class="{{ $class }}">{{ $statusChar }}</td>
                                @endforeach
                                
                                {{-- Totales por estudiante --}}
                                <td style="font-weight: bold;">{{ $presentCount }}</td>
                                <td style="font-weight: bold; color: #991b1b;">{{ $absentCount }}</td>
                                <td style="font-weight: bold; color: #92400e;">{{ $tardyCount }}</td>
                                
                                {{-- Porcentaje --}}
                                @php
                                    $percentage = $totalRecorded > 0 ? round(($presentCount / $totalRecorded) * 100) : 0;
                                    $colorPerc = $percentage < 70 ? 'color: red;' : 'color: green;';
                                @endphp
                                <td style="font-weight: bold; {{ $colorPerc }}">{{ $percentage }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($reportData['dates']) + 5 }}" style="text-align: center; padding: 20px;">
                                    No hay estudiantes inscritos en esta sección.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="legend">
                    <p>
                        <span class="legend-item"><span class="legend-box status-P">&nbsp;</span> P: Presente</span>
                        <span class="legend-item"><span class="legend-box status-A">&nbsp;</span> A: Ausente</span>
                        <span class="legend-item"><span class="legend-box status-T">&nbsp;</span> T: Tardanza</span>
                        <span class="legend-item"><span class="legend-box status-NA">&nbsp;</span> - : Sin Registro / Justificado</span>
                    </p>
                </div>
            </div>
        </body>
        </html>
    @endif
</div>