<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentRequest;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Payment;        // <-- Importar Payment
use App\Models\PaymentConcept; // <-- Importar Concepto
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
        
        try {
            DB::beginTransaction();

            $oldStatus = $this->selectedRequest->status;

            $this->selectedRequest->update([
                'status' => $newStatus,
                'admin_notes' => $this->adminNotes,
            ]);

            // LOGICA ADICIONAL AL APROBAR
            // Solo si cambia de estado a aprobado
            if ($newStatus === 'aprobado' && $oldStatus !== 'aprobado') {
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
            session()->flash('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    protected function handleApproval($request)
    {
        // Validamos que exista curso y estudiante
        if (!$request->course_id || !$request->student_id) {
            // Si falta info crítica, lanzamos excepción para que el catch la capture y avise
            throw new \Exception("La solicitud no tiene curso o estudiante asociado.");
        }

        $student = $request->student;
        $course = $request->course;

        // 1. Verificar si ya está inscrito
        $exists = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            // Si ya existe, no hacemos nada más que loguear/avisar
            session()->flash('warning', 'El estudiante ya estaba inscrito en este curso.');
            return;
        }

        // 2. Crear Inscripción (Enrollment)
        // Nota: Si la solicitud no especifica sección (schedule), creamos una inscripción general 
        // o asignada a una sección por defecto si existiera lógica para ello.
        // Aquí asumimos inscripción general pendiente de asignación de horario si no hay schedule_id.
        
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'Pendiente', // Pendiente de pago
            'enrollment_date' => now(),
        ]);

        // 3. Generar deuda de INSCRIPCIÓN
        $inscriptionConcept = PaymentConcept::firstOrCreate(
            ['name' => 'Inscripción'], 
            ['description' => 'Pago único de inscripción al curso', 'amount' => 0]
        );

        Payment::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'payment_concept_id' => $inscriptionConcept->id,
            'amount' => $course->registration_fee, // <-- Usamos el precio del CURSO
            'currency' => 'DOP',
            'status' => 'Pendiente',
            'gateway' => 'Sistema', // Indica que fue generado por el sistema administrativo
            'due_date' => now()->addDays(3),
        ]);

        Log::info("Solicitud ID {$request->id} aprobada por Admin. Inscripción y cobro generados.");
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