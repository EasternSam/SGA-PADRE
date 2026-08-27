<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\PromotionRecord;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentGrade;
use Livewire\Component;

class PromotionManager extends Component
{
    public $section_id = '';
    public $students = [];

    // Stats
    public $stats = ['promoted' => 0, 'retained' => 0, 'pending' => 0, 'total' => 0];

    public function updatedSectionId()
    {
        $this->loadData();
    }

    /**
     * Evalúa cada estudiante por asignaturas individuales según normativa MINERD.
     * NO se promueve por promedio general — se evalúa materia por materia.
     */
    public function loadData()
    {
        if (!$this->section_id) {
            $this->students = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $section = Section::with('gradeLevel')->find($this->section_id);
        if (!$section) return;

        $passingScore = $section->gradeLevel?->min_passing_score ?? 70;

        $students = Student::where('section_id', $this->section_id)
            ->where('status', 'Activo')
            ->with('gradeLevel')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Asignaturas de esta sección
        $sectionSubjects = SectionSubject::where('section_id', $this->section_id)
            ->with('subject')
            ->get();

        $existingRecords = PromotionRecord::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // Períodos del año activo
        $periodIds = \App\Models\EvaluationPeriod::where('academic_year_id', $activeYear->id)
            ->pluck('id');

        $this->students = [];
        $this->stats = ['promoted' => 0, 'retained' => 0, 'pending' => 0, 'total' => $students->count()];

        foreach ($students as $student) {
            // Evaluar CADA asignatura individualmente
            $failedSubjects = [];
            $subjectAverages = [];
            $totalScoreSum = 0;
            $totalScoreCount = 0;

            foreach ($sectionSubjects as $ss) {
                $grades = StudentGrade::where('student_id', $student->id)
                    ->where('section_subject_id', $ss->id)
                    ->whereIn('evaluation_period_id', $periodIds)
                    ->where('is_recovery', false)
                    ->where('is_extraordinary', false)
                    ->whereNotNull('score')
                    ->get();

                if ($grades->isEmpty()) continue;

                $subjectAvg = round($grades->avg('score'), 2);
                $subjectAverages[] = [
                    'name'  => $ss->subject?->name ?? 'Asignatura',
                    'avg'   => $subjectAvg,
                    'passed' => $subjectAvg >= $passingScore,
                ];

                $totalScoreSum += $subjectAvg;
                $totalScoreCount++;

                if ($subjectAvg < $passingScore) {
                    $failedSubjects[] = $ss->subject?->name ?? 'Asignatura';
                }
            }

            $generalAvg = $totalScoreCount > 0 ? round($totalScoreSum / $totalScoreCount, 2) : null;
            $failedCount = count($failedSubjects);
            $record = $existingRecords[$student->id] ?? null;

            if ($record) {
                if ($record->result === 'promoted') $this->stats['promoted']++;
                if ($record->result === 'retained') $this->stats['retained']++;
            }

            $this->students[] = [
                'id'              => $student->id,
                'name'            => $student->full_name,
                'grade_level'     => $student->gradeLevel?->short_name,
                'passing_score'   => $passingScore,
                'average'         => $generalAvg,
                'failed_count'    => $failedCount,
                'failed_subjects' => $failedSubjects,
                'subject_count'   => count($subjectAverages),
                'record_id'       => $record?->id,
                'result'          => $record?->result ?? '',
                'observations'    => $record?->observations ?? '',
            ];
        }
    }

    public function setResult($studentId, $result, $observations = null)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $student = Student::find($studentId);
        if (!$student) return;

        // Calcular promedio general para el récord
        $allGrades = StudentGrade::where('student_id', $studentId)
            ->whereHas('evaluationPeriod', fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->whereNotNull('score')
            ->get();
        $avg = $allGrades->count() > 0 ? round($allGrades->avg('score'), 2) : null;

        PromotionRecord::updateOrCreate(
            [
                'student_id'       => $studentId,
                'academic_year_id' => $activeYear->id,
            ],
            [
                'grade_level_id' => $student->grade_level_id,
                'section_id'     => $student->section_id,
                'result'         => $result,
                'final_average'  => $avg,
                'observations'   => $observations,
                'decision_date'  => now(),
            ]
        );

        $this->loadData();
    }

    /**
     * Promoción automática basada en asignaturas individuales (normativa MINERD).
     *
     * - 0 materias reprobadas: Promovido
     * - 1-2 materias reprobadas: Promovido con asignaturas pendientes
     * - 3+ materias reprobadas: Repitente
     */
    public function autoPromote()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $promoted = 0;
        $retained = 0;
        $pending = 0;

        foreach ($this->students as $s) {
            if ($s['result']) continue; // Saltar decisiones ya tomadas

            // Sin datos académicos → no se puede decidir
            if ($s['subject_count'] === 0) continue;

            $failedCount = $s['failed_count'];
            $failedNames = implode(', ', $s['failed_subjects']);

            if ($failedCount === 0) {
                $this->setResult($s['id'], 'promoted', 'Promovido — todas las asignaturas aprobadas.');
                $promoted++;
            } elseif ($failedCount <= 2) {
                $this->setResult($s['id'], 'promoted', "Promovido con {$failedCount} asignatura(s) pendiente(s): {$failedNames}.");
                $pending++;
            } else {
                $this->setResult($s['id'], 'retained', "Repitente — {$failedCount} asignaturas reprobadas: {$failedNames}.");
                $retained++;
            }
        }

        $msg = "Promoción automática completada. Promovidos: {$promoted}, Con pendientes: {$pending}, Repitentes: {$retained}.";
        session()->flash('message', $msg);
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.school.promotion-manager', [
            'activeYear' => $activeYear,
            'sections'   => $sections,
            'results'    => PromotionRecord::RESULTS,
        ])->layout('layouts.dashboard');
    }
}
