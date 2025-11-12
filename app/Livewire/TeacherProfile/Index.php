<?php

namespace App\Livewire\TeacherProfile;

use Livewire\Component;
use App\Models\User;
use App\Models\CourseSchedule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')] // Asumiendo 'layouts.dashboard'
class Index extends Component
{
    use WithPagination;

    public User $teacher; // Recibe el User (profesor)

    // Propiedades para el modal de edición (copiadas de ManageTeachers)
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $userId = null; 
    public $showModal = false;

    public function mount(User $teacher)
    {
        // Verificamos que el usuario sea un profesor
        if (!$teacher->hasRole('Profesor')) {
            abort(404, 'Usuario no encontrado o no es un profesor.');
        }
        $this->teacher = $teacher;
    }

    /**
     * Reglas de validación (para el modal de edición).
     */
    protected function rules()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ];
        if (!empty($this->password)) {
            $rules['password'] = 'min:8|confirmed';
        }
        return $rules;
    }

    /**
     * Muestra el modal en modo "Editar".
     */
    public function edit()
    {
        $this->userId = $this->teacher->id;
        $this->name = $this->teacher->name;
        $this->email = $this->teacher->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    /**
     * Guarda los cambios del profesor (Usuario).
     */
    public function save()
    {
        $this->validate();
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::findOrFail($this->userId);
        $user->update($data);
        
        session()->flash('message', 'Profesor actualizado exitosamente.');
        $this->closeModal();
        $this->teacher = $user->fresh(); // Refresca los datos del profesor en la página
    }

    /**
     * Cierra el modal y resetea el formulario.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'userId']);
        $this->resetValidation();
    }


    public function render()
    {
        // --- ¡¡¡ESTA ES LA CORRECCIÓN IDEAL!!! ---
        // Usamos la relación 'schedules()' que SÍ existe en tu User.php
        // y la asignamos a la variable '$sections' que la vista espera.
        $sections = $this->teacher->schedules() // <-- Usamos el nombre correcto de tu modelo
            ->with('module.course')
            ->orderBy('start_date', 'desc')
            ->paginate(10, ['*'], 'sectionsPage');

        return view('livewire.teacher-profile.index', [
            'sections' => $sections, // <-- Mantenemos este nombre para la vista
        ]);
    }
}