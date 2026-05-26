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

        // En el entorno escolar, el estudiante está matriculado en las asignaturas de su sección
        if ($this->selectedType->requires_enrolled_course || $this->selectedType->requires_completed_course) {
            if ($this->student && $this->student->section_id) {
                $this->availableEnrollments = \App\Models\SectionSubject::where('section_id', $this->student->section_id)
                    ->with(['subject', 'teacher'])
                    ->get();
            } else {
                $this->availableEnrollments = [];
            }
        }
    }

    public function submitRequest()
    {
        $this->validate([
            'typeId' => 'required|exists:request_types,id',
            'details' => 'nullable|string',
        ]);

        // Asegurarnos de tener el tipo seleccionado cargado y fresco
        $typeModel = RequestType::find($this->typeId);
        
        // Verificación de seguridad adicional
        if (!$typeModel) {
            $this->addError('typeId', 'El tipo de solicitud seleccionado no es válido.');
            return;
        }
        
        $this->selectedType = $typeModel;

        // Validación de asignatura escolar requerida
        if ($typeModel->requires_enrolled_course || $typeModel->requires_completed_course) {
            $this->validate([
                'selectedTargetId' => 'required|exists:section_subjects,id'
            ]);
        }

        // Obtener curso ID para preservar integridad relacional (carrera/programa del estudiante si existe)
        $courseId = $this->student->course_id;
        $finalDetails = $this->details;
        $targetDetails = "";

        if ($this->selectedTargetId) {
            $sectionSubject = \App\Models\SectionSubject::with(['subject', 'teacher'])->find($this->selectedTargetId);
            
            if ($sectionSubject) {
                $subjectName = $sectionSubject->subject->name ?? 'N/A';
                $teacherName = $sectionSubject->teacher->name ?? 'N/A';
                
                $targetDetails = "[Asignatura ID: {$this->selectedTargetId}]";
                // Agregar contexto al detalle
                $context = "Asignatura Relacionada: $subjectName\nDocente: $teacherName\n$targetDetails";
                $finalDetails = $this->details ? "$context\n\n{$this->details}" : $context;
            }
        }

        // Verificar duplicados para trámites activos
        if ($this->selectedTargetId) {
            $exists = StudentRequest::where('student_id', $this->student->id)
                ->where('request_type_id', $this->typeId)
                ->where('details', 'like', '%' . $targetDetails . '%')
                ->whereIn('status', ['pendiente', 'aprobado'])
                ->exists();
            
            if ($exists) {
                $this->addError('typeId', 'Ya tienes una solicitud activa de este tipo para esta asignatura.');
                return;
            }
        }

        // Crear Solicitud
        StudentRequest::create([
            'student_id' => $this->student->id,
            'request_type_id' => $this->typeId,
            'type' => $typeModel->name,
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