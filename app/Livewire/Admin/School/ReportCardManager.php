<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\Section;
use App\Models\Student;
use App\Models\ReportCard;
use Livewire\Component;

class ReportCardManager extends Component
{
    public $section_id = '';
    public $period_id = '';
    public $students = [];
    public $reportCards = [];

    // Modal para observaciones
    public $showNotesModal = false;
    public $editStudentId = null;
    public $editStudentName = '';
    public $teacher_comments = '';
    public $counselor_comments = '';
    public $conduct_grade = '';

    public function updatedSectionId()
    {
        $this->loadData();
    }

    public function updatedPeriodId()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->section_id || !$this->period_id) {
            $this->students = [];
            $this->reportCards = [];
            return;
        }

        $this->students = Student::where('section_id', $this->section_id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->toArray();

        $cards = ReportCard::where('section_id', $this->section_id)
            ->where('evaluation_period_id', $this->period_id)
            ->get()
            ->keyBy('student_id');

        $this->reportCards = [];
        foreach ($this->students as $s) {
            $card = $cards[$s['id']] ?? null;
            $this->reportCards[$s['id']] = [
                'exists'             => $card !== null,
                'teacher_comments'   => $card?->teacher_observations ?? '',
                'counselor_comments' => $card?->counselor_observations ?? '',
                'conduct_grade'      => $card?->conduct ?? '',
                'generated_at'       => $card?->generated_at,
            ];
        }
    }

    public function openNotes($studentId)
    {
        $student = collect($this->students)->firstWhere('id', $studentId);
        $this->editStudentId = $studentId;
        $this->editStudentName = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');
        $this->teacher_comments = $this->reportCards[$studentId]['teacher_comments'] ?? '';
        $this->counselor_comments = $this->reportCards[$studentId]['counselor_comments'] ?? '';
        $this->conduct_grade = $this->reportCards[$studentId]['conduct_grade'] ?? '';
        $this->showNotesModal = true;
    }

    public function saveNotes()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        ReportCard::updateOrCreate(
            [
                'student_id'          => $this->editStudentId,
                'evaluation_period_id' => $this->period_id,
            ],
            [
                'section_id'          => $this->section_id,
                'academic_year_id'    => $activeYear->id,
                'teacher_observations'    => $this->teacher_comments,
                'counselor_observations'  => $this->counselor_comments,
                'conduct'                 => $this->conduct_grade ?: null,
            ]
        );

        $this->showNotesModal = false;
        $this->loadData();
        session()->flash('message', 'Observaciones guardadas.');
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.school.report-card-manager', [
            'activeYear' => $activeYear,
            'periods'    => $periods,
            'sections'   => $sections,
        ])->layout('layouts.dashboard');
    }
}
