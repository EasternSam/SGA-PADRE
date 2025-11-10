<?php

namespace App\Livewire\StudentProfile;

use Livewire\Component;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination; // <-- ¡¡¡REPARACIÓN!!! Añadir paginación

#[Layout('app')]
class Index extends Component
{
    use WithPagination; // <-- ¡¡¡REPARACIÓN!!! Usar paginación

    public Student $student;

    // --- Propiedades para el modal de inscripción ---
    public $isEnrollModalOpen = false;
    public $courses = [];
    public $modules = [];
    public $schedules = [];
    public $selectedCourseId;
    public $selectedModuleId;
    public $selectedScheduleId;
    public $selectedScheduleInfo; // Para mostrar detalles

    // --- Propiedades para el modal de anulación ---
    public $isUnenrollModalOpen = false;
    public $enrollmentToCancel;

    // --- ¡¡¡REPARACIÓN!!! Propiedades para el filtro de la vista ---
    public $enrollmentStatusFilter = 'all';

    protected $queryString = [
        'enrollmentStatusFilter' => ['except' => 'all']
    ];


    public function mount(Student $student)
    {
        // ¡¡¡REPARACIÓN!!!
        // Ya no cargamos las inscripciones aquí, 'render' lo hará.
        $this->student = $student->load('user');
    }

    public function render()
    {
        // --- ¡¡¡REPARACIÓN!!! Lógica de paginación ---
        $enrollmentsQuery = $this->student->enrollments()
            ->with('courseSchedule.module.course', 'courseSchedule.teacher')
            ->orderBy('created_at', 'desc');

        // Aplicar el filtro si no es 'all'
        if ($this->enrollmentStatusFilter !== 'all') {
            $enrollmentsQuery->where('status', $this->enrollmentStatusFilter);
        }

        // Paginar los resultados
        $enrollments = $enrollmentsQuery->paginate(10); // 10 por página

        return view('livewire.student-profile.index', [
            'enrollments' => $enrollments, // <-- Pasamos la data paginada a la vista
        ]);
    }

    /**
     * ¡¡¡REPARACIÓN!!!
     * Cuando el filtro cambia, resetea a la página 1
     */
    public function updatedEnrollmentStatusFilter()
    {
        $this->resetPage();
    }

    // --- LÓGICA DE INSCRIPCIÓN ---

    public function openEnrollModal()
    {
        // Esta lógica parece estar desincronizada con la vista actual, 
        // pero la mantendremos por ahora para no romper otra cosa.
        // La vista actual usa 'searchAvailableCourse'
        $this->courses = Course::orderBy('name')->get();
        $this->modules = collect();
        $this->schedules = collect();
        $this->resetEnrollmentSelection();
        $this->isEnrollModalOpen = true;

        // Despachar evento para el modal de la vista (que se llama 'enroll-student-modal')
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function closeEnrollModal()
    {
        // $this->isEnrollModalOpen = false; // Esta propiedad no es usada por los modales 'name'
        $this->resetEnrollmentSelection();
        // ¡¡¡REPARACIÓN!!! Ser específico sobre qué modal cerrar
        $this->dispatch('close-modal', 'enroll-student-modal');
    }

    private function resetEnrollmentSelection()
    {
        $this->selectedCourseId = null;
        $this->selectedModuleId = null;
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
    }

    // --- Métodos 'updated' para selects dependientes ---

    public function updatedSelectedCourseId($courseId)
    {
        if ($courseId) {
            $this->modules = Module::where('course_id', $courseId)->orderBy('name')->get();
        } else {
            $this->modules = collect();
        }
        $this->schedules = collect();
        $this->selectedModuleId = null;
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
    }

    public function updatedSelectedModuleId($moduleId)
    {
        if ($moduleId) {
            $this->schedules = CourseSchedule::where('module_id', $moduleId)
                                ->with('teacher')
                                ->get();
        } else {
            $this->schedules = collect();
        }
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
    }

    public function updatedSelectedScheduleId($scheduleId)
    {
        if ($scheduleId) {
            $this->selectedScheduleInfo = CourseSchedule::with('teacher')->find($scheduleId);
        } else {
            $this->selectedScheduleInfo = null;
        }
    }

    /**
     * Inscribe al estudiante en la sección seleccionada
     */
    public function enrollStudent()
    {
        // 1. Validar que se seleccionó una sección
        if (!$this->selectedScheduleId) {
            session()->flash('error', 'Debes seleccionar una sección válida.');
            return;
        }

        // 2. Validar que el schedule existe
        $schedule = CourseSchedule::find($this->selectedScheduleId);
        if (!$schedule) {
            session()->flash('error', 'La sección seleccionada no existe.');
            return;
        }

        // 3. Revisar si ya está inscrito
        $isAlreadyEnrolled = Enrollment::where('student_id', $this->student->id)
            ->where('course_schedule_id', $this->selectedScheduleId)
            ->exists();

        if ($isAlreadyEnrolled) {
            session()->flash('error', 'El estudiante ya está inscrito en esta sección.');
            return;
        }

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->selectedScheduleId,
            'status' => 'active',
        ]);

