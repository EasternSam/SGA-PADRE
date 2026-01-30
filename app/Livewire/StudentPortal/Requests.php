<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\StudentRequest;
use App\Models\Enrollment;
use App\Models\RequestType; // Importar
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')]
class Requests extends Component
{
    use WithPagination;

    // Propiedades para los filtros de la vista
    public $search = '';
    public $statusFilter = '';

    public $typeId = ''; // Cambiado de $type a $typeId para usar el ID de la BD
    public $details = '';
    public $selectedTargetId = ''; // ID de la inscripción seleccionada

    public $student;
    public $requestTypes = []; // Ahora será una colección de modelos
    public $selectedType = null; // Para guardar el objeto del tipo seleccionado

    // Listas de cursos disponibles según reglas
    public $availableEnrollments = []; 

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            return;
        }

        // Cargar tipos de solicitud activos desde la BD
        $this->requestTypes = RequestType::where('is_active', true)->get();
    }

    // Resetear paginación cuando se usan los filtros
    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }

    public function updatedTypeId()
    {
        $this->reset('details', 'selectedTargetId', 'selectedType', 'availableEnrollments');
        $this->resetErrorBag();

        if ($this->typeId) {
            $this->selectedType = RequestType::find($this->typeId);
            
            if ($this->selectedType) {
                $this->loadAvailableEnrollments();
            }
        }
    }

    public function loadAvailableEnrollments()
    {
        if (!$this->selectedType) return;

        // Regla 1: Requiere estar CURSANDO (Activo)
        if ($this->selectedType->requires_enrolled_course) {
            $this->availableEnrollments = Enrollment::where('student_id', $this->student->id)
                ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
                ->with('courseSchedule.module.course')
                ->get();
        } 
        // Regla 2: Requiere haber COMPLETADO (Aprobado)
        elseif ($this->selectedType->requires_completed_course) {
            $this->availableEnrollments = Enrollment::where('student_id', $this->student->id)
                ->whereIn('status', ['Completado', 'completado', 'Aprobado', 'aprobado'])
                ->with('courseSchedule.module.course')
                ->get();
        }
    }

    public function submitRequest()
    {
        $this->validate([
            'typeId' => 'required|exists:request_types,id',
            'details' => 'nullable|string', // Cambiado a nullable según solicitud
        ]);

        // Asegurarnos de tener el tipo seleccionado cargado y fresco
        // A veces Livewire puede perder el estado de objetos complejos, mejor recargar por ID
        $typeModel = RequestType::find($this->typeId);
        
        // Verificación de seguridad adicional
        if (!$typeModel) {
            $this->addError('typeId', 'El tipo de solicitud seleccionado no es válido.');
            return;
        }
        
        // Actualizamos la propiedad pública para la vista por si acaso
        $this->selectedType = $typeModel;

        // Validación de curso requerido
        if ($typeModel->requires_enrolled_course || $typeModel->requires_completed_course) {
            $this->validate([
                'selectedTargetId' => 'required|exists:enrollments,id'
            ]);
        }

        // Obtener curso ID si aplica
        $courseId = null;
        $finalDetails = $this->details;

        if ($this->selectedTargetId) {
            $enrollment = Enrollment::with('courseSchedule.module.course')->find($this->selectedTargetId);
            
            if ($enrollment && $enrollment->courseSchedule && $enrollment->courseSchedule->module) {
                $courseId = $enrollment->courseSchedule->module->course_id;
                $courseName = $enrollment->courseSchedule->module->course->name ?? 'N/A';
                
                // Agregar contexto al detalle si hay algo escrito, sino solo info del curso
                $context = "Curso Relacionado: $courseName\nEstado Inscripción: {$enrollment->status}";
                $finalDetails = $this->details ? "$context\n\n{$this->details}" : $context;
            }
        }

        // Verificar duplicados para trámites importantes (ej: Diplomas)
        if ($typeModel->requires_completed_course && $courseId) {
            $exists = StudentRequest::where('student_id', $this->student->id)
                ->where('request_type_id', $this->typeId)
                ->where('course_id', $courseId)
                ->whereIn('status', ['pendiente', 'aprobado'])
                ->exists();
            
            if ($exists) {
                $this->addError('typeId', 'Ya tienes una solicitud activa de este tipo para este curso.');
                return;
            }
        }

        // Crear Solicitud
        // Usamos $typeModel->name explícitamente para asegurar que no sea nulo
        StudentRequest::create([
            'student_id' => $this->student->id,
            'request_type_id' => $this->typeId,
            'type' => $typeModel->name, // Nombre recuperado directamente de la BD en este ciclo
            'course_id' => $courseId,
            'details' => $finalDetails,
            'status' => 'pendiente',
        ]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        $this->reset('typeId', 'details', 'selectedTargetId', 'selectedType', 'availableEnrollments');
        $this->mount(); 
    }

    public function download($requestId)
    {
        // Buscar la solicitud asegurando que pertenece al estudiante logueado
        $request = StudentRequest::where('student_id', $this->student->id)
            ->with(['payment'])
            ->findOrFail($requestId);

        // 1. Validar Estado: Debe estar Aprobado
        // Normalizamos a minúsculas para comparar
        if (!in_array(strtolower($request->status), ['aprobado', 'approved'])) {
            session()->flash('error', 'La solicitud aún no ha sido aprobada.');
            return;
        }

        // 2. Validar Pago: Si tiene pago asociado, debe estar completado
        if ($request->payment) {
            $payStatus = strtolower($request->payment->status);
            if (!in_array($payStatus, ['pagado', 'paid', 'completado', 'completed'])) {
                session()->flash('error', 'Debes completar el pago antes de descargar el documento.');
                return;
            }
        }

        // 3. Redirigir a la ruta de descarga
        // Nota: Asumimos que certificates.download maneja la lógica de generación.
        // Pasamos student y course si existe, o request_id si el controlador lo soporta.
        return redirect()->route('certificates.download', [
            'student' => $request->student_id,
            'course' => $request->course_id ?? 0, // 0 o null si es un certificado general
            'request_id' => $request->id // Pasamos ID de solicitud por si se necesita trazar
        ]);
    }

    public function render()
    {
        $studentRequests = collect();

        if ($this->student) {
            $query = $this->student->requests()
                ->with(['payment', 'course', 'requestType']);

            // Aplicar Filtro de Búsqueda
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('requestType', function($qt) {
                          $qt->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('course', function($qc) {
                          $qc->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            // Aplicar Filtro de Estado
            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $studentRequests = $query->latest()->paginate(10);
        }

        return view('livewire.student-portal.requests', [
            'studentRequests' => $studentRequests,
        ]);
    }
}