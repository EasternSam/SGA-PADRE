<?php

namespace App\Livewire\Admin\School;

use App\Models\Student;
use App\Services\StudentLifecycleService;
use Livewire\Component;

class StudentJourney extends Component
{
    public $search = '';
    public $student_id = '';
    public $journey = null;
    public $history = [];
    public $studentInfo = null;

    public function mount()
    {
        if (request()->has('student_id')) {
            $this->student_id = request()->get('student_id');
            $this->loadJourney();
        }
    }

    public function selectStudent($id)
    {
        $this->student_id = $id;
        $this->search = '';
        $this->loadJourney();
    }

    public function loadJourney()
    {
        if (!$this->student_id) return;

        $student = Student::with(['gradeLevel', 'section'])->find($this->student_id);
        if (!$student) return;

        $service = app(StudentLifecycleService::class);

        $this->journey = $service->calculate($student);
        $this->history = $service->history($student);
        $this->studentInfo = [
            'id' => $student->id,
            'full_name' => $student->full_name,
            'grade' => $student->gradeLevel?->name ?? '—',
            'section' => $student->section?->name ?? '—',
            'status' => $student->status,
            'student_code' => $student->student_code ?? '—',
        ];
    }

    public function render()
    {
        $searchResults = $this->search && strlen($this->search) >= 2
            ? Student::where(function($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('student_code', 'like', "%{$this->search}%");
                })
                ->limit(10)
                ->get()
            : collect();

        return view('livewire.admin.school.student-journey', [
            'searchResults' => $searchResults,
        ])->layout('layouts.dashboard');
    }
}
