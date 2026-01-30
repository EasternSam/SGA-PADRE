<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\CourseSchedule; // Importar CourseSchedule
use App\Models\ClassroomReservation; // Importar Modelo de Reserva
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.dashboard')]
class ClassroomManagement extends Component
{
    public $selectedClassroom = null;
    public $showingScheduleModal = false;
    
    // Variables para el Modal de Crear Reserva
    public $showingReservationModal = false;
    public $reservation_classroom_id = null;
    public $reservation_title = '';
    public $reservation_description = '';
    public $reservation_date = '';
    public $reservation_start_time = '';
    public $reservation_end_time = '';

    // Variables para el Modal de Crear/Editar Aula
    public $showingClassroomModal = false;
    public $classroom_id = null; // Para edición si se requiere en el futuro
    public $classroom_name = '';
    public $classroom_capacity = 0;
    public $classroom_pc_count = 0;
    public $classroom_type = 'Aula';
    public $classroom_building_id = null;
    public $classroom_has_tv = false; // Nueva propiedad para TV
    public $classroom_is_active = true;

    // Variables para el Modal de Crear Edificio
    public $showingBuildingModal = false;
    public $new_building_name = '';
    
    // Inicializamos como array vacío o colección para evitar "Undefined variable"
    public $weekSchedules = []; 
    public $upcomingReservations = []; // Para mostrar reservas futuras
    
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
        $this->upcomingReservations = collect();
    }

    public function render()
    {
        // Cargamos todos los edificios y aulas
        $buildings = Building::with(['classrooms' => function($q) {
            $q->with('reservations'); // Eager loading para optimizar isOccupiedNow
        }])->get();

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
        }, 'reservations' => function($q) {
            // Cargar reservas futuras para mostrar en lista aparte
            $q->where('reserved_date', '>=', now()->toDateString())
              ->orderBy('reserved_date')
              ->orderBy('start_time');
        }, 'building'])->find($classroomId);

        // Preparamos la lista lineal para la vista
        if ($this->selectedClassroom) {
            $this->weekSchedules = $this->selectedClassroom->schedules;
            $this->upcomingReservations = $this->selectedClassroom->reservations;
            // Generamos también la grilla por si decidimos usarla, aunque ahora usamos la lista
            $this->generateCalendarGrid();
        } else {
            $this->weekSchedules = collect(); // Colección vacía
            $this->upcomingReservations = collect();
            $this->calendarGrid = [];
        }

        $this->showingScheduleModal = true;
        $this->dispatch('open-modal', 'schedule-view-modal');
    }

    public function closeModal()
    {
        $this->showingScheduleModal = false;
        $this->showingReservationModal = false;
        $this->showingClassroomModal = false;
        $this->showingBuildingModal = false;
        
        $this->selectedClassroom = null;
        $this->weekSchedules = collect(); // Reset a colección vacía
        $this->upcomingReservations = collect();
        $this->calendarGrid = [];
        
        $this->resetReservationForm();
        $this->resetClassroomForm();
        $this->resetBuildingForm();
        
        $this->dispatch('close-modal', 'schedule-view-modal');
        $this->dispatch('close-modal', 'reservation-modal');
        $this->dispatch('close-modal', 'classroom-modal');
        $this->dispatch('close-modal', 'building-modal');
    }

    // --- LÓGICA DE EDIFICIOS (CRUD) ---

    public function openBuildingModal()
    {
        $this->resetBuildingForm();
        $this->showingBuildingModal = true;
        $this->dispatch('open-modal', 'building-modal');
    }

    public function storeBuilding()
    {
        $this->validate([
            'new_building_name' => 'required|string|max:255|unique:buildings,name',
        ]);

        Building::create([
            'name' => $this->new_building_name
        ]);

        session()->flash('message', 'Edificio registrado correctamente.');
        $this->closeModal();
    }

    private function resetBuildingForm()
    {
        $this->new_building_name = '';
        $this->resetErrorBag();
    }

    // --- LÓGICA DE AULAS (CRUD) ---

    public function openClassroomModal()
    {
        $this->resetClassroomForm();
        // Por defecto seleccionamos el primer edificio si no hay uno seleccionado (opcional)
        $firstBuilding = Building::first();
        if ($firstBuilding) {
            $this->classroom_building_id = $firstBuilding->id;
        }
        $this->showingClassroomModal = true;
        $this->dispatch('open-modal', 'classroom-modal');
    }

    public function storeClassroom()
    {
        $this->validate([
            'classroom_name' => 'required|string|max:255',
            'classroom_capacity' => 'required|integer|min:1',
            'classroom_pc_count' => 'required|integer|min:0',
            'classroom_type' => 'required|in:Aula,Laboratorio,Auditorio',
            'classroom_building_id' => 'required|exists:buildings,id',
            'classroom_has_tv' => 'boolean',
        ]);

        // Procesar equipamiento
        $equipment = [];
        if ($this->classroom_has_tv) {
            $equipment[] = 'TV';
        }

        Classroom::create([
            'name' => $this->classroom_name,
            'capacity' => $this->classroom_capacity,
            'pc_count' => $this->classroom_pc_count,
            'type' => $this->classroom_type,
            'building_id' => $this->classroom_building_id,
            'equipment' => !empty($equipment) ? json_encode($equipment) : null,
            'is_active' => true,
        ]);

        session()->flash('message', 'Aula creada exitosamente.');
        $this->closeModal();
    }

    public function deleteClassroom($id)
    {
        $classroom = Classroom::find($id);

        if ($classroom) {
            // Opcional: Verificar si tiene horarios activos antes de borrar
            // if ($classroom->schedules()->exists()) { ... error ... }

            $classroom->delete();
            session()->flash('message', 'Aula eliminada correctamente.');
        }
    }

    private function resetClassroomForm()
    {
        $this->classroom_id = null;
        $this->classroom_name = '';
        $this->classroom_capacity = 0;
        $this->classroom_pc_count = 0;
        $this->classroom_type = 'Aula';
        $this->classroom_building_id = null;
        $this->classroom_has_tv = false;
        $this->resetErrorBag();
    }

    // --- LÓGICA DE RESERVAS ---

    public function openReservationModal($classroomId)
    {
        $this->resetReservationForm();
        $this->reservation_classroom_id = $classroomId;
        $this->selectedClassroom = Classroom::find($classroomId); // Necesario para mostrar el nombre en el modal
        $this->showingReservationModal = true;
        $this->dispatch('open-modal', 'reservation-modal');
    }

    public function createReservation()
    {
        $this->validate([
            'reservation_title' => 'required|string|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_start_time' => 'required',
            'reservation_end_time' => 'required|after:reservation_start_time',
        ], [
            'reservation_end_time.after' => 'La hora de fin debe ser posterior a la de inicio.'
        ]);

        // 1. Verificar colisión con OTRAS RESERVAS (No permitido)
        $conflictingReservation = ClassroomReservation::where('classroom_id', $this->reservation_classroom_id)
            ->where('reserved_date', $this->reservation_date)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->reservation_start_time, $this->reservation_end_time])
                      ->orWhereBetween('end_time', [$this->reservation_start_time, $this->reservation_end_time])
                      ->orWhere(function($q) {
                          $q->where('start_time', '<=', $this->reservation_start_time)
                            ->where('end_time', '>=', $this->reservation_end_time);
                      });
            })
            ->exists();

        if ($conflictingReservation) {
            $this->addError('reservation_start_time', 'Ya existe una reserva especial para este horario. Por favor verifique.');
            return;
        }

        // 2. Verificar colisión con MATERIAS (Permitido, pero notificamos)
        // Convertimos fecha a día de la semana en español
        $dayName = ucfirst(Carbon::parse($this->reservation_date)->locale('es')->dayName);
        
        $conflictingClass = CourseSchedule::where('classroom_id', $this->reservation_classroom_id)
            ->whereNull('deleted_at')
            ->where('start_date', '<=', $this->reservation_date)
            ->where('end_date', '>=', $this->reservation_date)
            ->whereJsonContains('days_of_week', $dayName)
            ->where(function ($query) {
                $query->where('start_time', '<', $this->reservation_end_time)
                      ->where('end_time', '>', $this->reservation_start_time);
            })
            ->with('module.course')
            ->first();

        // Crear la reserva
        ClassroomReservation::create([
            'classroom_id' => $this->reservation_classroom_id,
            'title' => $this->reservation_title,
            'description' => $this->reservation_description,
            'reserved_date' => $this->reservation_date,
            'start_time' => $this->reservation_start_time,
            'end_time' => $this->reservation_end_time,
            'created_by' => Auth::id(),
        ]);

        $message = 'Reserva creada con éxito.';
        
        if ($conflictingClass) {
            $courseName = $conflictingClass->module->course->name ?? 'Materia';
            $message .= " Nota: Esta reserva ocupará el lugar de la clase '$courseName' para el día seleccionado.";
        }

        session()->flash('message', $message);
        $this->closeModal();
        
        // Si estábamos viendo el horario de esa aula, actualizarlo
        if ($this->selectedClassroom && $this->selectedClassroom->id == $this->reservation_classroom_id) {
            $this->showSchedule($this->reservation_classroom_id);
        }
    }
    
    public function deleteReservation($reservationId)
    {
        $reservation = ClassroomReservation::find($reservationId);
        if ($reservation) {
            $reservation->delete();
            session()->flash('message', 'Reserva eliminada. El horario vuelve a la normalidad.');
            
            // Recargar vista
            if ($this->selectedClassroom) {
                $this->showSchedule($this->selectedClassroom->id);
            }
        }
    }

    private function resetReservationForm()
    {
        $this->reservation_title = '';
        $this->reservation_description = '';
        $this->reservation_date = '';
        $this->reservation_start_time = '';
        $this->reservation_end_time = '';
        $this->resetErrorBag();
    }

    /**
     * Desvincular un aula de una sección (CourseSchedule)
     */
    public function detachClassroom($scheduleId)
    {
        $schedule = CourseSchedule::find($scheduleId);

        if ($schedule) {
            $schedule->classroom_id = null;
            $schedule->save();
            
            // Recargar los horarios del aula seleccionada para reflejar cambios
            if ($this->selectedClassroom) {
                $this->showSchedule($this->selectedClassroom->id);
            }
            
            session()->flash('message', 'Aula desvinculada correctamente.');
        }
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