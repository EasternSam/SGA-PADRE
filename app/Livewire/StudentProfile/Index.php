<?php

namespace App\Livewire\StudentProfile;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Models\Student; // <-- Añadido
use Illuminate\Support\Facades\Auth; // <-- Corregido
use Illuminate\Support\Facades\Log; // <-- Añadido
use Illuminate\Validation\Rule; // <-- Añadido
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon; // <-- Añadido
use Livewire\Attributes\Layout; // <-- ¡AÑADIDO! Importa el atributo de Layout

#[Layout('layouts.dashboard')] // <-- ¡AÑADIDO! Especifica el layout correcto
class Index extends Component
{
    use WithPagination;

    public $student;
    public $search = '';
    public $selectedCourse;
    public $selectedModule;

    // Propiedades para el modal de Cursos (Ajustadas a la DB)
    public $course_id, $course_name, $course_credits, $course_code;
    public $courseModalTitle = '';

    // Propiedades para el modal de Módulos (Ajustadas a la DB)
    public $module_id, $module_name;
    public $moduleModalTitle = '';

    // Propiedades para el modal de Horarios (Sección)
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $scheduleModalTitle = '';
    public $teachers = []; // Para el dropdown de profesores

    // Propiedades para el modal de inscripción
    public $isEnrollModalOpen = false;
    public $availableSchedules = [];
    public $searchAvailableCourse = '';
    public $selectedScheduleId;
    public $selectedScheduleInfo;

    // Propiedades para el modal de anulación
    public $isUnenrollModalOpen = false;
    public $enrollmentToCancel;
    public $enrollmentToCancelId = null;

    // Propiedades para el filtro de la vista
    public $enrollmentStatusFilter = 'all';

    // Propiedad para la pestaña activa
    public $activeTab = 'enrollments';

    // Propiedades para la paginación de pagos
    public $paymentsPage = 1;

    // --- ¡AÑADIDO! ---
    // Escucha el evento 'paymentCreated' del modal y refresca el componente
    protected $listeners = ['paymentCreated' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
        'enrollmentStatusFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'enrollments'],
    ];

    public function mount(Student $student)
    {
        $this->student = $student->load('user');
        $this->teachers = User::role('Profesor')->orderBy('name')->get(); // Asumiendo que tienes un rol 'Teacher'
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEnrollmentStatusFilter()
    {
        $this->resetPage('enrollmentsPage');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage('page'); // Restablece la paginación de cursos/módulos
        $this->resetPage('paymentsPage'); // Restablece la paginación de pagos
    }

    public function render()
    {
        $coursesQuery = Course::query();

        if ($this->search) {
            $coursesQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $courses = $coursesQuery->with(['modules.schedules.teacher'])->paginate(10);

        $selectedCourseObject = $this->selectedCourse ? $courses->find($this->selectedCourse) : null;
        $modules = $selectedCourseObject ? $selectedCourseObject->modules : collect();

        $schedules = $this->selectedModule
            ? CourseSchedule::where('module_id', $this->selectedModule)->with('teacher')->get()
            : collect();
        
        $selectedModuleName = $this->selectedModule ? Module::find($this->selectedModule)?->name : null;

        // Lógica para las inscripciones del estudiante
        $enrollmentsQuery = $this->student->enrollments()
            ->with('courseSchedule.module.course', 'courseSchedule.teacher')
            ->orderBy('created_at', 'desc');

        if ($this->enrollmentStatusFilter !== 'all') {
            $enrollmentsQuery->where('status', $this->enrollmentStatusFilter);
        }

        $enrollments = $enrollmentsQuery->paginate(5, ['*'], 'enrollmentsPage'); // Paginar inscripciones

        // Lógica para el historial de pagos del estudiante
        $payments = $this->student->payments()
            ->with('paymentConcept', 'user') // Cargar relaciones
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'paymentsPage'); // Paginar pagos

        return view('livewire.student-profile.index', [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name,
            'selectedModuleName' => $selectedModuleName,
            'enrollments' => $enrollments, // Pasar inscripciones paginadas
            'payments' => $payments,       // Pasar pagos paginados
        ]);
    }

    // ... (otros métodos como createCourse, editCourse, saveCourse, createModule, editModule, saveModule, createSchedule, editSchedule, saveSchedule, selectCourse, selectModule, clearFilters) ...
    // Asegúrate de que los métodos save/close modal reseteen la paginación si es necesario o usen $this->dispatch('refreshComponent')

    // --- Métodos de Inscripción (Enrollment) ---

    public function openEnrollmentModal()
    {
        $this->resetEnrollmentSelection();
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect();
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function updatedSearchAvailableCourse()
    {
        if (strlen($this->searchAvailableCourse) < 3) {
            $this->availableSchedules = collect();
            return;
        }

        $this->availableSchedules = CourseSchedule::with('module.course', 'teacher')
            ->where(function ($query) {
                $query->where('section_name', 'like', '%' . $this->searchAvailableCourse . '%')
                    ->orWhereHas('module', function ($q) {
                        $q->where('name', 'like', '%' . $this->searchAvailableCourse . '%')
                          ->orWhereHas('course', function ($sq) {
                              $sq->where('name', 'like', '%' . $this->searchAvailableCourse . '%')
                                 ->orWhere('code', 'like', '%' . $this->searchAvailableCourse . '%');
                          });
                    });
            })
            ->whereDoesntHave('enrollments', function ($q) {
                $q->where('student_id', $this->student->id);
            })
            ->get();
    }
    
    // Función helper para resetear el modal de inscripción
    private function resetEnrollmentSelection()
    {
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect();
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
        $this->resetErrorBag();
    }

    public function enrollStudent()
    {
        $this->validate([
            'selectedScheduleId' => 'required|exists:course_schedules,id',
        ]);

        $schedule = CourseSchedule::find($this->selectedScheduleId);

        // Verificar si ya está inscrito
        $isAlreadyEnrolled = Enrollment::where('student_id', $this->student->id)
            ->where('course_schedule_id', $this->selectedScheduleId)
            ->exists();

        if ($isAlreadyEnrolled) {
            session()->flash('error', 'El estudiante ya está inscrito en esta sección.');
            $this->dispatch('close-modal', 'enroll-student-modal');
            return;
        }

        // Crear la inscripción
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $this->selectedScheduleId,
            'status' => 'Enrolled', // O el estado por defecto que manejes
        ]);

        session()->flash('message', 'Estudiante inscrito exitosamente.');
        $this->dispatch('close-modal', 'enroll-student-modal');
        $this->resetPage('enrollmentsPage'); // Refrescar la lista de inscripciones
    }

