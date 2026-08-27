<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\SchoolSchedule;
use App\Models\SchoolConfig;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TimeBlock;
use App\Models\User;
use Livewire\Component;

class ScheduleBuilder extends Component
{
    public $section_id = '';
    public $scheduleGrid = []; // [time_block_id][day] => schedule data
    public $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    // Modal
    public $showModal = false;
    public $editBlockId = '';
    public $editDay = '';
    public $editSubjectId = '';
    public $editTeacherId = '';
    public $editClassroom = '';
    public $conflictWarning = '';

    // Time Blocks config
    public $showBlocksModal = false;

    public function updatedSectionId()
    {
        $this->loadGrid();
    }

    public function loadGrid()
    {
        if (!$this->section_id) {
            $this->scheduleGrid = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $blocks = TimeBlock::where('academic_year_id', $activeYear->id)
            ->active()->ordered()->get();

        $existing = SchoolSchedule::where('section_id', $this->section_id)
            ->where('academic_year_id', $activeYear->id)
            ->with(['subject', 'teacher'])
            ->get()
            ->groupBy(function($s) {
                return $s->time_block_id . '_' . $s->day_of_week;
            });

        $this->scheduleGrid = [];
        foreach ($blocks as $block) {
            $row = [
                'block_id'   => $block->id,
                'block_name' => $block->name,
                'time_range' => $block->time_range,
                'type'       => $block->type,
                'cells'      => [],
            ];

            foreach ($this->days as $day) {
                $key = $block->id . '_' . $day;
                $schedule = $existing->get($key)?->first();
                $row['cells'][$day] = [
                    'id'           => $schedule?->id,
                    'subject_name' => $schedule?->subject?->name ?? '',
                    'subject_id'   => $schedule?->subject_id ?? '',
                    'teacher_name' => $schedule?->teacher?->name ?? '',
                    'teacher_id'   => $schedule?->teacher_id ?? '',
                    'classroom'    => $schedule?->classroom_name ?? '',
                ];
            }
            $this->scheduleGrid[] = $row;
        }
    }

    public function openCell($blockId, $day)
    {
        $this->editBlockId = $blockId;
        $this->editDay = $day;
        $this->conflictWarning = '';

        // Find existing
        foreach ($this->scheduleGrid as $row) {
            if ($row['block_id'] == $blockId) {
                $cell = $row['cells'][$day] ?? null;
                $this->editSubjectId = $cell['subject_id'] ?? '';
                $this->editTeacherId = $cell['teacher_id'] ?? '';
                $this->editClassroom = $cell['classroom'] ?? '';
                break;
            }
        }

        $this->showModal = true;
    }

    public function updatedEditTeacherId()
    {
        $this->checkConflict();
    }

    public function checkConflict()
    {
        $this->conflictWarning = '';

        if (!$this->editTeacherId || !$this->editBlockId || !$this->editDay) return;

        // Find the current schedule ID to exclude
        $currentId = null;
        foreach ($this->scheduleGrid as $row) {
            if ($row['block_id'] == $this->editBlockId) {
                $currentId = $row['cells'][$this->editDay]['id'] ?? null;
                break;
            }
        }

        $conflict = SchoolSchedule::teacherConflicts(
            $this->editTeacherId,
            $this->editDay,
            $this->editBlockId,
            $currentId
        );

        if ($conflict) {
            $this->conflictWarning = 'Este docente ya está asignado en ' .
                ($conflict->section?->gradeLevel?->short_name ?? '') . ' ' .
                ($conflict->section?->name ?? '') . ' (' .
                ($conflict->subject?->name ?? '') . ')';
        }
    }

    public function saveCell()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        if (!$this->editSubjectId) {
            // Clear cell
            SchoolSchedule::where('section_id', $this->section_id)
                ->where('time_block_id', $this->editBlockId)
                ->where('day_of_week', $this->editDay)
                ->delete();
        } else {
            SchoolSchedule::updateOrCreate(
                [
                    'section_id'    => $this->section_id,
                    'time_block_id' => $this->editBlockId,
                    'day_of_week'   => $this->editDay,
                ],
                [
                    'academic_year_id' => $activeYear->id,
                    'subject_id'       => $this->editSubjectId,
                    'teacher_id'       => $this->editTeacherId ?: null,
                    'classroom_name'   => $this->editClassroom ?: null,
                ]
            );
        }

        $this->showModal = false;
        $this->loadGrid();
    }

    public function clearCell()
    {
        $this->editSubjectId = '';
        $this->editTeacherId = '';
        $this->editClassroom = '';
        $this->saveCell();
    }

    public function generateDefaultBlocks()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $config = SchoolConfig::current();
        $shift = $config?->shift ?? 'matutina';
        $template = TimeBlock::TEMPLATES[$shift] ?? TimeBlock::TEMPLATES['matutina'];

        foreach ($template as $i => $block) {
            TimeBlock::firstOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'start_time'       => $block['start_time'],
                ],
                array_merge($block, [
                    'academic_year_id' => $activeYear->id,
                    'order'            => $i,
                ])
            );
        }

        $this->loadGrid();
        session()->flash('message', 'Bloques horarios generados para tanda ' . $shift);
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

        $subjects = Subject::active()->orderBy('name')->get();

        $teachers = User::role('Profesor')->orderBy('name')->get();

        $hasBlocks = $activeYear
            ? TimeBlock::where('academic_year_id', $activeYear->id)->exists()
            : false;

        return view('livewire.admin.school.schedule-builder', [
            'activeYear' => $activeYear,
            'sections'   => $sections,
            'subjects'   => $subjects,
            'teachers'   => $teachers,
            'hasBlocks'  => $hasBlocks,
            'dayLabels'  => SchoolSchedule::DAYS,
        ])->layout('layouts.dashboard');
    }
}
