<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\PromotionRecord;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGrade;
use Livewire\Component;

class PromotionManager extends Component
{
    public $section_id = '';
    public $students = [];

    // Stats
    public $stats = ['promoted' => 0, 'retained' => 0, 'total' => 0];

    public function updatedSectionId()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->section_id) {
            $this->students = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $students = Student::where('section_id', $this->section_id)
            ->where('status', 'Activo')
            ->with('gradeLevel')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $existingRecords = PromotionRecord::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $this->students = [];
        $this->stats = ['promoted' => 0, 'retained' => 0, 'total' => $students->count()];

        foreach ($students as $student) {
            // Calculate final average
            $allGrades = StudentGrade::where('student_id', $student->id)
                ->whereHas('evaluationPeriod', fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->whereNotNull('score')
                ->get();

            $avg = $allGrades->count() > 0 ? round($allGrades->avg('score'), 2) : null;
            $record = $existingRecords[$student->id] ?? null;

            if ($record) {
                if ($record->result === 'promoted') $this->stats['promoted']++;
                if ($record->result === 'retained') $this->stats['retained']++;
            }

            $this->students[] = [
                'id'            => $student->id,
                'name'          => $student->full_name,
                'grade_level'   => $student->gradeLevel?->short_name,
                'average'       => $avg,
                'record_id'     => $record?->id,
                'result'        => $record?->result ?? '',
                'observations'  => $record?->observations ?? '',
            ];
        }
    }

    public function setResult($studentId, $result)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $student = Student::find($studentId);
        if (!$student) return;

        // Calculate final average
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
                'decision_date'  => now(),
            ]
        );

        $this->loadData();
    }

    public function autoPromote()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        foreach ($this->students as $s) {
            if ($s['result']) continue; // Skip already decided

            $result = ($s['average'] !== null && $s['average'] >= 70) ? 'promoted' : 'retained';
            $this->setResult($s['id'], $result);
        }

        session()->flash('message', 'Promoción automática completada (≥70 = Promovido).');
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