    // --- Métodos para Desinscribir (Unenroll) ---
    public function confirmUnenroll($enrollmentId)
    {
        $this->enrollmentToCancelId = $enrollmentId;
        $this->dispatch('open-modal', 'confirm-unenroll-modal');
    }

    public function unenroll()
    {
        if ($this->enrollmentToCancelId) {
            try {
                $enrollment = Enrollment::find($this->enrollmentToCancelId);
                if ($enrollment && $enrollment->student_id == $this->student->id) {
                    $enrollment->delete();
                    session()->flash('message', 'Inscripción eliminada.');
                } else {
                    session()->flash('error', 'No se pudo encontrar la inscripción.');
                }
            } catch (\Exception $e) {
                Log::error('Error al eliminar inscripción: ' . $e->getMessage());
                session()->flash('error', 'No se pudo eliminar la inscripción.');
            }
        }
        $this->dispatch('close-modal', 'confirm-unenroll-modal');
        $this->resetPage('enrollmentsPage'); // Refrescar la lista de inscripciones
        $this->enrollmentToCancelId = null;
    }

    // --- Métodos para Modal de Curso ---
    protected function courseRules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'course_credits' => 'required|integer|min:0',
            'course_code' => [
                'required',
                'string',
                Rule::unique('courses', 'code')->ignore($this->course_id)
            ],
        ];
    }

    public function createCourse()
    {
        $this->resetCourseFields();
        $this->resetValidation();
        $this->courseModalTitle = 'Crear Nuevo Curso';
        $this->dispatch('open-modal', 'course-modal'); 
    }

    public function editCourse($courseId)
    {
        $this->resetValidation();
        try {
            $course = Course::findOrFail($courseId);
            $this->course_id = $course->id;
            $this->course_name = $course->name;
            $this->course_credits = $course->credits;
            $this->course_code = $course->code;
            
            $this->courseModalTitle = 'Editar Curso: ' . $course->name;
            $this->dispatch('open-modal', 'course-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Curso no encontrado.');
        }
    }

    public function saveCourse()
    {
        $this->validate($this->courseRules());

        Course::updateOrCreate(
            ['id' => $this->course_id],
            [
                'name' => $this->course_name,
                'credits' => $this->course_credits,
                'code' => $this->course_code,
            ]
        );

        session()->flash('message', $this->course_id ? 'Curso actualizado.' : 'Curso creado.');
        $this->dispatch('close-modal', 'course-modal'); // <-- Corregido 'thiss'
    }

    private function resetCourseFields()
    {
        $this->course_id = null;
        $this->course_name = '';
        $this->course_credits = 0;
        $this->course_code = '';
    }

    // --- MÉTODOS PARA MODAL DE MÓDULO ---
    protected function moduleRules()
    {
        return [
            'module_name' => 'required|string|max:255',
        ];
    }

    public function createModule()
    {
        if (!$this->selectedCourse) {
            session()->flash('error', 'Debes seleccionar un curso primero.');
            return;
        }
        $this->resetModuleFields();
        $this->resetValidation();
        $this->moduleModalTitle = 'Nuevo Módulo para ' . Course::find($this->selectedCourse)->name;
        $this->dispatch('open-modal', 'module-modal'); 
    }

    public function editModule($moduleId)
    {
        $this->resetValidation();
        try {
            $module = Module::findOrFail($moduleId);
            $this->module_id = $module->id;
            $this->module_name = $module->name;

            $this->moduleModalTitle = 'Editar Módulo: ' . $module->name;
            $this->dispatch('open-modal', 'module-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Módulo no encontrado.');
        }
    }

    public function saveModule()
    {
        $this->validate($this->moduleRules());

        Module::updateOrCreate(
            ['id' => $this->module_id],
            [
                'course_id' => $this->selectedCourse,
                'name' => $this->module_name,
            ]
        );

        session()->flash('message', $this->module_id ? 'Módulo actualizado.' : 'Módulo creado.');
        $this->dispatch('close-modal', 'module-modal'); 
    }

    private function resetModuleFields()
    {
        $this->module_id = null;
        $this->module_name = '';
    }
    
    // --- MÉTODOS PARA MODAL DE HORARIO (SECCIÓN) ---
    public function createSchedule()
    {
        if (!$this->selectedModule) {
            session()->flash('error', 'Debes seleccionar un módulo primero.');
            return;
        }
        $this->resetScheduleFields();
        $this->resetValidation();
        $this->scheduleModalTitle = 'Nueva Sección para ' . Module::find($this->selectedModule)->name;
        $this->dispatch('open-modal', 'schedule-modal'); 
    }

    public function editSchedule($scheduleId)
    {
        $this->resetValidation();
        try {
            $schedule = CourseSchedule::findOrFail($scheduleId);
            $this->schedule_id = $schedule->id;
            $this->teacher_id = $schedule->teacher_id;
            $this->days = $schedule->days_of_week ?? []; // <-- Corregido
            $this->section_name = $schedule->section_name;
            $this->start_time = $schedule->start_time ? Carbon::parse($schedule->start_time)->format('H:i') : null;
            $this->end_time = $schedule->end_time ? Carbon::parse($schedule->end_time)->format('H:i') : null;
            $this->start_date = $schedule->start_date ? Carbon::parse($schedule->start_date)->format('Y-m-d') : null;
            $this->end_date = $schedule->end_date ? Carbon::parse($schedule->end_date)->format('Y-m-d') : null;
           
            $this->scheduleModalTitle = 'Editar Sección';
            $this->dispatch('open-modal', 'schedule-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Horario no encontrado.');
            Log::error('Error al editar horario: ' . $e->getMessage());
        }
    }

    public function saveSchedule()
    {
        $this->validate([
            'teacher_id' => 'required|exists:users,id',
            'days' => 'required|array|min:1',
            'section_name' => 'nullable|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        CourseSchedule::updateOrCreate(
            ['id' => $this->schedule_id],
            [
                'module_id' => $this->selectedModule,
                'teacher_id' => $this->teacher_id,
                'days_of_week' => $this->days, // <-- Corregido
                'section_name' => $this->section_name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]
        );

        session()->flash('message', $this->schedule_id ? 'Sección actualizada.' : 'Sección creada.');
        $this->dispatch('close-modal', 'schedule-modal'); 
    }

    private function resetScheduleFields()
    {
        $this->schedule_id = null;
        $this->teacher_id = null;
        $this->days = [];
        $this->start_time = '';
        $this->end_time = '';
        $this->section_name = '';
        $this->start_date = '';
        $this->end_date = '';
    }
}