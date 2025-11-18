<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StudentRequest;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class RequestsManagement extends Component
{
    use WithPagination;

    public $filterStatus = 'pendiente'; // Filtrar por pendientes por defecto
    public $selectedRequest = null;
    public $adminNotes = '';
    public $showingModal = false;

    /**
     * Muestra el modal para ver/actualizar una solicitud.
     * --- CORRECCIÓN AQUÍ ---
     * Cambiamos el type-hint de StudentRequest a un $requestId.
     */
    public function viewRequest($requestId)
    {
        // Buscamos la solicitud manualmente. Es más seguro.
        $this->selectedRequest = StudentRequest::with('student.user')->find($requestId);

        // Si la encontramos, mostramos el modal.
        if ($this->selectedRequest) {
            $this->adminNotes = $this->selectedRequest->admin_notes ?? '';
            $this->showingModal = true;
        }
    }

    /**
     * Cierra el modal.
     */
    public function closeModal()
    {
        $this->showingModal = false;
        $this->selectedRequest = null;
        $this->reset('adminNotes');
    }

    /**
     * Actualiza el estado de la solicitud seleccionada.
     */
    public function updateRequest(string $newStatus)
    {
        if (!in_array($newStatus, ['aprobado', 'rechazado'])) {
            return;
        }

        if ($this->selectedRequest) {
            $this->selectedRequest->update([
                'status' => $newStatus,
                'admin_notes' => $this->adminNotes,
            ]);

            session()->flash('success', 'Solicitud actualizada correctamente.');
            $this->closeModal();
        }
    }

    /**
     * Renderiza el componente.
     */
    public function render()
    {
        $requests = StudentRequest::with('student.user') // Carga el estudiante y el usuario asociado
            ->when($this->filterStatus, fn($query) => $query->where('status', $this->filterStatus))
            ->latest()
            
            ->paginate(15);
            
        return view('livewire.admin.requests-management', [
            'requests' => $requests,
        ]);
    }
}