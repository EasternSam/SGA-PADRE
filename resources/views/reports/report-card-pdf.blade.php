<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín de Notas - {{ $student->full_name }}</title>
    <style>
        @page { margin: 1.5cm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-logo { width: 80px; vertical-align: top; padding-right: 15px; }
        .header-logo img { width: 70px; height: auto; }
        .header-center { text-align: center; vertical-align: middle; }
        .header-center h1 { font-size: 14pt; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .header-center h2 { font-size: 10pt; color: #374151; margin: 4px 0; }
        .header-center p { font-size: 8pt; color: #6b7280; margin: 2px 0; }
        .header-right { width: 80px; text-align: right; vertical-align: top; }

        .minerd-bar {
            background: linear-gradient(to right, #1e3a8a, #3b82f6);
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 10pt;
            font-weight: bold;
            margin: 8px 0;
            letter-spacing: 1px;
        }

        .student-info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .student-info td { padding: 4px 8px; font-size: 9pt; }
        .student-info .label { font-weight: bold; color: #374151; width: 25%; background: #f3f4f6; }
        .student-info .value { color: #1a1a1a; border-bottom: 1px dotted #d1d5db; }

        .grades-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .grades-table th {
            background: #1e3a8a;
            color: white;
            padding: 6px 4px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
            border: 1px solid #1e3a8a;
        }
        .grades-table td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            font-size: 8pt;
            text-align: center;
        }
        .grades-table td.subject-name { text-align: left; font-weight: bold; padding-left: 8px; }
        .grades-table tr:nth-child(even) td { background: #f9fafb; }
        .grades-table .area-header td {
            background: #e5e7eb;
            font-weight: bold;
            font-size: 8pt;
            text-align: left;
            padding-left: 6px;
            color: #374151;
            border-top: 2px solid #9ca3af;
        }
        .grade-cell { font-weight: bold; }
        .grade-high { color: #166534; }
        .grade-mid { color: #92400e; }
        .grade-low { color: #991b1b; }

        .performance-level {
            font-size: 7pt;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .level-A { background: #dcfce7; color: #166534; }
        .level-B { background: #dbeafe; color: #1e40af; }
        .level-C { background: #fef3c7; color: #92400e; }
        .level-D { background: #fee2e2; color: #991b1b; }

        .avg-row td { background: #eef2ff !important; font-weight: bold; border-top: 2px solid #1e3a8a; }

        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .attendance-table th { background: #1e3a8a; color: white; padding: 5px; font-size: 8pt; border: 1px solid #1e3a8a; }
        .attendance-table td { border: 1px solid #d1d5db; padding: 5px; font-size: 9pt; text-align: center; }

        .observations {
            margin-top: 10px;
            border: 1px solid #d1d5db;
            padding: 10px;
            min-height: 50px;
            font-size: 9pt;
        }
        .observations .label { font-weight: bold; color: #374151; margin-bottom: 4px; }

        .signatures-table { width: 100%; margin-top: 25px; border-collapse: collapse; }
        .signatures-table td { text-align: center; padding-top: 30px; width: 33%; }
        .signatures-table .line { border-top: 1px solid #374151; padding-top: 5px; font-size: 8pt; color: #6b7280; }

        .legend { margin-top: 10px; padding: 6px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 7pt; }
        .legend table { width: 100%; border-collapse: collapse; }
        .legend td { padding: 2px 6px; }

        .meta-info { position: fixed; bottom: 0; left: 0; right: 0; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 3px; }
        .page-number { float: right; }
        .page-number:before { content: "Página " counter(page) " de " counter(pages); }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @php
                    $logoPath = public_path('centuu.png');
                    if ($schoolConfig?->logo_path && file_exists(public_path('storage/' . $schoolConfig->logo_path))) {
                        $logoPath = public_path('storage/' . $schoolConfig->logo_path);
                    }
                    $base64Logo = file_exists($logoPath)
                        ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath))
                        : '';
                @endphp
                @if($base64Logo)
                    <img src="{{ $base64Logo }}" alt="Logo">
                @endif
            </td>
            <td class="header-center">
                <h1>{{ $schoolConfig?->school_name ?? 'Centro Educativo' }}</h1>
                <h2>Boletín de Calificaciones</h2>
                <p>{{ $schoolConfig?->address ?? '' }} | Tel: {{ $schoolConfig?->phone ?? '' }}</p>
                <p>Código MINERD: {{ $schoolConfig?->minerd_code ?? 'N/A' }} | RNC: {{ $schoolConfig?->rnc ?? 'N/A' }}</p>
            </td>
            <td class="header-right">
                @php
                    // Dominican Republic coat of arms could go here
                @endphp
            </td>
        </tr>
    </table>

    <div class="minerd-bar">
        BOLETÍN INFORMATIVO — {{ $period->name }} — Año Escolar {{ $activeYear->name }}
    </div>

    {{-- STUDENT INFO --}}
    <table class="student-info">
        <tr>
            <td class="label">Estudiante:</td>
            <td class="value">{{ $student->full_name }}</td>
            <td class="label">Matrícula:</td>
            <td class="value">{{ $student->student_id ?? $student->id }}</td>
        </tr>
        <tr>
            <td class="label">Grado:</td>
            <td class="value">{{ $section->gradeLevel?->name ?? '—' }}</td>
            <td class="label">Sección:</td>
            <td class="value">{{ $section->name }}</td>
        </tr>
        <tr>
            <td class="label">Período:</td>
            <td class="value">{{ $period->name }} ({{ $period->start_date?->format('d/m/Y') }} - {{ $period->end_date?->format('d/m/Y') }})</td>
            <td class="label">Año Escolar:</td>
            <td class="value">{{ $activeYear->name }}</td>
        </tr>
    </table>

    {{-- GRADES TABLE --}}
    <table class="grades-table">
        <thead>
            <tr>
                <th style="width: 35%; text-align: left;">Asignatura</th>
                @foreach($allPeriods as $p)
                    <th style="width: 10%;">P{{ $p->number }}</th>
                @endforeach
                <th style="width: 10%;">Prom.</th>
                <th style="width: 15%;">Nivel</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentArea = '';
                $totalScores = 0;
                $countScores = 0;
            @endphp
            @foreach($sectionSubjects as $ss)
                @php
                    $subjectArea = $ss->subject?->area ?? '';
                    $areaLabel = \App\Models\Subject::AREAS[$subjectArea] ?? $subjectArea;
                @endphp

                @if($subjectArea !== $currentArea)
                    @php $currentArea = $subjectArea; @endphp
                    <tr class="area-header">
                        <td colspan="{{ count($allPeriods) + 3 }}">{{ $areaLabel }}</td>
                    </tr>
                @endif

                <tr>
                    <td class="subject-name">{{ $ss->subject?->name }}</td>
                    @php
                        $periodScores = [];
                    @endphp
                    @foreach($allPeriods as $p)
                        @php
                            $score = isset($allGrades[$p->id]) ? ($allGrades[$p->id][$ss->id] ?? null) : null;
                            $periodScores[] = $score;
                        @endphp
                        <td class="grade-cell {{ $score !== null ? ($score >= 90 ? 'grade-high' : ($score >= 70 ? 'grade-mid' : 'grade-low')) : '' }}">
                            {{ $score !== null ? number_format($score, 0) : '—' }}
                        </td>
                    @endforeach
                    @php
                        $validScores = array_filter($periodScores, fn($s) => $s !== null);
                        $avg = count($validScores) > 0 ? array_sum($validScores) / count($validScores) : null;
                        if ($avg !== null) { $totalScores += $avg; $countScores++; }
                        $level = $avg !== null ? \App\Models\StudentGrade::getPerformanceLevel($avg) : null;
                    @endphp
                    <td class="grade-cell {{ $avg !== null ? ($avg >= 90 ? 'grade-high' : ($avg >= 70 ? 'grade-mid' : 'grade-low')) : '' }}">
                        {{ $avg !== null ? number_format($avg, 0) : '—' }}
                    </td>
                    <td>
                        @if($level)
                            <span class="performance-level level-{{ $level['letter'] }}">{{ $level['letter'] }} - {{ $level['label'] }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach

            {{-- AVERAGE ROW --}}
            <tr class="avg-row">
                <td style="text-align: right; font-size: 9pt;">PROMEDIO GENERAL</td>
                @foreach($allPeriods as $p)
                    @php
                        $periodAvg = collect($sectionSubjects)->map(function($ss) use ($allGrades, $p) {
                            return isset($allGrades[$p->id]) ? ($allGrades[$p->id][$ss->id] ?? null) : null;
                        })->filter(fn($v) => $v !== null);
                    @endphp
                    <td>{{ $periodAvg->count() > 0 ? number_format($periodAvg->avg(), 0) : '—' }}</td>
                @endforeach
                <td>{{ $countScores > 0 ? number_format($totalScores / $countScores, 0) : '—' }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- ATTENDANCE --}}
    <table class="attendance-table">
        <tr>
            <th>Días Registrados</th>
            <th>Presencias</th>
            <th>Ausencias</th>
            <th>Tardanzas</th>
            <th>% Asistencia</th>
        </tr>
        <tr>
            <td>{{ $attendanceStats['total_days'] }}</td>
            <td style="color: #166534; font-weight: bold;">{{ $attendanceStats['present'] }}</td>
            <td style="color: #991b1b; font-weight: bold;">{{ $attendanceStats['absent'] }}</td>
            <td style="color: #92400e;">{{ $attendanceStats['late'] }}</td>
            <td style="font-weight: bold;">
                {{ $attendanceStats['total_days'] > 0 ? number_format(($attendanceStats['present'] / $attendanceStats['total_days']) * 100, 1) : 0 }}%
            </td>
        </tr>
    </table>

    {{-- OBSERVATIONS --}}
    <div class="observations">
        <div class="label">Observaciones del Docente:</div>
        {{ $reportCard?->teacher_observations ?? 'Sin observaciones para este período.' }}
    </div>

    {{-- LEGEND --}}
    <div class="legend">
        <strong>Escala de Valoración MINERD:</strong>
        <table>
            <tr>
                <td><span class="performance-level level-A">A</span> Aprendizaje Logrado (90-100)</td>
                <td><span class="performance-level level-B">B</span> En Proceso Avanzado (80-89)</td>
                <td><span class="performance-level level-C">C</span> En Proceso Inicial (70-79)</td>
                <td><span class="performance-level level-D">D</span> En Inicio (0-69)</td>
            </tr>
        </table>
    </div>

    {{-- SIGNATURES --}}
    <table class="signatures-table">
        <tr>
            <td><div class="line">Docente Titular</div></td>
            <td><div class="line">Director/a</div></td>
            <td><div class="line">Padre / Madre / Tutor</div></td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="meta-info">
        {{ $schoolConfig?->school_name ?? '' }} | Boletín generado el {{ now()->format('d/m/Y h:i A') }}
        <span class="page-number"></span>
    </div>
</body>
</html>
