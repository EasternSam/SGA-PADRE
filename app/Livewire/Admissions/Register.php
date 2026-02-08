<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')] 
class Register extends Component
{
    public function mount()
    {
        // Redirigir porque el registro público ya no está activo
        return redirect()->route('login');
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <!-- El registro público ha sido deshabilitado -->
        </div>
        HTML;
    }
}