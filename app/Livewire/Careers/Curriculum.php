<?php

namespace App\Livewire\Careers;

use App\Models\Course;
use App\Models\Module;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.dashboard')]
class Curriculum extends Component
{
    public Course $career;
    
    // Colección de materias para la vista
    public $modulesByPeriod;

    // Estado del Modal
    public $showModuleModal = false;
    public $modalTitle = '';
    public $moduleId = null;

    // Campos del Formulario de Materia
    public $name = '';
    public $code = '';
    public $credits = 0;
    public $period_number = 1;
    public $is_elective = false;
    public $description = '';
    
    // Prerrequisitos
    public $selectedPrerequisites = []; // IDs de las materias seleccionadas
    public $availablePrerequisites = []; // Lista de materias disponibles para ser requisito

    public function mount(Course $career)
    {
        // Validar que sea una carrera universitaria
        if ($career->program_type !== 'degree') {
            abort(404, 'Este curso no es una carrera universitaria.');
        }
        $this->career = $career;
        $this->loadCurriculum();
    }

    public function loadCurriculum()
    {
        // Cargamos los módulos con sus prerrequisitos
        $modules = $this->career->modules()
            ->with('prerequisites')
            ->orderBy('period_number')
            ->orderBy('order')
            ->get();

        // Agrupamos por periodo (Cuatrimestre 1, 2, etc.)
        $this->modulesByPeriod = $modules->groupBy('period_number');
    }

    public function render()
    {
        return view('livewire.careers.curriculum');
    }

    // --- Acciones del Modal ---

    public function openCreateModal($period = 1)
    {
        $this->resetInput();
        $this->period_number = $period; // Pre-seleccionar el periodo desde donde se hizo clic
        $this->modalTitle = 'Nueva Asignatura';
        $this->loadAvailablePrerequisites();
        $this->showModuleModal = true;
        $this->dispatch('open-modal', 'module-form-modal');
    }

    public function edit($id)
    {
        $module = Module::with('prerequisites')->findOrFail($id);
        
        $this->moduleId = $module->id;
        $this->name = $module->name;
        $this->code = $module->code;
        $this->credits = $module->credits;
        $this->period_number = $module->period_number;
        $this->is_elective = (bool)$module->is_elective;
        $this->description = $module->description;
        
        // Cargar IDs de prerrequisitos actuales
        $this->selectedPrerequisites = $module->prerequisites->pluck('id')->map(fn($id) => (string)$id)->toArray();
        
        $this->loadAvailablePrerequisites();
        $this->modalTitle = 'Editar Asignatura: ' . $module->code;
        $this->showModuleModal = true;
        $this->dispatch('open-modal', 'module-form-modal');
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:20', 
                Rule::unique('modules')->where(function ($query) {
                    return $query->where('course_id', $this->career->id);
                })->ignore($this->moduleId)
            ],
            'credits' => 'required|integer|min:0',
            'period_number' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'selectedPrerequisites' => 'array',
            'selectedPrerequisites.*' => 'exists:modules,id'
        ];

        $this->validate($rules);

        try {
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
                    // Calcular orden automático al final del periodo
                    $lastOrder = Module::where('course_id', $this->career->id)
                        ->where('period_number', $this->period_number)
                        ->max('order');
                    $data['order'] = $lastOrder ? $lastOrder + 1 : 1;
                    
                    $module = Module::create($data);
                }

                // Sincronizar Prerrequisitos
                $module->prerequisites()->sync($this->selectedPrerequisites);
            });

            $this->loadCurriculum(); // Recargar lista
            $this->closeModal();
            $this->dispatch('notify', message: 'Asignatura guardada correctamente.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error al guardar: ' . $e->getMessage(), type: 'error');
        }
    }

    public function delete($id)
    {
        try {
            $module = Module::findOrFail($id);
            
            // Validar si es prerrequisito de alguien más antes de borrar
            if ($module->requiredFor()->exists()) {
                $this->dispatch('notify', message: 'No se puede eliminar: Esta materia es prerrequisito de otras.', type: 'error');
                return;
            }

            $module->delete();
            $this->loadCurriculum();
            $this->dispatch('notify', message: 'Asignatura eliminada.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error al eliminar.', type: 'error');
        }
    }

    // --- Helpers ---

    private function loadAvailablePrerequisites()
    {
        // Obtener todas las materias de la carrera EXCEPTO la actual (para evitar ciclos)
        $query = Module::where('course_id', $this->career->id);
        
        if ($this->moduleId) {
            $query->where('id', '!=', $this->moduleId);
        }

        $this->availablePrerequisites = $query->orderBy('period_number')->orderBy('code')->get();
    }

    private function resetInput()
    {
        $this->moduleId = null;
        $this->name = '';
        $this->code = '';
        $this->credits = 0;
        // $this->period_number NO se resetea para mantener contexto al crear varias en el mismo ciclo
        $this->is_elective = false;
        $this->description = '';
        $this->selectedPrerequisites = [];
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModuleModal = false;
        $this->dispatch('close-modal', 'module-form-modal');
    }
}