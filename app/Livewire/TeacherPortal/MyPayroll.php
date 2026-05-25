<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\PayrollItem;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class MyPayroll extends Component
{
    public function render()
    {
        $employee = auth()->user()->employee;
        
        $items = $employee 
            ? PayrollItem::where('employee_id', $employee->id)->with('payroll')->orderByDesc('id')->get()
            : collect([]);

        return view('livewire.teacher-portal.my-payroll', [
            'items' => $items,
            'employee' => $employee
        ]);
    }
}
