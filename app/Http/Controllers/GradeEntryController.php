<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLockPeriod;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeEntryController extends Controller
{
    /**
     * Display the grade entry matrix and evaluation register.
     */
    public function index(Request $request): Response
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'grade_level' => $s->gradeLevel?->short_name ?? $s->gradeLevel?->name ?? 'Grado',
                    'full_label' => ($s->gradeLevel?->short_name ?? 'Grado') . ' ' . $s->name,
                ])
            : collect();

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->orderBy('number')
                ->get()
                ->map(function ($p) {
                    $isLocked = GradeLockPeriod::isLocked($p->id);
                    $lockInfo = GradeLockPeriod::where('evaluation_period_id', $p->id)->first();
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'number' => $p->number,
                        'status' => $p->status,
                        'start_date' => $p->start_date?->format('d/m/Y'),
                        'end_date' => $p->end_date?->format('d/m/Y'),
                        'is_locked' => $isLocked,
                        'lock_date' => $lockInfo?->lock_date?->format('d/m/Y'),
                    ];
                })
            : collect();

        $sectionId = $request->query('section_id', $sections->first()['id'] ?? null);
        $periodId = $request->query('period_id', $periods->where('status', 'active')->first()['id'] ?? ($periods->first()['id'] ?? null));

        $sectionSubjects = $sectionId
            ? SectionSubject::where('section_id', $sectionId)
                ->with(['subject'])
                ->get()
                ->map(fn($ss) => [
                    'id' => $ss->id,
                    'subject_id' => $ss->subject_id,
                    'name' => $ss->subject?->name ?? 'Asignatura',
                    'code' => $ss->subject?->code ?? '',
                ])
            : collect();

        $sectionSubjectId = $request->query('section_subject_id', $sectionSubjects->first()['id'] ?? null);

        $students = [];
        $grades = [];
        $stats = [
            'total_students' => 0,
            'graded_count' => 0,
            'passing_count' => 0,
            'failing_count' => 0,
            'passing_rate' => 0,
            'average_score' => 0,
            'max_score' => 0,
            'min_score' => 0,
            'levels' => [
                'destacado' => 0,
                'logro_evidenciado' => 0,
                'en_proceso' => 0,
                'insuficiente' => 0,
            ],
        ];

        $isLocked = false;
        if ($periodId) {
            $isLocked = GradeLockPeriod::isLocked($periodId);
        }

        if ($sectionId && $periodId && $sectionSubjectId) {
            $studentModels = Student::where('section_id', $sectionId)
                ->whereIn('status', ['Activo', 'Activa', 'active'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $existingGrades = StudentGrade::where('section_subject_id', $sectionSubjectId)
                ->where('evaluation_period_id', $periodId)
                ->where('is_recovery', false)
                ->where('is_extraordinary', false)
                ->get()
                ->keyBy('student_id');

            $scoresList = [];

            $students = $studentModels->map(function ($s, $idx) use ($existingGrades, &$stats, &$scoresList) {
                $gradeRecord = $existingGrades->get($s->id);
                $score = $gradeRecord?->score !== null ? (float)$gradeRecord->score : null;

                $perfLevel = null;
                if ($score !== null) {
                    $scoresList[] = $score;
                    if ($score >= 89) {
                        $perfLevel = 'destacado';
                        $stats['levels']['destacado']++;
                    } elseif ($score >= 77) {
                        $perfLevel = 'logro_evidenciado';
                        $stats['levels']['logro_evidenciado']++;
                    } elseif ($score >= 65) {
                        $perfLevel = 'en_proceso';
                        $stats['levels']['en_proceso']++;
                    } else {
                        $perfLevel = 'insuficiente';
                        $stats['levels']['insuficiente']++;
                    }

                    if ($score >= 70) {
                        $stats['passing_count']++;
                    } else {
                        $stats['failing_count']++;
                    }
                }

                return [
                    'index' => $idx + 1,
                    'student_id' => $s->id,
                    'student_code' => $s->student_code ?? sprintf('EST-%04d', $s->id),
                    'student_name' => $s->last_name . ', ' . $s->first_name,
                    'first_name' => $s->first_name,
                    'last_name' => $s->last_name,
                    'score' => $score !== null ? (string)$score : '',
                    'performance_level' => $perfLevel,
                    'observations' => $gradeRecord?->observations ?? '',
                    'recorded_at' => $gradeRecord?->recorded_at?->format('d/m/Y H:i'),
                ];
            })->values()->toArray();

            $stats['total_students'] = count($students);
            $stats['graded_count'] = count($scoresList);
            if ($stats['graded_count'] > 0) {
                $stats['average_score'] = round(array_sum($scoresList) / count($scoresList), 1);
                $stats['max_score'] = max($scoresList);
                $stats['min_score'] = min($scoresList);
                $stats['passing_rate'] = round(($stats['passing_count'] / $stats['graded_count']) * 100);
            }
        }

        // Section Overview Matrix (all subjects or all periods)
        $overviewMatrix = [];
        if ($request->query('view_mode') === 'matrix' && $sectionId) {
            $studentModels = Student::where('section_id', $sectionId)
                ->whereIn('status', ['Activo', 'Activa', 'active'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $allGrades = StudentGrade::whereIn('section_subject_id', $sectionSubjects->pluck('id'))
                ->where('is_recovery', false)
                ->where('is_extraordinary', false)
                ->get();

            $overviewMatrix = $studentModels->map(function ($s, $idx) use ($sectionSubjects, $periods, $allGrades) {
                $studentRow = [
                    'index' => $idx + 1,
                    'student_id' => $s->id,
                    'student_code' => $s->student_code ?? sprintf('EST-%04d', $s->id),
                    'student_name' => $s->last_name . ', ' . $s->first_name,
                    'subject_scores' => [],
                    'overall_average' => 0,
                ];

                $accum = [];
                foreach ($sectionSubjects as $ss) {
                    $subjectGrades = $allGrades->where('student_id', $s->id)->where('section_subject_id', $ss['id']);
                    $avg = $subjectGrades->count() > 0 ? round($subjectGrades->avg('score'), 1) : null;
                    $studentRow['subject_scores'][$ss['id']] = $avg;
                    if ($avg !== null) $accum[] = $avg;
                }

                $studentRow['overall_average'] = count($accum) > 0 ? round(array_sum($accum) / count($accum), 1) : null;
                $studentRow['status'] = ($studentRow['overall_average'] !== null && $studentRow['overall_average'] >= 70) ? 'Aprobado' : (($studentRow['overall_average'] !== null) ? 'Reprobado' : 'Pendiente');
                return $studentRow;
            })->values()->toArray();
        }

        return Inertia::render('Grades/MatrixEntry', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
                'start_date' => $activeYear->start_date?->format('d/m/Y'),
                'end_date' => $activeYear->end_date?->format('d/m/Y'),
            ] : null,
            'sections' => $sections,
            'periods' => $periods,
            'sectionSubjects' => $sectionSubjects,
            'selectedSectionId' => (int)$sectionId,
            'selectedPeriodId' => (int)$periodId,
            'selectedSubjectId' => (int)$sectionSubjectId,
            'students' => $students,
            'stats' => $stats,
            'isLocked' => $isLocked,
            'viewMode' => $request->query('view_mode', 'single'),
            'overviewMatrix' => $overviewMatrix,
        ]);
    }

    /**
     * Save/Update grades in bulk.
     */
    public function saveGrades(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|integer',
            'period_id' => 'required|integer',
            'section_subject_id' => 'required|integer',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|integer',
            'grades.*.score' => 'nullable|numeric|min:0|max:100',
            'grades.*.observations' => 'nullable|string|max:500',
        ]);

        $periodId = $validated['period_id'];
        $sectionSubjectId = $validated['section_subject_id'];

        if (GradeLockPeriod::isLocked($periodId)) {
            // Check if user is administrator
            if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('SuperAdmin')) {
                return back()->with('error', 'El período de calificaciones seleccionado se encuentra bloqueado para edición.');
            }
        }

        $savedCount = 0;
        foreach ($validated['grades'] as $item) {
            $score = $item['score'] !== null && $item['score'] !== '' ? min(100, max(0, (float)$item['score'])) : null;
            $obs = $item['observations'] ?? null;

            if ($score === null && empty($obs)) {
                // If student has a previous record and now empty, keep or delete
                continue;
            }

            StudentGrade::updateOrCreate(
                [
                    'student_id' => $item['student_id'],
                    'section_subject_id' => $sectionSubjectId,
                    'evaluation_period_id' => $periodId,
                    'is_recovery' => false,
                    'is_extraordinary' => false,
                ],
                [
                    'score' => $score,
                    'observations' => $obs,
                    'recorded_by' => auth()->id(),
                    'recorded_at' => now(),
                ]
            );
            $savedCount++;
        }

        return back()->with('success', "Se han guardado {$savedCount} calificaciones correctamente.");
    }
}
