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

    // Configuración de precios por defecto
    const PRECIO_DIPLOMA = 500; 

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function viewRequest($requestId)
    {
        // Cargamos todas las relaciones necesarias
        $this->selectedRequest = StudentRequest::with(['student.user', 'course', 'payment.paymentConcept'])->find($requestId);

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
            
            // Refrescamos la solicitud para mostrar los cambios en el modal
            $this->selectedRequest->refresh();
            
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
        Log::info("Ejecutando lógica específica para solicitud tipo: {$request->type}");

        switch ($request->type) {
            case 'solicitar_diploma':
                $this->approveDiplomaRequest($request);
                break;
            
            case 'retiro_curso':
                // $this->approveRetiroRequest($request);
                break;
            
            default:
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
            Log::info("Solicitud #{$request->id} ya tiene pago asociado. Omitiendo.");
            return;
        }

        // 2. Obtener la inscripción usando whereHas para navegar por las relaciones
        // Enrollment -> CourseSchedule -> Module -> Course
        $enrollment = Enrollment::where('student_id', $request->student_id)
            ->whereHas('courseSchedule.module', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            })
            // Buscamos cualquier estado válido que indique que cursó la materia
            ->whereIn('status', ['Completado', 'completado', 'Aprobado', 'aprobado', 'Cursando', 'cursando']) 
            ->latest()
            ->first();

        if (!$enrollment) {
             Log::warning("Aprobando diploma sin enrollment encontrado para Request ID: {$request->id}. Creando pago sin enrollment_id.");
        }

        // 3. Obtener o Crear Concepto de Pago
        $concept = PaymentConcept::firstOrCreate(
            ['name' => 'Solicitud de Diploma'],
            [
                'description' => 'Pago por emisión y trámite de diploma', 
                'amount' => self::PRECIO_DIPLOMA
            ]
        );

        $monto = $concept->amount > 0 ? $concept->amount : self::PRECIO_DIPLOMA;
        $nombreCurso = $request->course->name ?? 'General';

        // 4. Crear el Pago Pendiente
        $payment = Payment::create([
            'student_id' => $request->student_id,
            'enrollment_id' => $enrollment ? $enrollment->id : null, // Ahora sí debería encontrarse
            'payment_concept_id' => $concept->id,
            'amount' => $monto,
            'currency' => 'DOP',
            'status' => 'Pendiente',
            'gateway' => 'Sistema', 
            'due_date' => now()->addDays(7), 
            'description' => "Diploma - $nombreCurso", // Descripción clara para que se vea en el admin
        ]);

        // 5. Vincular el pago a la solicitud
        $request->payment_id = $payment->id;
        $request->save(); 

        Log::info("Cobro de diploma generado (Pago ID: {$payment->id}) para solicitud #{$request->id}");
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