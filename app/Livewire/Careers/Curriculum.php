<?php

namespace App\Livewire\Careers;

use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\User;
use App\Models\Classroom;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.dashboard')]
class Curriculum extends Component
{
    public Course $career;
    
    // Datos Maestros
    public $modulesByPeriod;
    public $teachers;
    public $classrooms;

    // --- MODAL ASIGNATURA (Materia) ---
    public $showModuleModal = false;
    public $modalModuleTitle = '';
    public $moduleId = null;
    
    // Campos Materia
    public $name = '';
    public $code = '';
    public $credits = 3;
    public $period_number = 1;
    public $is_elective = false;
    public $description = '';
    public $selectedPrerequisites = []; 
    public $availablePrerequisites = [];

    // --- MODAL HORARIOS (Secciones) ---
    public $showScheduleModal = false;
    public $selectedModuleForSchedule = null;
    public $scheduleId = null;
    public $moduleSchedules = [];

    // Campos Horario
    public $s_day_of_week = 'Lunes';
    public $s_start_time = '18:00';
    public $s_end_time = '20:00';
    public $s_teacher_id = '';
    public $s_classroom_id = '';
    public $s_modality = 'Presencial';
    public $s_start_date;
    public $s_end_date;
    public $s_section_name = 'Sec-01'; // Nuevo campo para diferenciar secciones

    public function mount(Course $career)
    {
        if ($career->program_type !== 'degree') {
            return redirect()->route('admin.courses.index')->with('error', 'Este curso no es una carrera universitaria.');
        }
        $this->career = $career;
        $this->loadResources();
        $this->loadCurriculum();
    }

    public function loadResources()
    {
        // Cargar profesores y aulas para los selectores de horarios
        $this->teachers = User::role('Profesor')->orderBy('name')->get();
        $this->classrooms = Classroom::where('status', 'Activo')->orderBy('name')->get();
    }

    public function loadCurriculum()
    {
        $modules = $this->career->modules()
            ->with(['prerequisites', 'schedules']) // Cargar horarios también para mostrar contador
            ->orderBy('period_number')
            ->orderBy('order')
            ->get();

        $this->modulesByPeriod = $modules->groupBy('period_number');
    }

    public function render()
    {
        return view('livewire.careers.curriculum');
    }

    // =================================================================
    // GESTIÓN DE MATERIAS (MÓDULOS)
    // =================================================================

    public function openCreateModule($period = 1)
    {
        $this->resetModuleInput();
        $this->period_number = $period;
        $this->modalModuleTitle = 'Nueva Asignatura';
        $this->loadAvailablePrerequisites();
        $this->showModuleModal = true;
        $this->dispatch('open-modal', 'module-form-modal');
    }

    public function editModule($id)
    {
        $module = Module::with('prerequisites')->findOrFail($id);
        
        $this->moduleId = $module->id;
        $this->name = $module->name;
        $this->code = $module->code;
        $this->credits = $module->credits;
        $this->period_number = $module->period_number;
        $this->is_elective = (bool)$module->is_elective;
        $this->description = $module->description;
        
        // Convertir IDs a strings para el select múltiple si es necesario
        $this->selectedPrerequisites = $module->prerequisites->pluck('id')->toArray();
        
        $this->loadAvailablePrerequisites();
        $this->modalModuleTitle = 'Editar: ' . $module->code;
        $this->showModuleModal = true;
        $this->dispatch('open-modal', 'module-form-modal');
    }

