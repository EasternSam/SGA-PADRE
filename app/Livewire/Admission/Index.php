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
    public $selectedAdmissionId = null;
    public $admissionNotes = '';
    public $processAction = ''; // 'approve' o 'reject'

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openProcessModal($id, $action)
    {
        $this->selectedAdmissionId = $id;
        $this->processAction = $action;
        $admission = Admission::find($id);
        $this->admissionNotes = $admission->notes;
        $this->showProcessModal = true;
    }

    public function processAdmission()
    {
        $admission = Admission::findOrFail($this->selectedAdmissionId);

        if ($this->processAction === 'approve') {
            DB::transaction(function () use ($admission) {
                // 1. Crear Usuario
                $password = Str::random(8); // Generar contraseña temporal
                $user = User::create([
                    'name' => $admission->full_name,
                    'email' => $admission->email,
                    'password' => Hash::make($password),
                    'must_change_password' => true,
                ]);
                $user->assignRole('Estudiante');

                // 2. Crear Estudiante asociado
                Student::create([
                    'user_id' => $user->id,
                    'student_code' => 'EST-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'enrollment_date' => now(),
                    'status' => 'Activo',
                    'phone' => $admission->phone,
                    // Otros campos que vengan de admission...
                ]);

                // 3. Actualizar Admisión
                $admission->update([
                    'status' => 'approved',
                    'notes' => $this->admissionNotes . "\n[Sistema] Usuario creado automáticamente. Password temporal: " . $password,
                ]);

                // TODO: Enviar correo al estudiante con sus credenciales
            });

            session()->flash('message', 'Solicitud aprobada y estudiante creado exitosamente.');

        } else {
            $admission->update([
                'status' => 'rejected',
                'notes' => $this->admissionNotes,
            ]);
            session()->flash('message', 'Solicitud rechazada.');
        }

        $this->showProcessModal = false;
        $this->reset(['selectedAdmissionId', 'admissionNotes', 'processAction']);
    }

    public function render()
    {
        $admissions = Admission::with('course')
            ->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
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