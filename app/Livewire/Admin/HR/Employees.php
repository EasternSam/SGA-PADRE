<?php
namespace App\Livewire\Admin\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.dashboard')]
class Employees extends Component
{
    use WithPagination;

    public $search = '';
    
    // Modal state
    public $employeeId;
    
    // User creation / role fields
    public $is_new_user = false;
    public $new_user_name = '';
    public $new_user_email = '';
    public $new_user_password = '';
    public $role_name = '';

    // HR Form fields
    public $user_id;
    public $biometric_id;
    public $position;
    public $department;
    public $contract_type = 'Mensual';
    public $base_salary = 0;
    public $hourly_rate = 0;
    public $hire_date;
    public $status = 'Activo';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->dispatch('open-modal', 'employee-modal');
    }

    public function edit($id)
    {
        $employee = Employee::with('user.roles')->findOrFail($id);
        $this->employeeId = $id;
        
        $this->is_new_user = false;
        $this->user_id = $employee->user_id;
        $this->role_name = optional($employee->user)->roles->first()?->name ?? '';
        
        $this->biometric_id = $employee->biometric_id;
        $this->position = $employee->position;
        $this->department = $employee->department;
        $this->contract_type = $employee->contract_type;
        $this->base_salary = $employee->base_salary;
        $this->hourly_rate = $employee->hourly_rate;
        $this->hire_date = $employee->hire_date ? $employee->hire_date->format('Y-m-d') : null;
        $this->status = $employee->status;
        
        $this->dispatch('open-modal', 'employee-modal');
    }

    public function store()
    {
        $rules = [
            'biometric_id' => 'nullable|integer|unique:employees,biometric_id,' . $this->employeeId,
            'position' => 'nullable|string',
            'department' => 'nullable|string',
            'contract_type' => 'required|in:Mensual,Por Horas',
            'base_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:Activo,Inactivo',
            'role_name' => 'required|exists:roles,name',
        ];

        if ($this->employeeId || !$this->is_new_user) {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            $rules['new_user_name'] = 'required|string|max:255';
            $rules['new_user_email'] = 'required|email|unique:users,email';
            $rules['new_user_password'] = 'required|min:8';
        }

        $this->validate($rules);

        DB::beginTransaction();
        try {
            if (!$this->employeeId && $this->is_new_user) {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $this->new_user_name,
                    'email' => $this->new_user_email,
                    'password' => Hash::make($this->new_user_password),
                ]);
                $this->user_id = $user->id;
            }

            // Asignar o actualizar el rol del usuario
            $user = User::findOrFail($this->user_id);
            $user->syncRoles([$this->role_name]);

            // Actualizar / Crear el expediente de Empleado
            Employee::updateOrCreate(['id' => $this->employeeId], [
                'user_id' => $this->user_id,
                'biometric_id' => $this->biometric_id,
                'position' => $this->position,
                'department' => $this->department,
                'contract_type' => $this->contract_type,
                'base_salary' => $this->base_salary ?: 0,
                'hourly_rate' => $this->hourly_rate ?: 0,
                'hire_date' => $this->hire_date,
                'status' => $this->status,
            ]);

            DB::commit();

            session()->flash('message', $this->employeeId ? 'Empleado actualizado exitosamente.' : 'Empleado creado exitosamente.');
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error general: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'employee-modal');
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->employeeId = null;
        $this->is_new_user = false;
        $this->new_user_name = '';
        $this->new_user_email = '';
        $this->new_user_password = '';
        $this->role_name = '';
        $this->user_id = null;
        $this->biometric_id = null;
        $this->position = '';
        $this->department = '';
        $this->contract_type = 'Mensual';
        $this->base_salary = 0;
        $this->hourly_rate = 0;
        $this->hire_date = null;
        $this->status = 'Activo';
    }

    public function render(): View
    {
        $users = User::whereDoesntHave('roles', function($q) {
            $q->whereIn('name', ['Estudiante', 'Solicitante']);
        })->orderBy('name')->get();

        $roles = Role::whereNotIn('name', ['Estudiante', 'Solicitante'])->orderBy('name')->get();
        
        $employees = Employee::with('user.roles')
            ->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orWhere('biometric_id', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.admin.h-r.employees', [
            'employees' => $employees,
            'users' => $users,
            'roles' => $roles
        ]);
    }
}