        session()->flash('message', 'Estudiante inscrito correctamente.');
        
        // ¡¡¡REPARACIÓN!!! closeEnrollModal() ya hace el dispatch específico
        $this->closeEnrollModal(); 
        $this->resetPage(); // <-- ¡¡¡REPARACIÓN!!! Refresca la paginación
    }

    // --- LÓGICA DE ANULAR INSCRIPCIÓN ---
    // (Ajustada a la lógica del modal de la vista)

    public $confirmingUnenroll = false; // Lo mantenemos por si la vista x-modal lo usa internamente
    public $enrollmentToCancelId = null;

    public function confirmUnenroll($enrollmentId)
    {
        $this->enrollmentToCancelId = $enrollmentId;
        // $this->confirmingUnenroll = true; // <-- Ya no es necesario para ABRIR
        
        // ¡¡¡REPARACIÓN!!! Despachamos el evento para abrir el modal por su nombre
        $this->dispatch('open-modal', 'confirm-unenroll-modal');
    }

    public function unenroll() // Coincide con wire:click="unenroll"
    {
        if (!$this->enrollmentToCancelId) {
            return;
        }
        
        $enrollment = Enrollment::find($this->enrollmentToCancelId);

        if ($enrollment) {
            try {
                // ¡¡¡REPARACIÓN!!! 
                // Cambiado de 'cancelar' (actualizar estado) a 'eliminar' (borrar registro)
                $enrollment->delete();
                
                // Mensaje actualizado
                session()->flash('message', 'Inscripción eliminada correctamente.');

            } catch (\Exception $e) {
                // Mensaje actualizado
                session()->flash('error', 'No se pudo eliminar la inscripción.');
                Log::error("Error al eliminar inscripción: " . $e->getMessage());
            }
        }
        
        // $this->confirmingUnenroll = false; // <-- Ya no es necesario
        $this->enrollmentToCancelId = null;
        $this->resetPage(); // <-- ¡¡¡REPARACIÓN!!! Refresca la paginación

        // ¡¡¡REPARACIÓN!!! Ser específico sobre qué modal cerrar
        $this->dispatch('close-modal', 'confirm-unenroll-modal');
    }


    /**
     * Helper para recargar la data del estudiante (ahora solo resetea la pág)
     */
    private function refreshStudentData()
    {
        // ¡¡¡REPARACIÓN!!!
        // render() ahora maneja la carga de inscripciones.
        // Solo reseteamos la página y refrescamos el estudiante.
        $this->resetPage();
        $this->student = $this->student->fresh('user');
    }

    // --- Lógica para el modal de inscripción (basado en la vista) ---
    // (Añadido para compatibilidad con la vista)
    
    public $searchAvailableCourse = '';
    public $availableSchedules = [];
    
    public function updatedSearchAvailableCourse()
    {
        if (strlen($this->searchAvailableCourse) < 3) {
            $this->availableSchedules = [];
            return;
        }

        $this->availableSchedules = CourseSchedule::with('module.course', 'teacher')
            ->where(function($query) {
                $query->where('section_name', 'like', '%'.$this->searchAvailableCourse.'%')
                    ->orWhereHas('module', function($q) {
                        $q->where('name', 'like', '%'.$this->searchAvailableCourse.'%');
                    })
                    ->orWhereHas('module.course', function($q) {
                        $q->where('name', 'like', '%'.$this->searchAvailableCourse.'%');
                    });
            })
            // Opcional: Excluir secciones en las que ya está inscrito
            ->whereDoesntHave('enrollments', function($q) {
                $q->where('student_id', $this->student->id);
            })
            ->get();
    }

    // (La vista usa 'openEditModal' pero no está implementado, lo dejamos así)
    public function openEditModal()
    {
        $this->dispatch('open-modal', 'edit-student-modal');
    }

    // --- ¡¡¡AQUÍ ESTÁ LA SOLUCIÓN!!! ---
    /**
     * Soluciona el error 'MethodNotFoundException'.
     * Despacha un evento para abrir la ruta del reporte en una nueva pestaña.
     */
    public function generateReport()
    {
        // Obtenemos la URL de la ruta del reporte
        $url = route('reports.student-report', ['student' => $this->student->id]);
        
        // Usamos $this->dispatch() para enviar un evento a JavaScript (Alpine)
        // para abrir la URL en una nueva pestaña.
        $this->dispatch('open-new-tab', $url);
    }
}