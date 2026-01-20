<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentRequest;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Payment;         
use App\Models\PaymentConcept;  
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class RequestsManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = ''; 
    public $selectedRequest = null;
    public $adminNotes = '';
    public $showingModal = false;

    // Configuración de precios por defecto (idealmente debería venir de DB o Config)
    const PRECIO_DIPLOMA = 500; 

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function viewRequest($requestId)
    {
        $this->selectedRequest = StudentRequest::with(['student.user', 'course', 'payment'])->find($requestId);

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

        try {
            DB::beginTransaction();

            $oldStatus = $this->selectedRequest->status;

            // Actualizamos primero el estado
            $this->selectedRequest->update([
                'status' => $newStatus,
                'admin_notes' => $this->adminNotes,
            ]);

            // Si se aprueba, ejecutamos la lógica específica por tipo
            if ($newStatus === 'aprobado' && $oldStatus !== 'aprobado') {
                $this->handleSpecificLogic($this->selectedRequest);
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

    /**
     * Lógica centralizada para manejar diferentes tipos de solicitudes al aprobarse.
     */
    protected function handleSpecificLogic(StudentRequest $request)
    {
        switch ($request->type) {
            case 'solicitar_diploma':
                $this->approveDiplomaRequest($request);
                break;
            
            case 'retiro_curso':
                // Aquí podrías automatizar el cambio de estado del enrollment a 'Retirado'
                // $this->approveRetiroRequest($request);
                break;
            
            // Agregar más casos según necesidad...
            
            default:
                // Lógica por defecto o no hacer nada específico
                break;
        }
    }

    /**
     * Lógica específica para DIPLOMAS: Genera el cobro automático.
     */
    protected function approveDiplomaRequest(StudentRequest $request)
    {
        // 1. Verificar si ya existe un pago asociado para evitar duplicados
        if ($request->payment_id) {
            return;
        }

        // 2. Obtener la inscripción asociada al curso para vincular el pago
        // Como es diploma, buscamos una inscripción completada/aprobada de este estudiante en este curso
        $enrollment = Enrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->whereIn('status', ['Completado', 'completado', 'Aprobado', 'aprobado'])
            ->latest()
            ->first();

        if (!$enrollment) {
            // Si no encontramos enrollment, no podemos vincular el pago correctamente, 
            // pero podemos crearlo solo con student_id si la estructura lo permite.
            // Para mantener consistencia, lanzamos warning pero intentamos proceder si es posible.
             Log::warning("Aprobando diploma sin enrollment encontrado para Request ID: {$request->id}");
        }

        // 3. Obtener o Crear Concepto de Pago
        $concept = PaymentConcept::firstOrCreate(
            ['name' => 'Solicitud de Diploma'],
            ['description' => 'Pago por emisión y trámite de diploma', 'amount' => self::PRECIO_DIPLOMA]
        );

        // 4. Crear el Pago Pendiente
        $payment = Payment::create([
            'student_id' => $request->student_id,
            'enrollment_id' => $enrollment ? $enrollment->id : null,
            'payment_concept_id' => $concept->id,
            'amount' => $concept->amount ?? self::PRECIO_DIPLOMA,
            'currency' => 'DOP',
            'status' => 'Pendiente',
            'gateway' => 'Sistema',
            'due_date' => now()->addDays(7), // Damos 7 días para pagar
        ]);

        // 5. Vincular el pago a la solicitud
        $request->update(['payment_id' => $payment->id]);

        Log::info("Cobro de diploma generado para solicitud #{$request->id}");
    }

    public function render()
    {
        $requests = StudentRequest::with(['student.user', 'course', 'payment'])
            ->when($this->search, function ($query) {
                $query->whereHas('student.user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.requests-management', [
            'requests' => $requests
        ]);
    }
}