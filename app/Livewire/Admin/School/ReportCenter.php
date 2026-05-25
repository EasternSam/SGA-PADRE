<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\Section;
use Livewire\Component;

class ReportCenter extends Component
{
    public $selectedSection = '';
    public $selectedPeriod = '';
    public $selectedMonth = '';

    public function mount()
    {
        $this->selectedMonth = now()->month;
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
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->orderBy('number')
                ->get()
            : collect();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = ucfirst(\Carbon\Carbon::create(null, $i, 1)->translatedFormat('F'));
        }

        return view('livewire.admin.school.report-center', [
            'activeYear' => $activeYear,
            'sections'   => $sections,
            'periods'    => $periods,
            'months'     => $months,
        ])->layout('layouts.dashboard');
    }
}
