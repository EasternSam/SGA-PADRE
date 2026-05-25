<?php

namespace App\Livewire\Admin\School;

use App\Models\Subject;
use App\Models\GradeLevel;
use Livewire\Component;

class SubjectManager extends Component
{
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $code = '';
    public $area = '';
    public $is_core = true;
    public $weekly_hours = 4;
    public $description = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:subjects,code,' . $this->editingId,
            'area' => 'required|string',
            'weekly_hours' => 'required|integer|min:1|max:10',
        ];
    }

    public function create()
    {
        $this->reset(['name', 'code', 'area', 'is_core', 'weekly_hours', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code;
        $this->area = $subject->area;
        $this->is_core = $subject->is_core;
        $this->weekly_hours = $subject->weekly_hours;
        $this->description = $subject->description ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Subject::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'area' => $this->area,
                'is_core' => $this->is_core,
                'weekly_hours' => $this->weekly_hours,
                'description' => $this->description ?: null,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Asignatura actualizada.' : 'Asignatura creada.');
    }

    public function delete($id)
    {
        Subject::findOrFail($id)->delete();
        session()->flash('message', 'Asignatura eliminada.');
    }

    public function render()
    {
        return view('livewire.admin.school.subject-manager', [
            'subjects' => Subject::orderBy('area')->orderBy('name')->get(),
            'areas' => Subject::AREAS,
        ])->layout('layouts.dashboard');
    }
}
