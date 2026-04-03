<?php

namespace App\Livewire\Admin\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeAttendance;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class Attendances extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter = '';

    public function mount()
    {
        $this->dateFilter = Carbon::today()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = EmployeeAttendance::with(['employee.user'])
            ->orderBy('punch_time', 'desc');

        if (!empty($this->dateFilter)) {
            $query->whereDate('punch_time', $this->dateFilter);
        }

        if (!empty($this->search)) {
            $query->whereHas('employee.user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })->orWhere('biometric_id', 'like', '%' . $this->search . '%');
        }

        return view('livewire.admin.h-r.attendances', [
            'attendances' => $query->paginate(20)
        ]);
    }
}
