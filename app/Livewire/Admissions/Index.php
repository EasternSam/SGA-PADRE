<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Modal de Procesamiento
    public $showProcessModal = false;
    public $selectedAdmission = null; // Guardamos el objeto completo
    public $admissionNotes = '';
    
    // Estado temporal de documentos en el modal antes de guardar
    public $tempDocStatus = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openProcessModal($id)
    {
        $this->selectedAdmission = Admission::with('user', 'course')->find($id);
        $this->admissionNotes = $this->selectedAdmission->notes;
        
        // Cargar estados actuales o default 'pending'
        $currentStatus = $this->selectedAdmission->document_status ?? [];
        $documents = $this->selectedAdmission->documents ?? [];
        
        foreach($documents as $key => $path) {
            if(!isset($currentStatus[$key])) {
                $currentStatus[$key] = 'pending';
            }
        }
        
        $this->tempDocStatus = $currentStatus;
        $this->showProcessModal = true;
    }

    public function setDocStatus($key, $status)
    {
        $this->tempDocStatus[$key] = $status;
    }

    public function saveReview()
    {
        $admission = $this->selectedAdmission;

        // 1. Guardar estados de documentos
        $admission->document_status = $this->tempDocStatus;
        $admission->notes = $this->admissionNotes;

        // 2. Determinar estado general
        // Si hay algún rechazado -> estado general = rejected
        // Si todos aprobados -> estado general = approved (y crear estudiante)
        // Si mezcla -> pending
        
        $allApproved = !in_array('pending', $this->tempDocStatus) && !in_array('rejected', $this->tempDocStatus);
        $hasRejection = in_array('rejected', $this->tempDocStatus);

        if ($allApproved) {
            // APROBAR FINALMENTE
            DB::transaction(function () use ($admission) {
                $user = User::find($admission->user_id) ?? User::where('email', $admission->email)->first();
                
                if ($user) {
                    $user->removeRole('Solicitante');
                    $user->assignRole('Estudiante');

                    $existingStudent = Student::where('user_id', $user->id)->first();
                    if (!$existingStudent) {
                        Student::create([
                            'user_id' => $user->id,
                            'first_name' => $admission->first_name,
                            'last_name' => $admission->last_name,
                            'email' => $admission->email,
                            'cedula' => $admission->identification_id,
                            'status' => 'Activo',
                            'phone' => $admission->phone,
                            'birth_date' => $admission->birth_date,
                            'address' => $admission->address,
                        ]);
                    }
                }

                $admission->status = 'approved';
                $admission->save();
            });
            session()->flash('message', 'Solicitud aprobada completamente. Estudiante inscrito.');

        } elseif ($hasRejection) {
            $admission->status = 'rejected';
            $admission->save();
            session()->flash('message', 'Solicitud marcada con correcciones pendientes.');
        } else {
            $admission->status = 'pending';
            $admission->save();
            session()->flash('message', 'Revisión guardada. Aún pendiente.');
        }

        $this->showProcessModal = false;
        $this->reset(['selectedAdmission', 'tempDocStatus']);
    }

    public function render()
    {
        $admissions = Admission::with(['course', 'user'])
            ->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('identification_id', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admissions.index', [
            'admissions' => $admissions
        ]);
    }
}