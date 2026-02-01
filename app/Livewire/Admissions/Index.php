<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Ya no se usa para generar pass, pero lo dejo por si acaso
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
                
                // 1. Obtener Usuario Existente
                // Buscamos por user_id si existe, si no por email (para compatibilidad hacia atrás)
                $user = null;
                
                if ($admission->user_id) {
                    $user = User::find($admission->user_id);
                }
                
                if (!$user) {
                    $user = User::where('email', $admission->email)->first();
                }

                // Si por alguna razón crítica no existe el usuario (ej: borrado manual), lo recreamos
                if (!$user) {
                    $tempPassword = Str::random(10);
                    $user = User::create([
                        'name' => $admission->full_name,
                        'email' => $admission->email,
                        'password' => Hash::make($tempPassword),
                    ]);
                    $admission->update(['user_id' => $user->id]);
                    $this->admissionNotes .= "\n[Sistema] Usuario recreado. Pass temporal: " . $tempPassword;
                }

                // Asignar rol
                $user->assignRole('Estudiante');

                // 2. Crear Estudiante asociado (Si no existe ya)
                $existingStudent = Student::where('user_id', $user->id)->first();
                
                if (!$existingStudent) {
                    Student::create([
                        'user_id' => $user->id,
                        'student_code' => 'EST-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                        'enrollment_date' => now(),
                        'status' => 'Activo',
                        'phone' => $admission->phone,
                        'birth_date' => $admission->birth_date,
                        'address' => $admission->address,
                    ]);
                }

                // 3. Actualizar Admisión
                $admission->update([
                    'status' => 'approved',
                    'notes' => $this->admissionNotes . "\n[Sistema] Solicitud Aprobada. El estudiante ya puede acceder a su panel académico.",
                ]);

                // TODO: Enviar correo de notificación de aprobación
            });

            session()->flash('message', 'Solicitud aprobada correctamente. El usuario ahora es un Estudiante activo.');

        } else {
            // RECHAZO
            $admission->update([
                'status' => 'rejected',
                'notes' => $this->admissionNotes, // Aquí el admin explica qué documento está mal
            ]);
            session()->flash('message', 'Solicitud rechazada/devuelta al aspirante.');
        }

        $this->showProcessModal = false;
        $this->reset(['selectedAdmissionId', 'admissionNotes', 'processAction']);
    }

    public function render()
    {
        $admissions = Admission::with(['course', 'user']) // Eager load user
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