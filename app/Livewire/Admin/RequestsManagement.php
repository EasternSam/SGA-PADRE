<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentRequest;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class RequestsManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = ''; // Dejar vacío para ver TODO por defecto
    public $selectedRequest = null;
    public $adminNotes = '';
    public $showingModal = false;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = StudentRequest::with(['student.user', 'course'])->find($requestId);

        if ($this->selectedRequest) {
            $this->adminNotes = $this->selectedRequest->admin_notes ?? '';
            $this->showingModal = true;
        }
    }

    public function closeModal()
    {
        $this->showingModal = false;
        $this->selectedRequest = null;
        $this->reset('adminNotes');
    }

    public function updateRequest(string $newStatus)
    {
        if (!$this->selectedRequest) return;

        // Normalizar estados: backend usa 'approved'/'rejected' o 'aprobado'/'rechazado'?
        // Basado en tu vista anterior, usabas 'aprobado'/'rechazado'.
        
        try {
            DB::beginTransaction();

            $this->selectedRequest->update([
                'status' => $newStatus,
                'admin_notes' => $this->adminNotes,
            ]);

            // LOGICA ADICIONAL AL APROBAR
            if ($newStatus === 'aprobado') {
                $this->handleApproval($this->selectedRequest);
            }
            // LOGICA ADICIONAL AL RECHAZAR
            elseif ($newStatus === 'rechazado') {
                 // Lógica de rechazo si aplica (ej: notificar)
            }

            DB::commit();
            session()->flash('success', 'Solicitud actualizada correctamente.');
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando solicitud: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar la solicitud.');
        }
    }

    protected function handleApproval($request)
    {
        // Ejemplo: Si es inscripción, crear el enrollment.
        // Nota: user_id lo sacamos del estudiante
        if ($request->course_id && $request->student && $request->student->user_id) {
            // Lógica para inscribir o cambiar sección
            // Enrollment::updateOrCreate(...)
        }
    }

    public function render()
    {
        $requests = StudentRequest::with(['student.user', 'course'])
            ->when($this->search, function ($query) {
                $query->whereHas('student.user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest() // Ordenar por más reciente
            ->paginate(10);

        return view('livewire.admin.requests-management', [
            'requests' => $requests
        ]);
    }
}