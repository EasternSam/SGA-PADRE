<?php

namespace App\Livewire\Admin\School;

use App\Models\StudentAttendance;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Student;
use Livewire\Component;
use Carbon\Carbon;

class AttendanceRegister extends Component
{
    public $section_id = '';
    public $date = '';
    public $attendances = [];
    public $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'permission' => 0];
    public $hasExisting = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSectionId()
    {
        $this->loadStudents();
    }

    public function updatedDate()
    {
        $this->loadStudents();
    }

    public function loadStudents()
    {
        if (!$this->section_id || !$this->date) {
            $this->attendances = [];
            return;
        }

        $students = Student::where('section_id', $this->section_id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $existing = StudentAttendance::where('section_id', $this->section_id)
            ->whereDate('date', $this->date)
            ->pluck('status', 'student_id');

        $this->hasExisting = $existing->isNotEmpty();

        $this->attendances = $students->map(fn($student) => [
            'student_id'   => $student->id,
            'student_name' => $student->full_name,
            'photo'        => $student->profile_photo_url ?? null,
            'status'       => $existing[$student->id] ?? 'present',
        ])->toArray();

        $this->calculateSummary();
    }

    public function setStatus($index, $status)
    {
        $this->attendances[$index]['status'] = $status;
        $this->calculateSummary();
    }

    public function markAllPresent()
    {
        foreach ($this->attendances as $i => $a) {
            $this->attendances[$i]['status'] = 'present';
        }
        $this->calculateSummary();
    }

    public function calculateSummary()
    {
        $this->summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'permission' => 0];
        foreach ($this->attendances as $a) {
            if (isset($this->summary[$a['status']])) {
                $this->summary[$a['status']]++;
            }
        }
    }

    public function save()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            session()->flash('error', 'No hay año escolar activo.');
            return;
        }

        foreach ($this->attendances as $data) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'date'       => $this->date,
                ],
                [
                    'section_id'      => $this->section_id,
                    'academic_year_id' => $activeYear->id,
                    'status'          => $data['status'],
                    'recorded_by'     => auth()->id(),
                ]
            );
        }

        $this->hasExisting = true;
        session()->flash('message', 'Asistencia guardada correctamente para ' . Carbon::parse($this->date)->format('d/m/Y'));
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

        return view('livewire.admin.school.attendance-register', [
            'activeYear' => $activeYear,
            'sections'   => $sections,
        ])->layout('layouts.dashboard');
    }
}
