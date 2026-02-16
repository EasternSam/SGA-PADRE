<?php

namespace App\Modules\HumanResources\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        // Nota el prefijo 'humanresources::' que le dice a Laravel dónde buscar la vista
        return view('humanresources::dashboard');
    }
}