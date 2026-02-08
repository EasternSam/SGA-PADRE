<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\User;
use App\Models\Student;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestApprovedMail;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Modal de Procesamiento
    public $showProcessModal = false;
    public $selectedAdmission = null;
    public $admissionNotes = '';
    
    // Estado temporal de documentos
    public $tempDocStatus = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openProcessModal($id)
    {
        $this->selectedAdmission = Admission::with('user', 'course')->find($id);
        $this->admissionNotes = $this->selectedAdmission->notes;
        
        $currentStatus = $this->selectedAdmission->document_status ?? [];
        $documents = $this->selectedAdmission->documents ?? [];
        
        // Inicializar estados si no existen
        foreach($documents as $key => $path) {
            if(!isset($currentStatus[$key])) {
                $currentStatus[$key] = 'pending';
            }
        }
        
        $this->tempDocStatus = $currentStatus;
        $this->showProcessModal = true;

        // CORRECCIÓN: Disparar evento para abrir el modal en el frontend
        $this->dispatch('open-modal', 'process-modal');
    }

    public function closeProcessModal()
    {
        $this->showProcessModal = false;
        $this->reset(['selectedAdmission', 'tempDocStatus', 'admissionNotes']);
        
        // CORRECCIÓN: Disparar evento para cerrar el modal
        $this->dispatch('close-modal', 'process-modal');
    }

    public function setDocStatus($key, $status)
    {
        $this->tempDocStatus[$key] = $status;
    }

    public function approveAdmission()
    {
        $admission = $this->selectedAdmission;

        try {
            DB::transaction(function () use ($admission) {
                
                // 1. OBTENER O CREAR USUARIO
                $user = null;
                if ($admission->user_id) {
                    $user = User::find($admission->user_id);
                }
                if (!$user) {
                    $user = User::where('email', $admission->email)->first();
                }

                $passwordGenerated = null;
                if (!$user) {
                    $passwordGenerated = $admission->identification_id; 
                    $user = User::create([
                        'name' => $admission->first_name . ' ' . $admission->last_name,
                        'email' => $admission->email,
                        'password' => Hash::make($passwordGenerated),
                        'access_expires_at' => now()->addMonths(3),
                        'must_change_password' => true,
                    ]);
                    Log::info("Usuario creado autom para admisión ID {$admission->id}");
                }

                // 2. ASIGNAR ROL
                if ($user->hasRole('Solicitante')) {
                    $user->removeRole('Solicitante');
                }
                if (!$user->hasRole('Estudiante')) {
                    $user->assignRole('Estudiante');
                }

                // 3. CREAR O ACTUALIZAR PERFIL DE ESTUDIANTE
                $student = Student::where('user_id', $user->id)->first();
                $preMatricula = 'PRE-' . date('y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

                if (!$student) {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'first_name' => $admission->first_name,
                        'last_name' => $admission->last_name,
                        'email' => $admission->email,
                        'cedula' => $admission->identification_id,
                        'status' => 'Prospecto',
                        'phone' => $admission->phone,
                        'birth_date' => $admission->birth_date,
                        'address' => $admission->address,
                        'enrollment_date' => now(),
                        'course_id' => $admission->course_id,
                        'student_code' => $preMatricula,
                    ]);
                } else {
                    $student->update([
                        'course_id' => $admission->course_id,
                        'status' => 'Prospecto',
                    ]);
                }

                // 4. GENERAR DEUDA DE INSCRIPCIÓN
                if ($student && $admission->course_id) {
                    $course = Course::find($admission->course_id);
                    
                    if ($course && $course->registration_fee > 0) {
                        $concept = PaymentConcept::firstOrCreate(
                            ['name' => 'Inscripción'],
                            ['description' => 'Pago único de admisión a la carrera', 'amount' => $course->registration_fee]
                        );

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
                                'due_date' => now()->addDays(5),
                                'notes' => 'Generado automáticamente al aprobar admisión.',
                            ]);
                        }
                    }
                }

                // 5. ACTUALIZAR ADMISIÓN
                $admission->status = 'approved';
                $admission->document_status = $this->tempDocStatus; 
                $admission->notes = $this->admissionNotes . "\n[Sistema] Aprobado el " . now()->format('d/m/Y');
                $admission->user_id = $user->id;
                $admission->save();
            });

            session()->flash('message', 'Solicitud APROBADA. Usuario y deuda generados.');
            
            // Cerrar modal
            $this->closeProcessModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    public function saveReview()
    {
        $admission = $this->selectedAdmission;

        // 1. Guardar estados de documentos
        $admission->document_status = $this->tempDocStatus;
        $admission->notes = $this->admissionNotes;

        // 2. Lógica de estado
        $allApproved = !in_array('pending', $this->tempDocStatus) && !in_array('rejected', $this->tempDocStatus);
        $hasRejection = in_array('rejected', $this->tempDocStatus);

        if ($allApproved) {
            $this->approveAdmission();
            return;
        } elseif ($hasRejection) {
            $admission->status = 'rejected';
            $admission->save();
            session()->flash('message', 'Solicitud marcada para correcciones.');
        } else {
            $admission->status = 'pending';
            $admission->save();
            session()->flash('message', 'Revisión guardada (Parcial).');
        }

        // Cerrar modal
        $this->closeProcessModal();
    }

    public function render()
    {
        $admissions = Admission::with(['course', 'user'])
            ->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('identification_id', 'like', '%' . $this->search . '%')
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