<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGrade;
use Livewire\Component;

class HonorRoll extends Component
{
    public $period_id = '';
    public $grade_level_id = '';
    public $minAverage = 90;
    public $honorStudents = [];

    public function updatedPeriodId() { $this->loadData(); }
    public function updatedGradeLevelId() { $this->loadData(); }
    public function updatedMinAverage() { $this->loadData(); }

    public function loadData()
    {
        if (!$this->period_id) {
            $this->honorStudents = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        // Get students query
        $studentsQuery = Student::where('status', 'Activo')
            ->with('gradeLevel')
            ->when($this->grade_level_id, fn($q) => $q->where('grade_level_id', $this->grade_level_id));

        $students = $studentsQuery->get();
        $results = [];

        foreach ($students as $student) {
            $grades = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $this->period_id)
                ->whereNotNull('score')
                ->get();

            if ($grades->isEmpty()) continue;

            $avg = round($grades->avg('score'), 2);
            if ($avg < $this->minAverage) continue;

            // Verificar que NINGUNA asignatura esté reprobada
            $passingScore = $student->gradeLevel?->min_passing_score ?? 70;
            $hasFailedSubject = $grades->contains(fn($g) => $g->score < $passingScore);
            if ($hasFailedSubject) continue;

            $section = Section::with('gradeLevel')->find($student->section_id);
            $results[] = [
                'student'    => $student,
                'average'    => $avg,
                'grade_count'=> $grades->count(),
                'min_score'  => $grades->min('score'),
                'max_score'  => $grades->max('score'),
                'section'    => $section,
            ];
        }

        // Sort by average descending
        usort($results, fn($a, $b) => $b['average'] <=> $a['average']);

        $this->honorStudents = $results;
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $gradeLevels = GradeLevel::active()->ordered()->get();

        return view('livewire.admin.school.honor-roll', [
            'activeYear'  => $activeYear,
            'periods'     => $periods,
            'gradeLevels' => $gradeLevels,
        ])->layout('layouts.dashboard');
    }
}
