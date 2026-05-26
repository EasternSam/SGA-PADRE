<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\StudentGrade;
use App\Models\EvaluationPeriod;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;

class Grades extends Component
{
    public $periods = [];
    public $selectedPeriod = '';
    public $grades = [];
    public $studentName = '';
    public $sectionName = '';
    public $generalAverage = null;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->student) {
            return redirect()->route('kiosk.login');
        }

        $student = $user->student;
        $this->studentName = $student->full_name;
        $this->sectionName = $student->section?->name ?? '';

        // Períodos del año activo
        $activeYear = AcademicYear::where('status', 'active')->first();
        if ($activeYear) {
            $this->periods = EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->orderBy('start_date')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
                ->toArray();

            if (!empty($this->periods)) {
                $this->selectedPeriod = $this->periods[0]['id'];
                $this->loadGrades();
            }
        }
    }

    public function updatedSelectedPeriod()
    {
        $this->loadGrades();
    }

    public function loadGrades()
    {
        $user = Auth::user();
        if (!$user || !$user->student) return;

        $student = $user->student;
        $passingScore = $student->gradeLevel?->min_passing_score ?? 70;

        $this->grades = StudentGrade::where('student_id', $student->id)
            ->where('evaluation_period_id', $this->selectedPeriod)
            ->whereNotNull('score')
            ->with('sectionSubject.subject')
            ->get()
            ->map(function ($grade) use ($passingScore) {
                return [
                    'subject' => $grade->sectionSubject?->subject?->name ?? 'Asignatura',
                    'score' => $grade->score,
                    'passed' => $grade->score >= $passingScore,
                    'literal' => $this->getLiteralGrade($grade->score),
                ];
            })
            ->sortBy('subject')
            ->values()
            ->toArray();

        $scores = array_column($this->grades, 'score');
        $this->generalAverage = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
    }

    private function getLiteralGrade($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    public function goBack()
    {
        return $this->redirect(route('kiosk.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.grades')->layout('layouts.kiosk');
    }
}
