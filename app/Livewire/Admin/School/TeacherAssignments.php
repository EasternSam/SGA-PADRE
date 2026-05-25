<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Livewire\Component;

class TeacherAssignments extends Component
{
    public $section_id = '';
    public $assignments = [];

    // Modal
    public $showModal = false;
    public $editSubjectId = '';
    public $editTeacherId = '';
    public $editIsHomeroom = false;
    public $editId = null;

    public function updatedSectionId()
    {
        $this->loadAssignments();
    }

    public function loadAssignments()
    {
        if (!$this->section_id) {
            $this->assignments = [];
            return;
        }

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) return;

        // Get section subjects
        $sectionSubjects = SectionSubject::where('section_id', $this->section_id)
            ->with('subject')
            ->get();

        // Get existing assignments
        $existing = TeacherAssignment::where('section_id', $this->section_id)
            ->where('academic_year_id', $activeYear->id)
            ->with('teacher')
            ->get()
            ->keyBy('subject_id');

        $this->assignments = [];
        foreach ($sectionSubjects as $ss) {
            $assignment = $existing[$ss->subject_id] ?? null;
            $this->assignments[] = [
                'id'            => $assignment?->id,
                'subject_id'    => $ss->subject_id,
                'subject_name'  => $ss->subject?->name ?? '',
                'teacher_id'    => $assignment?->teacher_id,
                'teacher_name'  => $assignment?->teacher?->name ?? 'Sin asignar',
                'is_homeroom'   => $assignment?->is_homeroom ?? false,
            ];
        }
    }

    public function openAssign($subjectId)
    {
        $match = collect($this->assignments)->firstWhere('subject_id', $subjectId);
        $this->editId = $match['id'] ?? null;
        $this->editSubjectId = $subjectId;
        $this->editTeacherId = $match['teacher_id'] ?? '';
        $this->editIsHomeroom = $match['is_homeroom'] ?? false;
        $this->showModal = true;
    }

    public function saveAssignment()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear || !$this->editTeacherId) return;

        // If setting homeroom, clear any existing homeroom for this section
        if ($this->editIsHomeroom) {
            TeacherAssignment::where('section_id', $this->section_id)
                ->where('academic_year_id', $activeYear->id)
                ->where('is_homeroom', true)
                ->update(['is_homeroom' => false]);
        }

        TeacherAssignment::updateOrCreate(
            [
                'section_id'       => $this->section_id,
                'subject_id'       => $this->editSubjectId,
                'academic_year_id' => $activeYear->id,
            ],
            [
                'teacher_id'  => $this->editTeacherId,
                'is_homeroom' => $this->editIsHomeroom,
            ]
        );

        $this->showModal = false;
        $this->loadAssignments();
        session()->flash('message', 'Docente asignado correctamente.');
    }

    public function removeAssignment($subjectId)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        TeacherAssignment::where('section_id', $this->section_id)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $activeYear?->id)
            ->delete();

        $this->loadAssignments();
        session()->flash('message', 'Asignación removida.');
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

        $teachers = User::role('Profesor')->orderBy('name')->get();

        // Teacher workload summary
        $workloads = [];
        if ($activeYear) {
            foreach ($teachers as $t) {
                $count = TeacherAssignment::teacherLoad($t->id, $activeYear->id);
                if ($count > 0) {
                    $workloads[$t->id] = $count;
                }
            }
        }

        return view('livewire.admin.school.teacher-assignments', [
            'activeYear' => $activeYear,
            'sections'   => $sections,
            'teachers'   => $teachers,
            'workloads'  => $workloads,
        ])->layout('layouts.dashboard');
    }
}
