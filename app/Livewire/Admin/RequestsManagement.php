<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentRequest;
use App\Models\RequestType; // Importar
use App\Models\Student;     // Importar
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Payment;         
use App\Models\PaymentConcept;  
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestApprovedMail;
use App\Mail\PaymentReceiptMail;
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

    // --- Variables para Gestión de Tipos ---
    public $showingTypesModal = false;
    public $editingTypeId = null;
    public $type_name = '';
    public $type_description = '';
    public $type_requires_payment = false;
    public $type_payment_amount = 0;
    public $type_requires_enrolled_course = false;
    public $type_requires_completed_course = false;
    public $type_is_active = true;

    // --- Variables para Crear Nueva Solicitud (Manual) ---
    public $showingCreateModal = false;
    public $new_student_search = '';
    public $new_student_id = null;
    public $new_request_type_id = null;
    public $new_course_id = null;
    public $new_details = '';
    public $availableCourses = []; // Cursos filtrados según requisitos

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    // --- GESTIÓN DE SOLICITUDES EXISTENTES ---

    public function viewRequest($requestId)
    {
        $this->selectedRequest = StudentRequest::with(['student.user', 'course', 'payment.paymentConcept', 'requestType'])->find($requestId);

        if ($this->selectedRequest) {
            $this->adminNotes = $this->selectedRequest->admin_notes ?? '';
            $this->showingModal = true;
        }
    }

    public function updateRequest(string $newStatus)
    {
        if (!$this->selectedRequest) return;

        try {
            DB::beginTransaction();

            $oldStatus = $this->selectedRequest->status;

            $this->selectedRequest->update([
                'status' => $newStatus,
                'admin_notes' => $this->adminNotes,
            ]);

            // Lógica dinámica basada en la configuración del TIPO DE SOLICITUD
            if ($newStatus === 'aprobado' && $oldStatus !== 'aprobado') {
                $this->handleDynamicLogic($this->selectedRequest);
            }

            DB::commit();
            
            // Enviar Correo
            if ($newStatus === 'aprobado' && $oldStatus !== 'aprobado') {
                if ($this->selectedRequest->student && $this->selectedRequest->student->email) {
                    try {
                        $this->selectedRequest->refresh();
                        Mail::to($this->selectedRequest->student->email)->send(new RequestApprovedMail($this->selectedRequest));
                    } catch (\Exception $e) {
                        Log::error("Error enviando correo: " . $e->getMessage());
                    }
                }
            }
            
            $this->selectedRequest->refresh();
            session()->flash('success', 'Solicitud actualizada correctamente.');
            $this->showingModal = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando solicitud: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    protected function handleDynamicLogic(StudentRequest $request)
    {
        $type = $request->requestType;
        if (!$type) return;

        // 1. Generación de Cobro (Si el tipo lo requiere)
        if ($type->requires_payment && !$request->payment_id) {
            
            // Buscar inscripción asociada si hay curso
            $enrollmentId = null;
            if ($request->course_id) {
                $enrollment = Enrollment::where('student_id', $request->student_id)
                    ->whereHas('courseSchedule.module', function ($q) use ($request) {
                        $q->where('course_id', $request->course_id);
                    })
                    ->latest()
                    ->first();
                $enrollmentId = $enrollment ? $enrollment->id : null;
            }

            // Crear Concepto si no existe
            $concept = PaymentConcept::firstOrCreate(
                ['name' => $type->name],
                [
                    'description' => 'Pago automático por solicitud aprobada: ' . $type->name, 
                    'amount' => $type->payment_amount
                ]
            );

            // Crear Pago
            $payment = Payment::create([
                'student_id' => $request->student_id,
                'enrollment_id' => $enrollmentId,
                'payment_concept_id' => $concept->id,
                'amount' => $type->payment_amount,
                'currency' => 'DOP',
                'status' => 'Pendiente',
                'gateway' => 'Sistema', 
                'due_date' => now()->addDays(7), 
                'description' => $type->name . ($request->course ? ' - ' . $request->course->name : ''),
            ]);

            $request->payment_id = $payment->id;
            $request->save();

            // Notificar deuda
            if ($request->student && $request->student->email) {
                try {
                    Mail::to($request->student->email)->send(new PaymentReceiptMail($payment, ''));
                } catch (\Exception $e) {}
            }
        }
    }

    // --- CRUD DE TIPOS DE SOLICITUD (CONFIGURACIÓN) ---

    public function openTypesModal()
    {
        $this->resetTypeForm();
        $this->showingTypesModal = true;
    }

    public function editType($id)
    {
        $type = RequestType::find($id);
        if ($type) {
            $this->editingTypeId = $type->id;
            $this->type_name = $type->name;
            $this->type_description = $type->description;
            $this->type_requires_payment = $type->requires_payment;
            $this->type_payment_amount = $type->payment_amount;
            $this->type_requires_enrolled_course = $type->requires_enrolled_course;
            $this->type_requires_completed_course = $type->requires_completed_course;
            $this->type_is_active = $type->is_active;
            $this->showingTypesModal = true;
        }
    }

    public function saveType()
    {
        $this->validate([
            'type_name' => 'required|string|max:255',
            'type_payment_amount' => 'required_if:type_requires_payment,true|numeric|min:0',
        ]);

        $data = [
            'name' => $this->type_name,
            'description' => $this->type_description,
            'requires_payment' => $this->type_requires_payment,
            'payment_amount' => $this->type_requires_payment ? $this->type_payment_amount : 0,
            'requires_enrolled_course' => $this->type_requires_enrolled_course,
            'requires_completed_course' => $this->type_requires_completed_course,
            'is_active' => $this->type_is_active,
        ];

        if ($this->editingTypeId) {
            RequestType::find($this->editingTypeId)->update($data);
            session()->flash('success', 'Tipo de solicitud actualizado.');
        } else {
            RequestType::create($data);
            session()->flash('success', 'Nuevo tipo de solicitud creado.');
        }

        $this->resetTypeForm();
    }

    public function deleteType($id)
    {
        $type = RequestType::find($id);
        if ($type) {
            $type->delete(); // Soft delete sería mejor si ya hay solicitudes, pero por ahora delete normal
            session()->flash('success', 'Tipo eliminado.');
        }
    }

    private function resetTypeForm()
    {
        $this->editingTypeId = null;
        $this->type_name = '';
        $this->type_description = '';
        $this->type_requires_payment = false;
        $this->type_payment_amount = 0;
        $this->type_requires_enrolled_course = false;
        $this->type_requires_completed_course = false;
        $this->type_is_active = true;
    }

    // --- CREAR NUEVA SOLICITUD (MANUAL) ---

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showingCreateModal = true;
    }

    public function updatedNewRequestTypeId()
    {
        $this->new_course_id = null;
        $this->availableCourses = [];

        if (!$this->new_request_type_id || !$this->new_student_id) return;

        $type = RequestType::find($this->new_request_type_id);
        if (!$type) return;

        // Filtrar cursos según requisitos
        if ($type->requires_enrolled_course) {
            // Cursos que el estudiante está cursando actualmente
            $this->availableCourses = Course::whereHas('modules.schedules.enrollments', function($q) {
                $q->where('student_id', $this->new_student_id)
                  ->where('status', 'Cursando');
            })->get();
        } elseif ($type->requires_completed_course) {
            // Cursos que el estudiante ya aprobó
            $this->availableCourses = Course::whereHas('modules.schedules.enrollments', function($q) {
                $q->where('student_id', $this->new_student_id)
                  ->whereIn('status', ['Aprobado', 'Completado']);
            })->get();
        } else {
            // Si no requiere nada específico, mostrar todos los cursos (o ninguno según lógica de negocio)
            $this->availableCourses = Course::all();
        }
    }

    // Método helper para buscar estudiantes dinámicamente
    public function getStudentsProperty()
    {
        if (strlen($this->new_student_search) < 2) return [];
        return Student::whereHas('user', function($q) {
            $q->where('name', 'like', '%' . $this->new_student_search . '%');
        })->take(5)->get();
    }

    public function selectStudent($id)
    {
        $this->new_student_id = $id;
        $this->new_student_search = Student::find($id)->user->name;
        $this->updatedNewRequestTypeId(); // Refrescar cursos si ya había tipo seleccionado
    }

    public function storeRequest()
    {
        $this->validate([
            'new_student_id' => 'required|exists:students,id',
            'new_request_type_id' => 'required|exists:request_types,id',
            'new_details' => 'required|string',
        ]);

        $type = RequestType::find($this->new_request_type_id);

        if (($type->requires_enrolled_course || $type->requires_completed_course) && !$this->new_course_id) {
            $this->addError('new_course_id', 'Este tipo de solicitud requiere seleccionar un curso.');
            return;
        }

        StudentRequest::create([
            'student_id' => $this->new_student_id,
            'request_type_id' => $this->new_request_type_id,
            'course_id' => $this->new_course_id,
            'details' => $this->new_details,
            'status' => 'pendiente', // Siempre nace pendiente para revisión
        ]);

        session()->flash('success', 'Solicitud creada manualmente.');
        $this->showingCreateModal = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm()
    {
        $this->new_student_search = '';
        $this->new_student_id = null;
        $this->new_request_type_id = null;
        $this->new_course_id = null;
        $this->new_details = '';
        $this->availableCourses = [];
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->showingModal = false;
        $this->showingTypesModal = false;
        $this->showingCreateModal = false;
        $this->selectedRequest = null;
        $this->reset('adminNotes');
        $this->resetTypeForm();
        $this->resetCreateForm();
    }

    public function render()
    {
        $requests = StudentRequest::with(['student.user', 'course', 'payment', 'requestType'])
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

        // Cargamos los tipos disponibles para el modal de configuración
        $requestTypes = RequestType::all();

        return view('livewire.admin.requests-management', [
            'requests' => $requests,
            'requestTypes' => $requestTypes
        ]);
    }
}