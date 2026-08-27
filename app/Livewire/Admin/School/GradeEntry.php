<?php

namespace App\Livewire\Admin\School;

use App\Models\StudentGrade;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\EvaluationPeriod;
use App\Models\AcademicYear;
use App\Models\GradeLockPeriod;
use App\Models\Student;
use Livewire\Component;

class GradeEntry extends Component
{
    public $section_id = '';
    public $period_id = '';
    public $section_subject_id = '';
    public $grades = [];
    public $saving = false;

    public function updatedSectionSubjectId()
    {
        $this->loadGrades();
    }

    public function updatedPeriodId()
    {
        $this->loadGrades();
    }

    public function loadGrades()
    {
        if (!$this->section_id || !$this->period_id || !$this->section_subject_id) {
            $this->grades = [];
            return;
        }

        $students = Student::where('section_id', $this->section_id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $existingGrades = StudentGrade::where('section_subject_id', $this->section_subject_id)
            ->where('evaluation_period_id', $this->period_id)
            ->where('is_recovery', false)
            ->where('is_extraordinary', false)
            ->pluck('score', 'student_id');

        $this->grades = $students->map(fn($student) => [
            'student_id'   => $student->id,
            'student_name' => $student->full_name,
            'score'        => $existingGrades[$student->id] ?? '',
        ])->toArray();
    }

    public function saveGrades()
    {
        // Check grade lock
        if ($this->period_id && GradeLockPeriod::isLocked($this->period_id)) {
            session()->flash('error', 'Este período está bloqueado. No se pueden modificar calificaciones.');
            return;
        }

        $this->saving = true;

        foreach ($this->grades as $gradeData) {
            if ($gradeData['score'] === '' || $gradeData['score'] === null) continue;

            $score = min(100, max(0, floatval($gradeData['score'])));

            StudentGrade::updateOrCreate(
                [
                    'student_id'         => $gradeData['student_id'],
                    'section_subject_id' => $this->section_subject_id,
                    'evaluation_period_id' => $this->period_id,
                    'is_recovery'        => false,
                    'is_extraordinary'   => false,
                ],
                [
                    'score'       => $score,
                    'recorded_by' => auth()->id(),
                    'recorded_at' => now(),
                ]
            );
        }

        $this->saving = false;
        session()->flash('message', 'Calificaciones guardadas exitosamente.');
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

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $sectionSubjects = $this->section_id
            ? SectionSubject::where('section_id', $this->section_id)
                ->with('subject')
                ->get()
            : collect();

        $isLocked = $this->period_id ? GradeLockPeriod::isLocked($this->period_id) : false;

        return view('livewire.admin.school.grade-entry', [
            'activeYear'      => $activeYear,
            'sections'        => $sections,
            'periods'         => $periods,
            'sectionSubjects' => $sectionSubjects,
            'isLocked'        => $isLocked,
        ])->layout('layouts.dashboard');
    }
}
