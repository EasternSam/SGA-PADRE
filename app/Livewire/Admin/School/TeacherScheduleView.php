<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\SchoolSchedule;
use App\Models\TimeBlock;
use Livewire\Component;

class TeacherScheduleView extends Component
{
    public $teacher_id = '';
    public $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    public $scheduleGrid = [];

    public function mount()
    {
        // Default to current logged-in user if they're a teacher
        if (auth()->user()?->hasRole('Profesor')) {
            $this->teacher_id = auth()->id();
        }
    }

    public function updatedTeacherId()
    {
        $this->loadGrid();
    }

    public function loadGrid()
    {
        if (!$this->teacher_id) {
            $this->scheduleGrid = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $blocks = TimeBlock::where('academic_year_id', $activeYear->id)
            ->active()->ordered()->get();

        $existing = SchoolSchedule::where('teacher_id', $this->teacher_id)
            ->where('academic_year_id', $activeYear->id)
            ->with(['subject', 'section.gradeLevel'])
            ->get()
            ->groupBy(fn($s) => $s->time_block_id . '_' . $s->day_of_week);

        $this->scheduleGrid = [];
        $totalHours = 0;
        foreach ($blocks as $block) {
            $row = [
                'block_name' => $block->name,
                'time_range' => $block->time_range,
                'type'       => $block->type,
                'cells'      => [],
            ];

            foreach ($this->days as $day) {
                $key = $block->id . '_' . $day;
                $schedule = $existing->get($key)?->first();
                $row['cells'][$day] = [
                    'subject'    => $schedule?->subject?->name ?? '',
                    'section'    => ($schedule?->section?->gradeLevel?->short_name ?? '') . ' ' . ($schedule?->section?->name ?? ''),
                    'classroom'  => $schedule?->classroom_name ?? '',
                    'has_class'  => $schedule !== null,
                ];
                if ($schedule) $totalHours++;
            }
            $this->scheduleGrid[] = $row;
        }
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $teachers = \App\Models\User::role('Profesor')->orderBy('name')->get();

        $isTeacherOnly = auth()->user()?->hasRole('Profesor') && !auth()->user()?->hasRole('Admin');

        // Count total teaching hours
        $totalClasses = 0;
        foreach ($this->scheduleGrid as $row) {
            foreach ($row['cells'] as $cell) {
                if ($cell['has_class']) $totalClasses++;
            }
        }

        return view('livewire.admin.school.teacher-schedule-view', [
            'activeYear'    => $activeYear,
            'teachers'      => $teachers,
            'isTeacherOnly' => $isTeacherOnly,
            'totalClasses'  => $totalClasses,
            'dayLabels'     => SchoolSchedule::DAYS,
        ])->layout('layouts.dashboard');
    }
}
