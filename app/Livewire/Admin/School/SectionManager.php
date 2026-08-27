<?php

namespace App\Livewire\Admin\School;

use App\Models\Section;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\User;
use Livewire\Component;

class SectionManager extends Component
{
    public $showModal = false;
    public $editingId = null;
    public $academic_year_id = '';
    public $grade_level_id = '';
    public $name = '';
    public $homeroom_teacher_id = '';
    public $capacity = 35;
    public $filterYear = '';
    public $filterLevel = '';

    public function create()
    {
        $this->reset(['name', 'grade_level_id', 'homeroom_teacher_id', 'capacity', 'editingId']);
        $activeYear = AcademicYear::where('status', 'active')->first();
        $this->academic_year_id = $activeYear?->id ?? '';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $section = Section::findOrFail($id);
        $this->editingId = $section->id;
        $this->academic_year_id = $section->academic_year_id;
        $this->grade_level_id = $section->grade_level_id;
        $this->name = $section->name;
        $this->homeroom_teacher_id = $section->homeroom_teacher_id ?? '';
        $this->capacity = $section->capacity;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'name' => 'required|string|max:5',
            'capacity' => 'required|integer|min:1|max:60',
        ]);

        Section::updateOrCreate(
            ['id' => $this->editingId],
            [
                'academic_year_id' => $this->academic_year_id,
                'grade_level_id' => $this->grade_level_id,
                'name' => strtoupper($this->name),
                'homeroom_teacher_id' => $this->homeroom_teacher_id ?: null,
                'capacity' => $this->capacity,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Sección actualizada.' : 'Sección creada exitosamente.');
    }

    public function delete($id)
    {
        Section::findOrFail($id)->delete();
        session()->flash('message', 'Sección eliminada.');
    }

    public function render()
    {
        $query = Section::with(['gradeLevel', 'academicYear', 'homeroomTeacher'])
            ->when($this->filterYear, fn($q) => $q->where('academic_year_id', $this->filterYear))
            ->when($this->filterLevel, fn($q) => $q->where('grade_level_id', $this->filterLevel))
            ->orderBy('grade_level_id')
            ->orderBy('name');

        return view('livewire.admin.school.section-manager', [
            'sections' => $query->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'gradeLevels' => GradeLevel::active()->ordered()->get(),
            'teachers' => User::role('Profesor')->orderBy('name')->get(),
        ])->layout('layouts.dashboard');
    }
}
