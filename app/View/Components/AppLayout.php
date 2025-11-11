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
        // --- ¡CORRECCIÓN! ---
        // Estaba buscando 'layouts.app' (que no existe).
        // Lo apuntamos a 'layouts.dashboard', que es el layout
        // que estás usando en el resto de tu aplicación Jetstream.
        return view('layouts.dashboard');
    }
}