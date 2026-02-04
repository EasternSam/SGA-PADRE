<?php

namespace App\Livewire\Careers;

use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $careerId = null;

    // Campos del formulario (adaptados para Carreras Universitarias)
    public $name;
    public $code;
    public $description;
    public $total_credits;
    public $duration_periods;
    public $degree_title;
    public $registration_fee;
    public $monthly_fee;
    public $credit_price; // <-- NUEVO: Precio por crédito
    public $is_sequential = false;
    public $status = 'Activo';

    public $confirmingDeletion = false;
    public $careerToDeleteId = null;
    
    public $modalTitle = '';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Filtramos solo los registros que son de tipo 'degree' (Carreras Universitarias)
        $careers = Course::where('program_type', 'degree')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.careers.index', [
            'careers' => $careers,
        ]);
    }

    public function create()
    {
        $this->resetInput();
        $this->modalTitle = 'Nueva Carrera Universitaria';
        $this->dispatch('open-modal', 'career-form-modal');
    }

    public function edit($id)
    {
        $career = Course::findOrFail($id);

        $this->careerId = $id;
        $this->name = $career->name;
        $this->code = $career->code;
        $this->description = $career->description;
        $this->total_credits = $career->total_credits;
        $this->duration_periods = $career->duration_periods;
        $this->degree_title = $career->degree_title;
        $this->registration_fee = $career->registration_fee;
        $this->monthly_fee = $career->monthly_fee;
        $this->credit_price = $career->credit_price; // <-- Cargar valor
        $this->is_sequential = (bool)$career->is_sequential;
        $this->status = $career->status;

        $this->modalTitle = 'Editar Carrera';
        $this->resetValidation();
        $this->dispatch('open-modal', 'career-form-modal');
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('courses')->ignore($this->careerId)],
            'description' => 'nullable|string',
            'total_credits' => 'required|integer|min:0',
            'duration_periods' => 'required|integer|min:1', // Cuatrimestres
            'degree_title' => 'required|string|max:255',
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
            'credit_price' => 'required|numeric|min:0', // <-- Validación
            'status' => 'required|in:Activo,Inactivo',
        ];

        $this->validate($rules);

        $data = [
            'program_type' => 'degree', // Forzamos tipo Universidad
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'total_credits' => $this->total_credits,
            'duration_periods' => $this->duration_periods,
            'degree_title' => $this->degree_title,
            'registration_fee' => $this->registration_fee,
            'monthly_fee' => $this->monthly_fee,
            'credit_price' => $this->credit_price, // <-- Guardar valor
            'is_sequential' => $this->is_sequential, // Generalmente true para carreras
            'status' => $this->status,
        ];

        Course::updateOrCreate(['id' => $this->careerId], $data);

        session()->flash('message', $this->careerId ? 'Carrera actualizada correctamente.' : 'Carrera creada correctamente.');
        $this->closeModal(); // Usamos el método interno para cerrar y limpiar
    }

    // --- MODIFICADO PARA BORRADO EN CASCADA COMPLETO ---
    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $career = Course::findOrFail($id);
                
                // 1. Obtener todos los módulos (materias) de la carrera
                $modules = $career->modules;

                foreach ($modules as $module) {
                    // A. Eliminar relaciones de prerrequisitos (tabla pivote)
                    // Primero detach de donde este módulo es requisito
                    $module->requiredFor()->detach();
                    // Segundo detach de los requisitos que tiene este módulo
                    $module->prerequisites()->detach();

                    // B. Eliminar Secciones (Horarios) asociadas
                    // Si tienes inscripciones (Enrollments), deberías decidir si borrarlas o bloquear el borrado.
                    // Aquí asumimos borrado fuerte, pero si hay inscripciones activas, CourseSchedule podría tener restricción.
                    // Para limpieza total, borramos schedules.
                    foreach ($module->schedules as $schedule) {
                        // Opcional: Borrar inscripciones si es necesario
                        // $schedule->enrollments()->delete();
                        $schedule->delete();
                    }

                    // C. Eliminar el módulo
                    $module->delete();
                }

                // 2. Eliminar la carrera
                $career->delete();
            });

            session()->flash('message', 'Carrera y todos sus datos asociados (materias, horarios) eliminados correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error crítico al eliminar: ' . $e->getMessage());
        }
    }

    // --- ESTE ES EL MÉTODO QUE FALTABA O ESTABA PRIVADO ---
    public function closeModal() 
    {
        $this->dispatch('close-modal', 'career-form-modal');
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->careerId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->total_credits = 0;
        $this->duration_periods = 12; // Default 12 cuatrimestres (4 años)
        $this->degree_title = '';
        $this->registration_fee = 0;
        $this->monthly_fee = 0;
        $this->credit_price = 0; // <-- Reset
        $this->is_sequential = true;
        $this->status = 'Activo';
        $this->modalTitle = '';
        $this->resetValidation();
    }
}