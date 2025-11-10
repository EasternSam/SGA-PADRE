<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // FIX: Apuntamos al layout correcto en minúscula.
        // Esto corrige las vistas de Breeze (como el perfil).
        return view('layouts.dashboard');
    }
}