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
    
    // Inicializamos como array vacío o colección para evitar "Undefined variable"
    public $weekSchedules = []; 
    
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
        
        // Aseguramos que sea una colección vacía al inicio
        $this->weekSchedules = collect();
    }

    public function render()
    {
        // Cargamos todos los edificios y aulas
        $buildings = Building::with('classrooms')->get();

        return view('livewire.admin.classroom-management', [
            'buildings' => $buildings
        ]);
    }

    public function showSchedule($classroomId)
    {
        // CORRECCIÓN 1: Usar whereDate para incluir cursos que terminan hoy
        $this->selectedClassroom = Classroom::with(['schedules' => function($q) {
            $q->whereNull('deleted_at') // Filtro de soft deletes
              ->whereDate('end_date', '>=', now()->startOfDay()) 
              ->orderBy('start_time')
              ->with(['module.course', 'teacher']);
        }, 'building'])->find($classroomId);

        // Preparamos la lista lineal para la vista
        if ($this->selectedClassroom) {
            $this->weekSchedules = $this->selectedClassroom->schedules;
            // Generamos también la grilla por si decidimos usarla, aunque ahora usamos la lista
            $this->generateCalendarGrid();
        } else {
            $this->weekSchedules = collect(); // Colección vacía
            $this->calendarGrid = [];
        }

        $this->showingScheduleModal = true;
        $this->dispatch('open-modal', 'schedule-view-modal');
    }

    public function closeModal()
    {
        $this->showingScheduleModal = false;
        $this->selectedClassroom = null;
        $this->weekSchedules = collect(); // Reset a colección vacía
        $this->calendarGrid = [];
        $this->dispatch('close-modal', 'schedule-view-modal');
    }

    /**
     * Construye una matriz [Hora][Día] = Info del Curso para el calendario visual
     */
    private function generateCalendarGrid()
    {
        $this->calendarGrid = [];

        if (!$this->selectedClassroom || $this->selectedClassroom->schedules->isEmpty()) {
            return;
        }

        foreach ($this->selectedClassroom->schedules as $schedule) {
            
            if (!$schedule->start_time || !$schedule->end_time) continue;

            try {
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
            } catch (\Exception $e) {
                continue;
            }
            
            // Recorrer los días que toca este curso
            if (is_array($schedule->days_of_week)) {
                foreach ($schedule->days_of_week as $dayRaw) {
                    
                    // Normalización robusta de UTF-8
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