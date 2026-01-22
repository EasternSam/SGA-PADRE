<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Building;
use App\Models\Classroom;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class ClassroomManagement extends Component
{
    public $selectedClassroom = null;
    public $showingScheduleModal = false;
    
    // Estructura para el calendario visual
    public $calendarGrid = []; 
    public $timeSlots = [];
    public $daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    public function mount()
    {
        // Generar slots de tiempo de 7:00 AM a 10:00 PM (cada hora)
        $start = Carbon::createFromTime(7, 0);
        $end = Carbon::createFromTime(22, 0);
        
        while ($start <= $end) {
            $this->timeSlots[] = $start->format('H:i');
            $start->addHour();
        }
    }

    public function render()
    {
        // Cargamos edificios con aulas y sus horarios ACTIVOS (futuros o presentes)
        // Y filtramos explícitamente deleted_at null por seguridad
        $buildings = Building::with(['classrooms.schedules' => function($q) {
            $q->whereNull('deleted_at') // <--- FILTRO DE SEGURIDAD
              ->where('end_date', '>=', now())
              ->with(['module.course', 'teacher']);
        }])->get();

        return view('livewire.admin.classroom-management', [
            'buildings' => $buildings
        ]);
    }

    public function showSchedule($classroomId)
    {
        $this->selectedClassroom = Classroom::with(['schedules' => function($q) {
            $q->whereNull('deleted_at') // <--- FILTRO DE SEGURIDAD
              ->whereDate('end_date', '>=', now()->startOfDay())
              ->with(['module.course', 'teacher']);
        }, 'building'])->find($classroomId);

        $this->generateCalendarGrid();

        $this->showingScheduleModal = true;
        $this->dispatch('open-modal', 'schedule-view-modal');
    }

    public function closeModal()
    {
        $this->showingScheduleModal = false;
        $this->selectedClassroom = null;
        $this->calendarGrid = [];
        $this->dispatch('close-modal', 'schedule-view-modal');
    }

    /**
     * Construye una matriz [Hora][Día] = Info del Curso
     */
    private function generateCalendarGrid()
    {
        $this->calendarGrid = [];

        if (!$this->selectedClassroom || $this->selectedClassroom->schedules->isEmpty()) {
            return;
        }

        foreach ($this->selectedClassroom->schedules as $schedule) {
            
            // Doble verificación por si acaso
            if ($schedule->trashed()) continue;

            if (!$schedule->start_time || !$schedule->end_time) continue;

            try {
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
            } catch (\Exception $e) {
                continue;
            }
            
            if (is_array($schedule->days_of_week)) {
                foreach ($schedule->days_of_week as $dayRaw) {
                    
                    $day = mb_convert_case($dayRaw, MB_CASE_TITLE, "UTF-8");

                    $tempStart = $start->copy();
                    
                    if ($tempStart >= $end) continue;

                    while ($tempStart < $end) {
                        $hourKey = $tempStart->format('H') . ':00'; 
                        
                        if (in_array($hourKey, $this->timeSlots)) {
                            if (!isset($this->calendarGrid[$hourKey][$day])) {
                                
                                $duration = $start->diffInHours($end);
                                $rowspan = $duration < 1 ? 1 : round($duration);
                                
                                $this->calendarGrid[$hourKey][$day] = [
                                    'course' => $schedule->module->course->name ?? 'Curso',
                                    'teacher' => $schedule->teacher->name ?? 'Sin prof.',
                                    'section' => $schedule->section_name,
                                    'color' => $this->getColorForCourse($schedule->module->course->id ?? 0),
                                    'rowspan' => $rowspan,
                                    'start_real' => $start->format('H:i'),
                                    'end_real' => $end->format('H:i')
                                ];
                            }
                        }
                        $tempStart->addHour();
                    }
                }
            }
        }
    }

    private function getColorForCourse($id)
    {
        $colors = [
            'bg-blue-100 text-blue-700 border-blue-200', 
            'bg-green-100 text-green-700 border-green-200', 
            'bg-purple-100 text-purple-700 border-purple-200', 
            'bg-orange-100 text-orange-700 border-orange-200', 
            'bg-indigo-100 text-indigo-700 border-indigo-200',
            'bg-pink-100 text-pink-700 border-pink-200',
            'bg-teal-100 text-teal-700 border-teal-200'
        ];
        return $colors[$id % count($colors)];
    }
}