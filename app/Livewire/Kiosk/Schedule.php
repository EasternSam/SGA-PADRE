<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.kiosk')]
class Schedule extends Component
{
    public ?Student $student;
    public $activeEnrollments = [];

    public function mount()
    {
        $user = Auth::user();
        $this->student = $user?->student;

        if ($this->student) {
            $this->activeEnrollments = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher',
                'courseSchedule.classroom' // asumiendo que hay un modelo de classroom
            ])
            ->where('student_id', $this->student->id)
            ->whereIn('status', ['Cursando', 'Activo', 'Enrolled'])
            ->get();
        }
    }

    public function goBack()
    {
        return $this->redirectRoute('kiosk.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.schedule');
    }
}
