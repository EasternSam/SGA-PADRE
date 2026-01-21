<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Building;
use App\Models\Classroom;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class ClassroomManagement extends Component
{
    public $selectedClassroom = null;
    public $showingScheduleModal = false;
    public $weekSchedules = [];

    public function render()
    {
        $buildings = Building::with(['classrooms.schedules' => function($q) {
            // Precargar horarios futuros cercanos para optimizar el "isOccupied"
            $q->where('end_date', '>=', now());
        }])->get();

        return view('livewire.admin.classroom-management', [
            'buildings' => $buildings
        ]);
    }

    public function showSchedule($classroomId)
    {
        $this->selectedClassroom = Classroom::with('schedules.module.course', 'schedules.teacher')->find($classroomId);
        
        // Organizar horarios de la semana
        $this->weekSchedules = $this->selectedClassroom->schedules
            ->where('end_date', '>=', now()) // Solo cursos vigentes
            ->sortBy('start_time');

        $this->showingScheduleModal = true;
    }

    public function closeModal()
    {
        $this->showingScheduleModal = false;
        $this->selectedClassroom = null;
    }
}