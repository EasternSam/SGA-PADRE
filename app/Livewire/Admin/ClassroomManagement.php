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
    public $daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public function mount()
    {
        // Generar slots de tiempo de 8:00 AM a 10:00 PM (cada hora)
        $start = Carbon::createFromTime(8, 0);
        $end = Carbon::createFromTime(22, 0);
        
        while ($start < $end) {
            $this->timeSlots[] = $start->format('H:i');
            $start->addHour();
        }
    }

    public function render()
    {
        // Cargamos edificios con aulas y sus horarios ACTIVOS (futuros o presentes)
        $buildings = Building::with(['classrooms.schedules' => function($q) {
            $q->where('end_date', '>=', now())
              ->with(['module.course', 'teacher']);
        }])->get();

        return view('livewire.admin.classroom-management', [
            'buildings' => $buildings
        ]);
    }

    public function showSchedule($classroomId)
    {
        $this->selectedClassroom = Classroom::with(['schedules' => function($q) {
            $q->where('end_date', '>=', now())
              ->with(['module.course', 'teacher']);
        }, 'building'])->find($classroomId);

        $this->generateCalendarGrid();

        $this->showingScheduleModal = true;
        // Importante: Disparar evento para que Alpine abra el modal visualmente
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

        if (!$this->selectedClassroom) return;

        foreach ($this->selectedClassroom->schedules as $schedule) {
            
            $start = Carbon::parse($schedule->start_time);
            $end = Carbon::parse($schedule->end_time);
            
            // Recorrer los días que toca este curso
            foreach ($schedule->days_of_week as $day) {
                // Normalizar día (Asegurar capitalización)
                $day = ucfirst(strtolower($day));

                // Marcar las horas ocupadas en la grilla
                $tempStart = $start->copy();
                while ($tempStart < $end) {
                    $hourKey = $tempStart->format('H:i');
                    
                    // Solo guardamos el inicio del bloque para pintar la tarjeta completa
                    // O guardamos referencia en cada slot si queremos celdas individuales
                    $this->calendarGrid[$hourKey][$day] = [
                        'course' => $schedule->module->course->name ?? 'N/A',
                        'teacher' => $schedule->teacher->name ?? 'Sin prof.',
                        'color' => $this->getColorForCourse($schedule->module->course->id ?? 0),
                        'rowspan' => $start->diffInHours($end) ?: 1
                    ];

                    // Avanzamos 1 hora
                    $tempStart->addHour();
                }
            }
        }
    }

    private function getColorForCourse($id)
    {
        $colors = ['bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700', 'bg-indigo-100 text-indigo-700'];
        return $colors[$id % count($colors)];
    }
}