<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\User;
use App\Models\Student;
use App\Models\Payment;          // <-- Importar
use App\Models\PaymentConcept;   // <-- Importar
use App\Models\Course;           // <-- Importar
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;

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

    // Método para APROBAR MANUALMENTE la admisión completa
    public function approveAdmission()
    {
        $admission = $this->selectedAdmission;

        DB::transaction(function () use ($admission) {
            $user = User::find($admission->user_id) ?? User::where('email', $admission->email)->first();
            $student = null;

            if ($user) {
                // Actualizar rol
                $user->removeRole('Solicitante');
                $user->assignRole('Estudiante');

                // Crear Estudiante si no existe
                $student = Student::where('user_id', $user->id)->first();
                if (!$student) {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'first_name' => $admission->first_name,
                        'last_name' => $admission->last_name,
                        'email' => $admission->email,
                        'cedula' => $admission->identification_id,
                        'status' => 'Activo',
                        'phone' => $admission->phone,
                        'birth_date' => $admission->birth_date,
                        'address' => $admission->address,
                        'enrollment_date' => now(),
                        'course_id' => $admission->course_id, // <-- Asignar carrera
                        // Matrícula temporal hasta que pague inscripción
                        'student_code' => 'PRE-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    ]);
                } else {
                    // Si ya existe, actualizamos la carrera
                    $student->update(['course_id' => $admission->course_id]);
                }
            }

            // --- GENERAR DEUDA DE INSCRIPCIÓN ---
            if ($student && $admission->course_id) {
                $course = Course::find($admission->course_id);
                
                // Solo si es carrera (grado) y tiene costo de inscripción
                if ($course && $course->program_type === 'degree' && $course->registration_fee > 0) {
                    
                    // Buscar o crear concepto de pago "Inscripción"
                    $concept = PaymentConcept::firstOrCreate(
                        ['name' => 'Inscripción'],
                        ['description' => 'Pago único de admisión a la carrera']
                    );

                    // Verificar si ya tiene deuda de inscripción pendiente para no duplicar
                    $exists = Payment::where('student_id', $student->id)
                        ->where('payment_concept_id', $concept->id)
                        ->where('status', 'Pendiente')
                        ->exists();

                    if (!$exists) {
                        Payment::create([
                            'user_id' => $user->id,
                            'student_id' => $student->id,
                            'payment_concept_id' => $concept->id,
                            'amount' => $course->registration_fee,
                            'currency' => 'DOP',
                            'status' => 'Pendiente',
                            'gateway' => 'Sistema',
                            'due_date' => now()->addDays(3), // 3 días para pagar
                            'notes' => 'Generado automáticamente al aprobar admisión.',
                        ]);
                    }
                }
            }

            // Actualizar admisión a aprobado
            $admission->status = 'approved';
            $admission->document_status = $this->tempDocStatus; 
            $admission->notes = $this->admissionNotes . "\n[Sistema] Aprobado manualmente por administración.";
            $admission->save();
        });

        session()->flash('message', 'Solicitud APROBADA. Se ha generado la deuda de inscripción.');
        $this->showProcessModal = false;
        $this->reset(['selectedAdmission', 'tempDocStatus']);
    }

    public function saveReview()
    {
        $admission = $this->selectedAdmission;

        // 1. Guardar estados de documentos
        $admission->document_status = $this->tempDocStatus;
        $admission->notes = $this->admissionNotes;

        // 2. Determinar estado general AUTOMÁTICO
        $allApproved = !in_array('pending', $this->tempDocStatus) && !in_array('rejected', $this->tempDocStatus);
        $hasRejection = in_array('rejected', $this->tempDocStatus);

        if ($allApproved) {
            // Si todos están aprobados, podemos llamar a la aprobación completa
            $this->approveAdmission();
            return;
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