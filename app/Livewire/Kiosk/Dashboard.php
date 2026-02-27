<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.kiosk.dashboard')->layout('layouts.kiosk');
    }
}
