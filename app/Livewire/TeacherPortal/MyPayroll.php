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
        
        if (!$employee) {
            return view('livewire.teacher-portal.my-payroll', [
                'items' => collect([])
            ]);
        }

        // Only show items that belong to a generated/paid payroll so they don't see drafts.
        $items = PayrollItem::where('employee_id', $employee->id)
            ->whereHas('payroll', function($q) {
                // $q->where('status', 'Pagado'); // Descomentar para solo ver los que ya se depositaron
            })
            ->with('payroll')
            ->orderByDesc('id')
            ->get();

        return view('livewire.teacher-portal.my-payroll', [
            'items' => $items,
            'employee' => $employee
        ]);
    }
}
