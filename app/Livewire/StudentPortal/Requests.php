<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\StudentRequest;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')]
class Requests extends Component
{
    use WithPagination;

    public $type = '';
    public $details = '';
    public $selectedTargetId = ''; // ID de la inscripción seleccionada

    public $student;
    public $activeEnrollments = [];
    public $completedEnrollments = [];
    public $canRequestDiploma = false;

    // Opciones para el dropdown de tipos de solicitud
    public $requestTypes = [
        'retiro_curso' => 'Retiro de Curso',
        'cambio_seccion' => 'Cambio de Sección',
        'solicitar_diploma' => 'Solicitar Diploma',
        'revision_calificacion' => 'Solicitar Revisión de Calificación',
        'otro' => 'Otro',
    ];

    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            return;
        }

        // Cargar inscripciones ACTIVAS (para retiros/cambios)
        $this->activeEnrollments = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
            ->with('courseSchedule.module.course')
            ->get();

        // Cargar inscripciones COMPLETADAS (para diplomas)
        $this->completedEnrollments = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Completado', 'completado', 'Aprobado', 'aprobado'])
            ->with('courseSchedule.module.course')
            ->get();

        $this->canRequestDiploma = $this->completedEnrollments->count() > 0;
    }

    public function rules()
    {
        $rules = [
            'type' => ['required', 'string', Rule::in(array_keys($this->requestTypes))],
            'details' => 'required|string|min:10',
        ];

        if (in_array($this->type, ['retiro_curso', 'cambio_seccion'])) {
            $rules['selectedTargetId'] = 'required|integer|exists:enrollments,id';
            $rules['details'] = 'required|string|min:5';
        } elseif ($this->type === 'solicitar_diploma') {
            $rules['selectedTargetId'] = 'required|integer|exists:enrollments,id';
            $rules['details'] = 'nullable|string'; 
        }

        return $rules;
    }

    public function updatedType()
    {
        $this->reset('details', 'selectedTargetId');
        $this->resetErrorBag();
        
        if ($this->type === 'solicitar_diploma') {
            $this->details = "Solicito la emisión del diploma correspondiente al curso finalizado.";
        }
    }

    public function submitRequest()
    {
        $this->validate();

        if (!$this->student) {
            session()->flash('error', 'No se pudo encontrar el perfil de estudiante.');
            return;
        }

        // 1. Buscamos la inscripción directamente en la BD para asegurar integridad de datos
        // Esto soluciona el problema de "Curso No especificado"
        $enrollment = Enrollment::with('courseSchedule.module.course')
            ->where('student_id', $this->student->id)
            ->where('id', $this->selectedTargetId)
            ->first();

        if (!$enrollment) {
            session()->flash('error', 'La inscripción seleccionada no es válida.');
            return;
        }

        $finalDetails = $this->details;
        $courseId = $enrollment->course_id; // Obtenemos el ID del curso de forma segura

        // 2. Lógica específica por tipo
        if (in_array($this->type, ['retiro_curso', 'cambio_seccion'])) {
            
            $courseName = $enrollment->courseSchedule->module->course->name ?? 'Curso Desconocido'; 
            $sectionName = $enrollment->courseSchedule->section_name ?? 'Sección Desconocida';
            
            $finalDetails = "Curso Afectado: $courseName (Sección: $sectionName)\n" .
                            "ID Inscripción: $enrollment->id\n\n" .
                            "Motivo: $this->details";

        } elseif ($this->type === 'solicitar_diploma') {
            if (!$this->canRequestDiploma) {
                session()->flash('error', 'No cumple los requisitos para solicitar diploma.');
                return;
            }

            // Validar nuevamente el estado por seguridad
            if (!in_array(strtolower($enrollment->status), ['completado', 'aprobado'])) {
                 session()->flash('error', 'El curso seleccionado no consta como completado en el sistema.');
                 return;
            }

            $courseName = $enrollment->courseSchedule->module->course->name ?? 'Curso';
            
            // Verificar duplicados pendientes o aprobados
            $existingRequest = StudentRequest::where('student_id', $this->student->id)
                ->where('course_id', $courseId)
                ->where('type', 'solicitar_diploma')
                ->whereIn('status', ['pendiente', 'aprobado'])
                ->exists();
            
            if ($existingRequest) {
                session()->flash('error', 'Ya tiene una solicitud de diploma activa para este curso.');
                return;
            }

            $finalDetails = "Solicitud de Diploma para el curso: $courseName.\n" .
                            "Fecha de finalización: " . ($enrollment->updated_at?->format('d/m/Y') ?? 'N/A');
        }

        // 3. Crear la solicitud con el course_id asegurado
        StudentRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $courseId, // <--- Aquí estaba el problema, ahora está garantizado
            'type' => $this->type,
            'details' => $finalDetails,
            'status' => 'pendiente',
        ]);

        session()->flash('success', 'Solicitud enviada correctamente. Espere la aprobación del administrador.');
        
        $this->reset('type', 'details', 'selectedTargetId');
        $this->mount(); // Recargar listas
    }

    public function render()
    {
        $studentRequests = $this->student 
            ? $this->student->requests()->with(['payment', 'course'])->latest()->paginate(10) 
            : collect();

        return view('livewire.student-portal.requests', [
            'studentRequests' => $studentRequests,
        ]);
    }
}