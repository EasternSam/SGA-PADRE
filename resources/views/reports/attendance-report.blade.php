<div>
    @if(empty($reportData))
        <div class="p-4 text-center text-gray-500">
            <p>Seleccione los filtros y haga clic en "Generar Reporte" para ver los resultados.</p>
        </div>
    @else
        <style>
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
                width: 15%;
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
                text-align: left !important;
                width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
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
                width: 80px; 
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                transform-origin: center left; 
                bottom: 10px; left: 12px;
            }
            .status-P { background-color: #dcfce7 !important; color: #166534; } 
            .status-A { background-color: #fee2e2 !important; color: #991b1b; } 
            .status-T { background-color: #fef3c7 !important; color: #92400e; } 
            .status-NA { background-color: #f3f4f6 !important; color: #9ca3af; } 
            .status-unknown { background-color: #e5e7eb !important; }

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
        </style>

        <div class="container pb-10">
            <h2 class="text-xl font-bold mb-4 uppercase text-gray-800">
                Resumen de Asistencia
            </h2>

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
                            <td class="student-name" style="text-transform: uppercase;">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </td>
                            
                            @foreach ($reportData['dates'] as $dateStr)
                                @php
                                    $statusRaw = $reportData['matrix'][$student->id][$dateStr] ?? null;
                                    
                                    $statusChar = '-'; 
                                    $class = '';

                                    if ($statusRaw) {
                                        $totalRecorded++;
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
                                            $class = 'status-NA';
                                        } else {
                                            $statusChar = '?';
                                            $class = 'status-unknown';
                                        }
                                    } else {
                                        $class = 'status-NA';
                                    }
                                @endphp
                                <td class="{{ $class }}">{{ $statusChar }}</td>
                            @endforeach
                            
                            <td style="font-weight: bold;">{{ $presentCount }}</td>
                            <td style="font-weight: bold; color: #991b1b;">{{ $absentCount }}</td>
                            <td style="font-weight: bold; color: #92400e;">{{ $tardyCount }}</td>
                            
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
            
            <div class="legend border-t mt-6 pt-4 text-gray-600">
                <p>
                    <span class="legend-item"><span class="legend-box status-P">&nbsp;</span> P: Presente</span>
                    <span class="legend-item"><span class="legend-box status-A">&nbsp;</span> A: Ausente</span>
                    <span class="legend-item"><span class="legend-box status-T">&nbsp;</span> T: Tardanza</span>
                    <span class="legend-item"><span class="legend-box status-NA">&nbsp;</span> - : Sin Registro / Justificado</span>
                </p>
            </div>
        </div>
    @endif
</div>