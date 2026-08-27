<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletines - {{ $section->gradeLevel?->short_name }} {{ $section->name }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 9pt; color: #1a1a1a; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-logo { width: 80px; vertical-align: top; padding-right: 15px; }
        .header-logo img { width: 70px; height: auto; }
        .header-center { text-align: center; vertical-align: middle; }
        .header-center h1 { font-size: 14pt; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .header-center h2 { font-size: 10pt; color: #374151; margin: 4px 0; }
        .header-center p { font-size: 8pt; color: #6b7280; margin: 2px 0; }

        .minerd-bar { background: #1e3a8a; color: white; text-align: center; padding: 5px; font-size: 10pt; font-weight: bold; margin: 8px 0; }

        .student-info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .student-info td { padding: 3px 8px; font-size: 9pt; }
        .student-info .label { font-weight: bold; color: #374151; width: 20%; background: #f3f4f6; }
        .student-info .value { border-bottom: 1px dotted #d1d5db; }

        .grades-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .grades-table th { background: #1e3a8a; color: white; padding: 5px 3px; font-size: 7pt; text-transform: uppercase; text-align: center; border: 1px solid #1e3a8a; }
        .grades-table td { border: 1px solid #d1d5db; padding: 4px 3px; font-size: 8pt; text-align: center; }
        .grades-table td.subject-name { text-align: left; font-weight: bold; padding-left: 6px; }
        .grades-table tr:nth-child(even) td { background: #f9fafb; }
        .grade-cell { font-weight: bold; }
        .grade-high { color: #166534; } .grade-mid { color: #92400e; } .grade-low { color: #991b1b; }
        .avg-row td { background: #eef2ff !important; font-weight: bold; border-top: 2px solid #1e3a8a; }
        .performance-level { font-size: 7pt; padding: 1px 4px; border-radius: 3px; font-weight: bold; }
        .level-A { background: #dcfce7; color: #166534; } .level-B { background: #dbeafe; color: #1e40af; }
        .level-C { background: #fef3c7; color: #92400e; } .level-D { background: #fee2e2; color: #991b1b; }

        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .attendance-table th { background: #1e3a8a; color: white; padding: 4px; font-size: 7pt; border: 1px solid #1e3a8a; }
        .attendance-table td { border: 1px solid #d1d5db; padding: 4px; font-size: 8pt; text-align: center; }

        .observations { margin-top: 8px; border: 1px solid #d1d5db; padding: 8px; min-height: 35px; font-size: 8pt; }
        .observations .label { font-weight: bold; color: #374151; margin-bottom: 3px; }

        .signatures-table { width: 100%; margin-top: 15px; }
        .signatures-table td { text-align: center; padding-top: 25px; width: 33%; }
        .signatures-table .line { border-top: 1px solid #374151; padding-top: 4px; font-size: 7pt; color: #6b7280; }

        .legend { margin-top: 6px; padding: 4px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 7pt; }
        .legend table { width: 100%; } .legend td { padding: 1px 4px; }

        .page-break { page-break-after: always; }
        .meta-info { position: fixed; bottom: 0; left: 0; right: 0; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 3px; }
        .page-number { float: right; }
        .page-number:before { content: "Página " counter(page) " de " counter(pages); }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('centuu.png');
        if ($schoolConfig?->logo_path && file_exists(public_path('storage/' . $schoolConfig->logo_path))) {
            $logoPath = public_path('storage/' . $schoolConfig->logo_path);
        }
        $base64Logo = file_exists($logoPath)
            ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath))
            : '';
    @endphp

    @foreach($studentsData as $index => $sd)
        @php
            $student = $sd['student'];
            $grades = $sd['grades'];
            $attendanceStats = $sd['attendanceStats'];
            $reportCard = $sd['reportCard'];
            $allGrades = $sd['allGrades'];
        @endphp

        {{-- HEADER --}}
        <table class="header-table">
            <tr>
                <td class="header-logo">@if($base64Logo)<img src="{{ $base64Logo }}" alt="Logo">@endif</td>
                <td class="header-center">
                    <h1>{{ $schoolConfig?->school_name ?? 'Centro Educativo' }}</h1>
                    <h2>Boletín de Calificaciones</h2>
                    <p>Código MINERD: {{ $schoolConfig?->minerd_code ?? 'N/A' }}</p>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>

        <div class="minerd-bar">BOLETÍN — {{ $period->name }} — {{ $activeYear->name }}</div>

        <table class="student-info">
            <tr>
                <td class="label">Estudiante:</td><td class="value">{{ $student->full_name }}</td>
                <td class="label">Grado:</td><td class="value">{{ $section->gradeLevel?->name }} {{ $section->name }}</td>
            </tr>
        </table>

        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 40%; text-align: left;">Asignatura</th>
                    @foreach($allPeriods as $p)<th>P{{ $p->number }}</th>@endforeach
                    <th>Prom.</th>
                    <th style="width: 12%;">Nivel</th>
                </tr>
            </thead>
            <tbody>
                @php $totalScores = 0; $countScores = 0; @endphp
                @foreach($sectionSubjects as $ss)
                    <tr>
                        <td class="subject-name">{{ $ss->subject?->name }}</td>
                        @php $periodScores = []; @endphp
                        @foreach($allPeriods as $p)
                            @php $score = isset($allGrades[$p->id]) ? ($allGrades[$p->id][$ss->id] ?? null) : null; $periodScores[] = $score; @endphp
                            <td class="grade-cell {{ $score !== null ? ($score >= 90 ? 'grade-high' : ($score >= 70 ? 'grade-mid' : 'grade-low')) : '' }}">
                                {{ $score !== null ? number_format($score, 0) : '—' }}
                            </td>
                        @endforeach
                        @php
                            $valid = array_filter($periodScores, fn($s) => $s !== null);
                            $avg = count($valid) > 0 ? array_sum($valid) / count($valid) : null;
                            if ($avg !== null) { $totalScores += $avg; $countScores++; }
                            $level = $avg !== null ? \App\Models\StudentGrade::getPerformanceLevel($avg) : null;
                        @endphp
                        <td class="grade-cell">{{ $avg !== null ? number_format($avg, 0) : '—' }}</td>
                        <td>@if($level)<span class="performance-level level-{{ $level['letter'] }}">{{ $level['letter'] }}</span>@else —@endif</td>
                    </tr>
                @endforeach
                <tr class="avg-row">
                    <td style="text-align: right;">PROMEDIO GENERAL</td>
                    @foreach($allPeriods as $p)<td></td>@endforeach
                    <td>{{ $countScores > 0 ? number_format($totalScores / $countScores, 0) : '—' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <table class="attendance-table">
            <tr><th>Días</th><th>Presencias</th><th>Ausencias</th><th>Tardanzas</th><th>% Asistencia</th></tr>
            <tr>
                <td>{{ $attendanceStats['total_days'] }}</td>
                <td>{{ $attendanceStats['present'] }}</td>
                <td>{{ $attendanceStats['absent'] }}</td>
                <td>{{ $attendanceStats['late'] }}</td>
                <td>{{ $attendanceStats['total_days'] > 0 ? number_format(($attendanceStats['present'] / $attendanceStats['total_days']) * 100, 1) : 0 }}%</td>
            </tr>
        </table>

        <div class="observations">
            <div class="label">Observaciones:</div>
            {{ $reportCard?->teacher_observations ?? '' }}
        </div>

        <div class="legend">
            <strong>Escala:</strong> A = Logrado (90-100) | B = Avanzado (80-89) | C = Inicial (70-79) | D = Inicio (0-69)
        </div>

        <table class="signatures-table">
            <tr>
                <td><div class="line">Docente</div></td>
                <td><div class="line">Director/a</div></td>
                <td><div class="line">Padre/Tutor</div></td>
            </tr>
        </table>

        @if(!$loop->last)<div class="page-break"></div>@endif
    @endforeach

    <div class="meta-info">
        {{ $schoolConfig?->school_name ?? '' }} | Generado: {{ now()->format('d/m/Y h:i A') }}
        <span class="page-number"></span>
    </div>
</body>
</html>
