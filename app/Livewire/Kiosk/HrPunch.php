<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Livewire\Attributes\Layout;

#[Layout('layouts.kiosk')]
class HrPunch extends Component
{
    public $biometricId = '';

    public function punch($type)
    {
        // 0 = Entrada, 1 = Salida
        $this->validate([
            'biometricId' => 'required|integer'
        ]);

        $employee = Employee::where('biometric_id', $this->biometricId)->first();

        if (!$employee) {
            session()->flash('error', 'ID Biométrico no encontrado en el sistema.');
            return;
        }

        EmployeeAttendance::create([
            'biometric_id' => $employee->biometric_id,
            'punch_time' => now(),
            'punch_type' => $type,
            'device_serial' => 'WEB-KIOSK'
        ]);

        $action = $type === 0 ? 'Entrada' : 'Salida';
        session()->flash('success', "{$action} registrada: {$employee->user->name} (" . now()->format('h:i A') . ")");
        $this->biometricId = '';
    }

    public function render()
    {
        return view('livewire.kiosk.hr-punch');
    }
}
