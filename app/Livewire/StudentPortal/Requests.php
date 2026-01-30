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

        // Asegurarnos de tener el tipo seleccionado cargado
        if (!$this->selectedType || $this->selectedType->id != $this->typeId) {
            $this->selectedType = RequestType::find($this->typeId);
        }
        
        // Verificación de seguridad adicional
        if (!$this->selectedType) {
            $this->addError('typeId', 'El tipo de solicitud seleccionado no es válido.');
            return;
        }

        // Validación de curso requerido
        if ($this->selectedType->requires_enrolled_course || $this->selectedType->requires_completed_course) {
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
        if ($this->selectedType->requires_completed_course && $courseId) {
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
        StudentRequest::create([
            'student_id' => $this->student->id,
            'request_type_id' => $this->typeId,
            'type' => $this->selectedType->name, // Usamos el nombre del tipo cargado
            'course_id' => $courseId,
            'details' => $finalDetails,
            'status' => 'pendiente',
        ]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        $this->reset('typeId', 'details', 'selectedTargetId', 'selectedType', 'availableEnrollments');
        $this->mount(); 
    }

    public function render()
    {
        $studentRequests = $this->student 
            ? $this->student->requests()->with(['payment', 'course', 'requestType'])->latest()->paginate(10) 
            : collect();

        return view('livewire.student-portal.requests', [
            'studentRequests' => $studentRequests,
        ]);
    }
}