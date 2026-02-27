<?php

namespace App\Livewire\Admin\Finance;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class PeriodClosing extends Component
{
    public $lock_date;

    public function mount()
    {
        $this->lock_date = Setting::where('key', 'accounting_lock_date')->value('value');
    }

    public function save()
    {
        $this->validate([
            'lock_date' => 'nullable|date',
        ]);

        Setting::updateOrCreate(
            ['key' => 'accounting_lock_date'],
            [
                'value' => $this->lock_date,
                'type' => 'date',
                'description' => 'Fecha de Cierre Contable. No se permitirán asientos con fecha igual o anterior a esta.'
            ]
        );

        session()->flash('success', 'El período contable ha sido cerrado exitosamente hasta la fecha seleccionada.');
    }

    public function render()
    {
        return view('livewire.admin.finance.period-closing');
    }
}
