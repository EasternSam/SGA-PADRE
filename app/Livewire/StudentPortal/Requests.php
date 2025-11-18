<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\StudentRequest;
use App\Models\Enrollment; // Asegúrate de que esté importado
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule; // <-- AÑADIR ESTA LÍNEA

#[Layout('layouts.dashboard')] // <-- CORREGIDO: para coincidir con tu Dashboard
class Requests extends Component
{
    use WithPagination;

    public $type = '';
    public $details = '';
    public $selectedEnrollmentId = '';

    public $student;
    public $activeEnrollments = [];
    public $canRequestDiploma = false;

    // Opciones para el dropdown de tipos de solicitud
    public $requestTypes = [
        'retiro_curso' => 'Retiro de Curso',
        'cambio_seccion' => 'Cambio de Sección',
        'solicitar_diploma' => 'Solicitar Diploma',
        'revision_calificacion' => 'Solicitar Revisión de Calificación',
        'otro' => 'Otro',
    ];

    /**
     * Carga los datos iniciales del estudiante.
     */
    public function mount()
    {
        $this->student = Auth::user()->student;

        if (!$this->student) {
            // Si no hay estudiante, no podemos hacer nada.
            // La vista mostrará un error o estado vacío.
            return;
        }

        // --- CORRECCIÓN ---
        // Usar los mismos estados que Dashboard.php para 'activos'
        $this->activeEnrollments = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', [
                'Cursando', 'cursando', 
                'Activo', 'activo'
            ]) // <-- LÓGICA DE ESTADOS ACTUALIZADA
            ->with('courseSchedule.module.course') // <-- CORRECCIÓN AQUÍ
            ->get();

        // --- CORRECCIÓN ---
        // Usar los mismos estados que Dashboard.php para 'completados'
        $completedCount = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', [
                'Completado', 'completado'
            ]) // <-- LÓGICA DE ESTADOS ACTUALIZADA
            ->count();

        $this->canRequestDiploma = $completedCount > 0;
    }

    /**
     * Reglas de validación dinámicas.
     */
    public function rules()
    {
        $rules = [
            // --- CORRECCIÓN AQUÍ ---
            // 'in_array_key' no existe. La forma correcta es usar Rule::in() con las llaves (keys) del array.
            'type' => ['required', 'string', Rule::in(array_keys($this->requestTypes))],
            'details' => 'required|string|min:10',
        ];

        // Si el tipo es retiro o cambio, el ID de la inscripción es requerido
        if ($this->type === 'retiro_curso' || $this->type === 'cambio_seccion') {
            $rules['selectedEnrollmentId'] = 'required|integer|exists:enrollments,id';
            $rules['details'] = 'required|string|min:5'; // El motivo puede ser más corto
        }

        return $rules;
    }

    /**
     * Se ejecuta cuando la propiedad $type cambia.
     */
    public function updatedType()
    {
        // Limpiar los campos dependientes cuando cambia el tipo
        $this->reset('details', 'selectedEnrollmentId');
        $this->resetErrorBag(); // Limpiar errores de validación anteriores
    }

    /**
     * Guarda la nueva solicitud.
     */
    public function submitRequest()
    {
        $this->validate();

        if (!$this->student) {
            session()->flash('error', 'No se pudo encontrar el perfil de estudiante asociado a su usuario.');
            return;
        }

        // Lógica para deshabilitar el envío de diploma si no es elegible
        if ($this->type === 'solicitar_diploma' && !$this->canRequestDiploma) {
            session()->flash('error', 'Usted no cumple los requisitos para solicitar un diploma en este momento.');
            return;
        }

        $finalDetails = $this->details;

        // Si es un retiro o cambio, adjuntar la información del curso a los detalles
        if ($this->type === 'retiro_curso' || $this->type === 'cambio_seccion') {
            // Volver a cargar la colección si se perdió (aunque no debería pasar)
            if ($this->activeEnrollments->isEmpty()) {
                $this->mount(); // Recarga los datos
            }

            $enrollment = $this->activeEnrollments->find($this->selectedEnrollmentId);
            
            if ($enrollment) {
                // --- CORRECCIÓN AQUÍ ---
                $courseName = $enrollment->courseSchedule->module->course->name ?? 'Curso Desconocido'; 
                $sectionName = $enrollment->courseSchedule->section_name ?? 'Sección Desconocida';
                
                $finalDetails = "Curso Afectado: $courseName (Sección: $sectionName)\n" .
                                "ID Inscripción: $this->selectedEnrollmentId\n\n" .
                                "Motivo: $this->details";
            } else {
                // Fallback por si no se encuentra
                $finalDetails = "ID Inscripción (no encontrado en lista): $this->selectedEnrollmentId\n\n" .
                                "Motivo: $this->details";
            }
        }

        StudentRequest::create([
            'student_id' => $this->student->id,
            'type' => $this->type,
            'details' => $finalDetails, // Guardar los detalles formateados
            'status' => 'pendiente',
        ]);

        session()->flash('success', 'Solicitud enviada correctamente.');
        
        // Resetear todo el formulario
        $this->reset('type', 'details', 'selectedEnrollmentId');
        
        // Recargar las inscripciones activas por si una fue retirada (aunque aquí solo se solicita)
        $this->mount();
    }

    /**
     * Renderiza el componente.
     */
    public function render()
    {
        $studentRequests = $this->student ? $this->student->requests()->latest()->paginate(10) : collect();

        return view('livewire.student-portal.requests', [
            'studentRequests' => $studentRequests,
        ]);
    }
}