    public function saveModule()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('modules')->where(fn($q) => $q->where('course_id', $this->career->id))->ignore($this->moduleId)],
            'credits' => 'required|integer|min:0',
            'period_number' => 'required|integer|min:1',
            'selectedPrerequisites' => 'array',
        ]);

        DB::transaction(function () {
            $data = [
                'course_id' => $this->career->id,
                'name' => $this->name,
                'code' => $this->code,
                'credits' => $this->credits,
                'period_number' => $this->period_number,
                'is_elective' => $this->is_elective,
                'description' => $this->description,
                'status' => 'Activo',
            ];

            if ($this->moduleId) {
                $module = Module::findOrFail($this->moduleId);
                $module->update($data);
            } else {
                // Auto-orden
                $lastOrder = Module::where('course_id', $this->career->id)
                    ->where('period_number', $this->period_number)
                    ->max('order');
                $data['order'] = $lastOrder ? $lastOrder + 1 : 1;
                $module = Module::create($data);
            }

            $module->prerequisites()->sync($this->selectedPrerequisites);
        });

        $this->loadCurriculum();
        $this->closeModuleModal();
        $this->dispatch('notify', message: 'Asignatura guardada.', type: 'success');
    }

    public function deleteModule($id)
    {
        try {
            $module = Module::findOrFail($id);
            if ($module->requiredFor()->exists()) {
                $this->dispatch('notify', message: 'No se puede eliminar: Es prerrequisito de otras materias.', type: 'error');
                return;
            }
            $module->delete();
            $this->loadCurriculum();
            $this->dispatch('notify', message: 'Asignatura eliminada.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error al eliminar.', type: 'error');
        }
    }

    // =================================================================
    // GESTIÓN DE HORARIOS (SECCIONES)
    // =================================================================

    public function openScheduleModal($moduleId)
    {
        $this->selectedModuleForSchedule = Module::findOrFail($moduleId);
        $this->loadModuleSchedules();
        $this->resetScheduleInput();
        $this->showScheduleModal = true;
        $this->dispatch('open-modal', 'schedule-management-modal');
    }

    public function loadModuleSchedules()
    {
        if ($this->selectedModuleForSchedule) {
            $this->moduleSchedules = CourseSchedule::with(['teacher', 'classroom'])
                ->where('module_id', $this->selectedModuleForSchedule->id)
                ->orderBy('start_date', 'desc')
                ->get();
        }
    }

    public function saveSchedule()
    {
        $this->validate([
            's_section_name' => 'required|string|max:50',
            's_day_of_week' => 'required',
            's_start_time' => 'required',
            's_end_time' => 'required|after:s_start_time',
            's_teacher_id' => 'required|exists:users,id',
            's_start_date' => 'required|date',
            's_end_date' => 'required|date|after_or_equal:s_start_date',
        ]);

        $data = [
            'module_id' => $this->selectedModuleForSchedule->id,
            'teacher_id' => $this->s_teacher_id,
            'classroom_id' => $this->s_classroom_id ?: null,
            'section_name' => $this->s_section_name,
            'day_of_week' => $this->s_day_of_week,
            'start_time' => $this->s_start_time,
            'end_time' => $this->s_end_time,
            'modality' => $this->s_modality,
            'start_date' => $this->s_start_date,
            'end_date' => $this->s_end_date,
            'status' => 'Activo',
        ];

        if ($this->scheduleId) {
            CourseSchedule::find($this->scheduleId)->update($data);
            $msg = 'Horario actualizado.';
        } else {
            CourseSchedule::create($data);
            $msg = 'Sección creada exitosamente.';
        }

        $this->loadModuleSchedules(); // Recargar lista interna del modal
        $this->loadCurriculum(); // Recargar contadores vista principal
        $this->resetScheduleInput(); // Limpiar formulario para agregar otro
        $this->dispatch('notify', message: $msg, type: 'success');
    }

    public function editSchedule($id)
    {
        $schedule = CourseSchedule::findOrFail($id);
        $this->scheduleId = $id;
        $this->s_section_name = $schedule->section_name;
        $this->s_day_of_week = $schedule->day_of_week;
        $this->s_start_time = $schedule->start_time;
        $this->s_end_time = $schedule->end_time;
        $this->s_teacher_id = $schedule->teacher_id;
        $this->s_classroom_id = $schedule->classroom_id;
        $this->s_modality = $schedule->modality;
        $this->s_start_date = $schedule->start_date;
        $this->s_end_date = $schedule->end_date;
    }

    public function deleteSchedule($id)
    {
        CourseSchedule::destroy($id);
        $this->loadModuleSchedules();
        $this->loadCurriculum();
        $this->dispatch('notify', message: 'Horario eliminado.', type: 'success');
    }

    // =================================================================
    // HELPERS
    // =================================================================

    private function loadAvailablePrerequisites()
    {
        // Solo mostrar materias de periodos ANTERIORES para evitar lógica circular
        $query = Module::where('course_id', $this->career->id);
        
        if ($this->period_number > 1) {
             $query->where('period_number', '<', $this->period_number);
        } else {
             // Si es periodo 1, no debería tener prerequisitos (normalmente)
             $this->availablePrerequisites = [];
             return;
        }

        $this->availablePrerequisites = $query->orderBy('period_number')->orderBy('code')->get();
    }

    public function updatedPeriodNumber()
    {
        // Si cambia el periodo en el form, recargar prerequisitos válidos
        $this->loadAvailablePrerequisites();
    }

    private function resetModuleInput()
    {
        $this->moduleId = null;
        $this->name = '';
        $this->code = '';
        $this->credits = 3;
        $this->is_elective = false;
        $this->description = '';
        $this->selectedPrerequisites = [];
        $this->resetValidation();
    }

    private function resetScheduleInput()
    {
        $this->scheduleId = null;
        $this->s_section_name = 'Sec-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        $this->s_day_of_week = 'Lunes';
        $this->s_start_time = '18:00';
        $this->s_end_time = '20:00';
        $this->s_teacher_id = '';
        $this->s_classroom_id = '';
        $this->s_modality = 'Presencial';
        // Fechas por defecto (ej. inicio de mes actual)
        $this->s_start_date = now()->format('Y-m-d');
        $this->s_end_date = now()->addMonths(4)->format('Y-m-d');
        $this->resetValidation();
    }

    public function closeModuleModal()
    {
        $this->showModuleModal = false;
        $this->dispatch('close-modal', 'module-form-modal');
    }
}