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

        // 1. Obtener la inscripción con todas las relaciones anidadas necesarias
        $enrollment = Enrollment::with(['courseSchedule.module.course'])
            ->where('student_id', $this->student->id)
            ->where('id', $this->selectedTargetId)
            ->first();

        if (!$enrollment) {
            session()->flash('error', 'La inscripción seleccionada no es válida.');
            return;
        }

        // 2. Extracción ROBUSTA del ID del Curso
        $courseId = null;
        $courseName = 'Curso Desconocido';

        // Intentamos obtener el ID navegando por las relaciones
        if ($enrollment->courseSchedule && $enrollment->courseSchedule->module) {
            $courseId = $enrollment->courseSchedule->module->course_id; // ID directo desde el módulo
            
            // Si el objeto curso está cargado, obtenemos el nombre
            if ($enrollment->courseSchedule->module->course) {
                $courseName = $enrollment->courseSchedule->module->course->name;
                // Por seguridad, si el ID anterior falló, usamos el del objeto
                if (!$courseId) {
                    $courseId = $enrollment->courseSchedule->module->course->id;
                }
            }
        }

        // Validación Crítica: Si no tenemos ID de curso, no podemos proceder
        if (!$courseId) {
            session()->flash('error', 'Error técnico: No se pudo identificar el ID del curso asociado a esta inscripción. Por favor contacte soporte.');
            return;
        }

        $finalDetails = $this->details;

        // 3. Lógica específica por tipo
        if (in_array($this->type, ['retiro_curso', 'cambio_seccion'])) {
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
                 session()->flash('error', "El curso seleccionado ($courseName) no consta como completado.");
                 return;
            }

            // Verificar duplicados
            $existingRequest = StudentRequest::where('student_id', $this->student->id)
                ->where('course_id', $courseId)
                ->where('type', 'solicitar_diploma')
                ->whereIn('status', ['pendiente', 'aprobado'])
                ->first();
            
            if ($existingRequest) {
                session()->flash('error', "Ya existe una solicitud activa para el curso: $courseName.");
                return;
            }

            $finalDetails = "Solicitud de Diploma para el curso: $courseName.\n" .
                            "Fecha de finalización: " . ($enrollment->updated_at?->format('d/m/Y') ?? 'N/A');
        }

        // 4. Crear la solicitud
        StudentRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $courseId,
            'type' => $this->type,
            'details' => $finalDetails,
            'status' => 'pendiente',
        ]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        
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