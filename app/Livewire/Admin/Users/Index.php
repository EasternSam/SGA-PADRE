<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $userId = null;
    
    // Campos del formulario
    public $name;
    public $email;
    public $password; // Opcional en edición
    public $role;     // ID del rol seleccionado

    public $confirmingDeletion = false;
    public $userToDeleteId = null;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // CORRECCIÓN: Filtrar para EXCLUIR estudiantes y agrupar la búsqueda
        $users = User::with('roles')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Estudiante');
            })
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Obtener todos los roles excepto 'Estudiante' para el select del modal
        $roles = Role::where('name', '!=', 'Estudiante')->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $this->resetInput();
        $this->dispatch('open-modal', 'user-modal');
    }

    private function resetInput()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; // No cargamos la contraseña por seguridad
        
        // Obtener el primer rol asignado (asumiendo un rol principal por usuario)
        $this->role = $user->roles->first()?->name ?? '';

        $this->resetValidation();
        $this->dispatch('open-modal', 'user-modal');
    }

    public function store()
    {
        // Validaciones dinámicas
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'role' => 'required|exists:roles,name',
        ];

        // Contraseña obligatoria solo al crear
        if (!$this->userId) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        // Preparar datos
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // Solo actualizar contraseña si se escribió algo
        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);

        // Sincronizar Rol
        $user->syncRoles([$this->role]);

        session()->flash('message', $this->userId ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');

        $this->dispatch('close-modal', 'user-modal');
        $this->resetInput();
    }

    public function confirmDeletion($id)
    {
        // Evitar que se borre a sí mismo
        if ($id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');
            return;
        }

        $this->userToDeleteId = $id;
        $this->dispatch('open-modal', 'confirm-user-deletion');
    }

    public function delete()
    {
        try {
            $user = User::findOrFail($this->userToDeleteId);
            
            // Verificar si tiene relaciones críticas (ej: es un profesor con clases)
            // Esto se puede expandir según necesidades
            
            $user->delete();
            session()->flash('message', 'Usuario eliminado correctamente.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }

        $this->dispatch('close-modal', 'confirm-user-deletion');
        $this->userToDeleteId = null;
    }
}