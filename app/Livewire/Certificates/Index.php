<?php

namespace App\Livewire\Certificates;

use App\Models\Enrollment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $minGrade = 70; // Nota mínima para habilitar el certificado

    public function render()
    {
        // CORRECCIÓN CRÍTICA:
        // La relación correcta es: courseSchedule -> module -> course
        // Usamos la notación de puntos para cargar todos los niveles.
        $enrollments = Enrollment::query()
            ->with(['student', 'courseSchedule.module.course']) 
            ->whereHas('student', function (Builder $query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            // Opcional: Filtrar solo si tiene nota final asignada para evitar vacíos
            ->whereNotNull('final_grade')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.certificates.index', [
            'enrollments' => $enrollments
        ])->layout('layouts.dashboard');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}