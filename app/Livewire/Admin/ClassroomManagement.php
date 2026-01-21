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
    public $weekSchedules = []; // Lista lineal para el sidebar del modal
    
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
            $q->whereDate('end_date', '>=', now()->startOfDay()) 
              ->orderBy('start_time')
              ->with(['module.course', 'teacher']);
        }, 'building'])->find($classroomId);

        // Preparamos la lista lineal para la vista (panel derecho o inferior del modal)
        if ($this->selectedClassroom) {
            $this->weekSchedules = $this->selectedClassroom->schedules;
            $this->generateCalendarGrid();
        } else {
            $this->weekSchedules = [];
            $this->calendarGrid = [];
        }

        $this->showingScheduleModal = true;
        // Importante: Disparar evento para que Alpine abra el modal visualmente
        $this->dispatch('open-modal', 'schedule-view-modal');
    }

    public function closeModal()
    {
        $this->showingScheduleModal = false;
        $this->selectedClassroom = null;
        $this->weekSchedules = [];
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
                    
                    // CORRECCIÓN 2: Normalización robusta de UTF-8 para días con tilde (Miércoles, Sábado)
                    $day = mb_convert_case($dayRaw, MB_CASE_TITLE, "UTF-8");

                    // Rellenar slots de hora
                    $tempStart = $start->copy();
                    
                    // Seguridad para evitar bucles infinitos
                    if ($tempStart >= $end) continue;

                    while ($tempStart < $end) {
                        // Estandarizamos la llave de hora a "HH:00" para la grilla
                        // Esto agrupa los cursos que empiezan ej: 14:15 en el bloque de las 14:00
                        $hourKey = $tempStart->format('H') . ':00'; 
                        
                        // Solo procesamos si la hora está dentro de nuestro rango visual
                        if (in_array($hourKey, $this->timeSlots)) {
                            
                            // Si es la primera hora del bloque O la celda está vacía
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
                        
                        // Avanzamos 1 hora para marcar el siguiente bloque si es necesario
                        // (Nota: En este enfoque simple usando rowspan, el while podría optimizarse, 
                        // pero lo dejamos así para asegurar que recorra el tiempo correctamente).
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