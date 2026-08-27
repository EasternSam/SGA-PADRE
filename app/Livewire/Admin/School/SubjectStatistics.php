<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudentGrade;
use Livewire\Component;

class SubjectStatistics extends Component
{
    public $period_id = '';

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        // If no period selected, use latest with data
        if (!$this->period_id && $periods->isNotEmpty()) {
            $latestWithData = $periods->last();
            $this->period_id = $latestWithData?->id ?? '';
        }

        $subjectStats = [];

        if ($this->period_id) {
            // Get all section_subjects for the active year
            $sectionSubjects = SectionSubject::whereHas('section', fn($q) =>
                    $q->where('academic_year_id', $activeYear?->id)
                )
                ->with(['subject', 'section.gradeLevel'])
                ->get();

            $grouped = $sectionSubjects->groupBy(fn($ss) => $ss->subject?->id);

            foreach ($grouped as $subjectId => $ssGroup) {
                $subjectName = $ssGroup->first()?->subject?->name ?? '—';

                $allGrades = StudentGrade::whereIn('section_subject_id', $ssGroup->pluck('id'))
                    ->where('evaluation_period_id', $this->period_id)
                    ->whereNotNull('score')
                    ->get();

                if ($allGrades->isEmpty()) continue;

                $avg = round($allGrades->avg('score'), 1);
                $max = $allGrades->max('score');
                $min = $allGrades->min('score');
                $count = $allGrades->count();
                $approved = $allGrades->where('score', '>=', 70)->count();
                $failed = $count - $approved;
                $approvalRate = $count > 0 ? round(($approved / $count) * 100, 1) : 0;

                $subjectStats[] = [
                    'name'         => $subjectName,
                    'avg'          => $avg,
                    'max'          => $max,
                    'min'          => $min,
                    'count'        => $count,
                    'approved'     => $approved,
                    'failed'       => $failed,
                    'approvalRate' => $approvalRate,
                    'sections'     => $ssGroup->count(),
                ];
            }

            // Sort by avg descending
            usort($subjectStats, fn($a, $b) => $b['avg'] <=> $a['avg']);
        }

        return view('livewire.admin.school.subject-statistics', [
            'periods'      => $periods,
            'subjectStats' => $subjectStats,
        ])->layout('layouts.dashboard');
    }
}
