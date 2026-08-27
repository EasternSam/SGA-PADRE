<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLockPeriod;
use Livewire\Component;

class GradeLockManager extends Component
{
    public $locks = [];

    public function mount()
    {
        $this->loadLocks();
    }

    public function loadLocks()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            $this->locks = [];
            return;
        }

        $periods = EvaluationPeriod::where('academic_year_id', $activeYear->id)
            ->orderBy('number')->get();

        $this->locks = $periods->map(function ($period) {
            $lock = GradeLockPeriod::where('evaluation_period_id', $period->id)->first();
            return [
                'period_id'   => $period->id,
                'period_name' => $period->name,
                'lock_date'   => $lock?->lock_date?->format('Y-m-d') ?? '',
                'is_locked'   => $lock?->is_locked ?? false,
                'lock_id'     => $lock?->id,
            ];
        })->toArray();
    }

    public function saveLock($index)
    {
        $lock = $this->locks[$index];

        GradeLockPeriod::updateOrCreate(
            ['evaluation_period_id' => $lock['period_id']],
            [
                'lock_date'  => $lock['lock_date'] ?: null,
                'is_locked'  => $lock['is_locked'],
                'locked_by'  => auth()->id(),
                'lock_reason' => $lock['is_locked'] ? 'Bloqueado manualmente' : null,
            ]
        );

        $this->loadLocks();
        session()->flash('message', 'Configuración de bloqueo guardada.');
    }

    public function toggleLock($index)
    {
        $this->locks[$index]['is_locked'] = !$this->locks[$index]['is_locked'];
        $this->saveLock($index);
    }

    public function render()
    {
        return view('livewire.admin.school.grade-lock-manager')
            ->layout('layouts.dashboard');
    }
}
