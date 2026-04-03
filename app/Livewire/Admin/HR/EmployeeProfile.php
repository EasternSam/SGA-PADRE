<?php
namespace App\Livewire\Admin\HR;

use Livewire\Component;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class EmployeeProfile extends Component
{
    public Employee $employee;
    public $biometric_id;
    public $spatie_role;

    // Salary Modal State
    public $edit_contract_type;
    public $edit_base_salary;
    public $edit_hourly_rate;

    // Generic Event Modal State
    public $event_type;
    public $event_date;
    public $event_end_date;
    public $event_amount;
    public $event_score;
    public $event_description;

    public function mount(Employee $employee)
    {
        $this->employee = $employee->load([
            'user.roles', 
            'attendances' => function($q) { $q->latest('punch_time')->take(50); },
            'payrollItems.payroll'
        ]);
        
        $this->biometric_id = $employee->biometric_id;
        $this->spatie_role = optional($employee->user->roles->first())->name ?? '';
    }

    public function updateBiometricId()
    {
        $this->validate([
            'biometric_id' => 'nullable|integer|unique:employees,biometric_id,' . $this->employee->id
        ]);
        
        $this->employee->update(['biometric_id' => $this->biometric_id]);
        session()->flash('success', 'ID Biométrico actualizado a: ' . $this->biometric_id);
    }

    public function updateSpatieRole()
    {
        $user = $this->employee->user;
        
        if (empty($this->spatie_role)) {
            $user->syncRoles([]);
        } else {
            $user->syncRoles([$this->spatie_role]);
        }
        
        $this->employee->load('user.roles');
        session()->flash('success', 'Nivel de acceso del sistema modificado a: ' . ($this->spatie_role ?: 'DENEGADO'));
    }

    public function openSalaryModal()
    {
        $this->edit_contract_type = $this->employee->contract_type;
        $this->edit_base_salary = $this->employee->base_salary;
        $this->edit_hourly_rate = $this->employee->hourly_rate;
        $this->dispatch('open-modal', 'salary-modal');
    }

    public function saveSalary()
    {
        $this->validate([
            'edit_contract_type' => 'required|in:Mensual,Por Horas',
            'edit_base_salary' => 'nullable|numeric|min:0',
            'edit_hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $this->employee->update([
            'contract_type' => $this->edit_contract_type,
            'base_salary' => $this->edit_base_salary ?: 0,
            'hourly_rate' => $this->edit_hourly_rate ?: 0,
        ]);

        $this->dispatch('close-modal', 'salary-modal');
        session()->flash('success', 'Parámetros salariales actualizados exitosamente.');
    }

    public function showToast($message)
    {
        session()->flash('success', 'Acción Temporal: ' . $message);
    }

    public function openEventModal($type)
    {
        $this->event_type = $type;
        $this->event_date = now()->format('Y-m-d');
        $this->event_end_date = null;
        $this->event_amount = null;
        $this->event_score = null;
        $this->event_description = '';
        $this->dispatch('open-modal', 'event-modal');
    }

    public function saveEvent()
    {
        $rules = [
            'event_type' => 'required|string',
            'event_date' => 'required|date',
            'event_description' => 'required|string|max:1000'
        ];

        if (in_array($this->event_type, ['bonus', 'deduction'])) {
            $rules['event_amount'] = 'required|numeric|min:0.01';
        } elseif ($this->event_type === 'evaluation') {
            $rules['event_score'] = 'required|integer|min:1|max:5';
        } elseif (in_array($this->event_type, ['vacation', 'medical'])) {
            $rules['event_end_date'] = 'required|date|after_or_equal:event_date';
        }

        $this->validate($rules);

        \App\Models\EmployeeEvent::create([
            'employee_id' => $this->employee->id,
            'type' => $this->event_type,
            'event_date' => $this->event_date,
            'end_date' => $this->event_end_date,
            'amount' => $this->event_amount,
            'score' => $this->event_score,
            'description' => $this->event_description
        ]);

        $this->dispatch('close-modal', 'event-modal');
        $this->employee->load('events');
        session()->flash('success', 'Registro asentado oficialmente en el expediente.');
    }

    public function revokeTokens()
    {
        DB::table('personal_access_tokens')->where('tokenable_id', $this->employee->user_id)->delete();
        session()->flash('success', 'Todos los tokens de sesión de este usuario han sido revocados.');
    }

    public function sendPasswordReset()
    {
        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            ['email' => $this->employee->user->email]
        );
        session()->flash('success', 'Enlace de restablecimiento enviado exitosamente a: ' . $this->employee->user->email);
    }

    public function regeneratePin()
    {
        $pin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->employee->user->update(['kiosk_pin' => \Illuminate\Support\Facades\Hash::make($pin)]);
        
        // El PIN se muestra una sola vez y no se puede recuperar después por seguridad.
        session()->flash('success', "NUEVO PIN GENERADO: {$pin}. Por favor entregue este número al empleado inmediatamente.");
    }

    public function banUser()
    {
        $user = $this->employee->user;
        $user->access_expires_at = now()->subDay(); // Banear expirando acceso
        $user->save();
        $this->employee->update(['status' => 'Inactivo']);
        session()->flash('success', 'Usuario BANEADO operativamente del sistema.');
    }

    public function render()
    {
        // ... logic
        return view('livewire.admin.h-r.employee-profile');
    }
}
