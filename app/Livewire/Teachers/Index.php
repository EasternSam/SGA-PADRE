<?php

namespace App\Livewire\Teachers; // <-- CAMBIADO

use Livewire\Component;
use App\Models\User; 
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash; 
// use Spatie\Permission\Models\Role; 

class Index extends Component // <-- CAMBIADO
{
    use WithPagination;

    // Propiedades del formulario (basadas en la tabla Users)
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    // Propiedades de estado
    public $userId = null; 
    public $showModal = false;
    public $search = '';

    /**
     * Reglas de validación que dependen del estado.
     */
    protected function rules()
    {
        // Reglas base
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId, // Valida contra la tabla users
        ];

        // Reglas para crear
        if (!$this->userId) {
            $rules['password'] = 'required|min:8|confirmed';
        } 
        // Reglas para editar (solo si se provee una nueva contraseña)
        elseif (!empty($this->password)) {
            $rules['password'] = 'min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Mensajes de validación personalizados.
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }

    /**
     * Muestra el modal en modo "Crear".
     */
    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Muestra el modal en modo "Editar".
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        // La contraseña se deja vacía a propósito por seguridad
        $this->password = '';
        $this->password_confirmation = '';

        $this->resetValidation();
        $this->showModal = true;
    }

    /**
     * Guarda el profesor (Crea un nuevo Usuario o actualiza uno existente).
     */
    public function save()
    {
        $this->validate();

        // Preparar datos del usuario
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // Solo agregar la contraseña si se proporcionó
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            // Actualizar Usuario
            $user = User::findOrFail($this->userId);
            $user->update($data);
            session()->flash('message', 'Profesor actualizado exitosamente.');
        } else {
            // Crear Usuario
            $user = User::create($data);
            // Asignar el rol de Profesor (asumiendo Spatie/Permission)
            $user->assignRole('Profesor'); 
            session()->flash('message', 'Profesor creado exitosamente.');
        }

        $this->closeModal();
    }

    /**
     * Elimina un usuario (profesor).
     */
    public function delete($id)
    {
        // Opcional: Quizás quieras remover el rol en lugar de borrar el usuario.
        // Por ahora, lo borramos.
        User::findOrFail($id)->delete();
        session()->flash('message', 'Profesor eliminado exitosamente.');
    }

    /**
     * Cierra el modal y resetea el formulario.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Resetea las propiedades del formulario.
     */
    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'userId']);
        $this->resetValidation();
    }

    /**
     * Actualiza la paginación cuando se busca.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Renderiza el componente.
     */
    public function render()
    {
        // Asumiendo que usas Spatie/Permission
        // Busca solo usuarios que tengan el rol 'Profesor'
        $teachers = User::role('Profesor') 
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10); 

        return view('livewire.teachers.index', [ // <-- CAMBIADO
            'teachers' => $teachers,
        ])->layout('layouts.dashboard'); // Asumo que usas un layout 'app.blade.php'
    }
}