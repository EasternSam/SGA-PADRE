<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $userId = null;
    
    public $name;
    public $email;
    public $password;
    public $role;

    public $confirmingDeletion = false;
    public $userToDeleteId = null;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
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
        $this->password = '';
        
        $this->role = $user->roles->first()?->name ?? '';

        $this->resetValidation();
        $this->dispatch('open-modal', 'user-modal');
    }

    public function store()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'role' => 'required|exists:roles,name',
        ];

        if (!$this->userId) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles([$this->role]);

        session()->flash('message', $this->userId ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');

        $this->dispatch('close-modal', 'user-modal');
        $this->resetInput();
    }

    public function confirmDeletion($id)
    {
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
            
            // Protección adicional: No borrar si es super admin (opcional)
            if ($user->hasRole('Admin') && User::role('Admin')->count() <= 1) {
                 throw new \Exception("No puedes eliminar al último administrador.");
            }

            $user->delete();
            session()->flash('message', 'Usuario eliminado correctamente.');

        } catch (QueryException $e) {
            // Código de error SQL 23000 suele ser violación de integridad (Foreign Key)
            if ($e->getCode() == '23000') {
                session()->flash('error', 'No se puede eliminar este usuario porque tiene registros asociados (pagos, clases, etc). Desactívalo en su lugar.');
            } else {
                session()->flash('error', 'Error de base de datos al eliminar usuario.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }

        $this->dispatch('close-modal', 'confirm-user-deletion');
        $this->userToDeleteId = null;
    }
}