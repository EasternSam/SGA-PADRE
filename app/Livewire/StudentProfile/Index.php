<?php

namespace App\Livewire\StudentProfile;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // <-- Asegúrate de que este 'use' esté
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
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
    // public $activeTab = 'enrollments'; // Se maneja con Alpine en la vista
    
    // Propiedades para la paginación de pagos
    public $paymentsPage = 1;

    // --- ¡AÑADIDO! ---
    // Escucha el evento 'paymentCreated' del modal y refresca el componente
    // Y 'studentUpdated' para refrescar cuando se guarda el modal de edición
    // --- ¡MODIFICACIÓN! Se añade 'flashMessage' ---
    // --- ¡MODIFICACIÓN CLAVE! Cambiamos '$refresh' por 'refreshPaymentList' ---
    protected $listeners = [
        'paymentCreated' => 'refreshPaymentList', // <--- AQUÍ ESTÁ EL CAMBIO
        'studentUpdated' => '$refresh',
        'flashMessage' // Escucha el evento de flash message
    ];


    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
        'enrollmentStatusFilter' => ['except' => 'all'],
        // 'activeTab' => ['except' => 'enrollments'], // Se maneja con Alpine
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

    /*
    // Este método ya no es necesario si se usa Alpine.js para las pestañas
    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage('page'); // Restablece la paginación de cursos/módulos
        $this->resetPage('paymentsPage'); // Restablece la paginación de pagos
    }
    */

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

    // --- ¡MÉTODO AÑADIDO! ---
    /**
     * Escucha el evento 'paymentCreated' y resetea la paginación de pagos.
     * Esto fuerza a Livewire a recargar la consulta de pagos desde la página 1.
     */
    public function refreshPaymentList()
    {
        $this->resetPage('paymentsPage');
    }

    // --- ¡¡¡MÉTODO PARA REPORTE!!! ---
    /**
     * Prepara y emite el evento para abrir el reporte en una nueva pestaña.
     */
    public function generateReport()
    {
        // --- ¡¡¡MODIFICACIÓN PARA DEPURAR!!! ---
        Log::info('El método generateReport FUE LLAMADO.');
        // --- FIN DE LA MODIFICACIÓN ---
        
        // Obtiene la URL de la ruta del reporte (definida en routes/web.php)
        // --- ¡CORRECCIÓN! El nombre de la ruta es 'reports.student-report' ---
        $url = route('reports.student-report', $this->student->id);
        
        Log::info('URL Generada para el Reporte: ' . $url);

        // --- ¡¡¡LA CORRECCIÓN ESTÁ AQUÍ!!! ---
        // Usamos parámetros nombrados (sintaxis de Livewire 3)
        $this->dispatch('open-pdf-modal', url: $url);
    }
    // --- FIN DEL MÉTODO DE REPORTE ---


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
        $this->dispatch('close-modal', 'course-modal');
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
            $this->days = $schedule->days_of_week ?? [];
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
                'days_of_week' => $this->days,
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

    // --- ¡¡¡INICIO CÓDIGO AÑADIDO PARA MODAL DE ESTUDIANTE!!! ---

    // Propiedades para el modal de Estudiante (Copiadas de Students/Index.php)
    public $student_id;
    public $first_name = '';
    public $last_name = '';
    public $cedula = ''; // o dni
    public $email = '';
    public $mobile_phone = ''; // Teléfono móvil (principal)
    public $home_phone = ''; // Teléfono casa
    public $address = '';
    public $city = '';
    public $sector = '';
    public $birth_date; // Fecha de nacimiento
    public $gender = '';
    public $nationality = '';
    public $how_found = '';
    
    // Propiedades del Tutor (si es menor)
    public $is_minor = false;
    public $tutor_name = '';
    public $tutor_cedula = '';
    public $tutor_phone = '';
    public $tutor_relationship = '';
    
    // Propiedades de la UI
    public $modalTitle = '';

    /**
     * Reglas de validación para el Estudiante
     */
    protected function studentRules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('students')->ignore($this->student_id),
            ],
            'cedula' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('students')->ignore($this->student_id),
            ],
            'mobile_phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:255',
            'is_minor' => 'boolean',
            'tutor_name' => 'required_if:is_minor,true|nullable|string|max:255',
            'tutor_cedula' => 'nullable|string|max:20',
            'tutor_phone' => 'required_if:is_minor,true|nullable|string|max:20',
            'tutor_relationship' => 'nullable|string|max:100',
        ];
    }

    /**
     * Mensajes de validación personalizados para Estudiante
     */
    protected $studentMessages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El formato del correo no es válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'tutor_name.required_if' => 'El nombre del tutor es obligatorio si el estudiante es menor de edad.',
        'tutor_phone.required_if' => 'El teléfono del tutor es obligatorio si el estudiante es menor de edad.',
    ];

    /**
     * Abre el modal para editar el estudiante actual del perfil.
     */
    public function editStudent()
    {
        $this->resetValidation(); // Limpiar errores previos
        $this->student_id = $this->student->id; // Carga el ID del estudiante del perfil
        
        // Cargar los datos del estudiante (ya cargado en $this->student) en las propiedades del formulario
        $this->first_name = $this->student->first_name;
        $this->last_name = $this->student->last_name;
        $this->cedula = $this->student->cedula;
        $this->email = $this->student->email;
        $this->mobile_phone = $this->student->mobile_phone;
        $this->home_phone = $this->student->home_phone;
        $this->address = $this->student->address;
        $this->city = $this->student->city;
        $this->sector = $this->student->sector;
        // Formatear la fecha para el input 'date'
        $this->birth_date = $this->student->birth_date ? Carbon::parse($this->student->birth_date)->format('Y-m-d') : null;
        $this->gender = $this->student->gender;
        $this->nationality = $this->student->nationality;
        $this->how_found = $this->student->how_found;
        $this->is_minor = (bool)$this->student->is_minor; // Asegurar que sea booleano
        $this->tutor_name = $this->student->tutor_name;
        $this->tutor_cedula = $this->student->tutor_cedula;
        $this->tutor_phone = $this->student->tutor_phone;
        $this->tutor_relationship = $this->student->tutor_relationship;

        $this->modalTitle = 'Editar Estudiante: ' . $this->student->fullName;
        
        // Dispara el evento para abrir el modal (el HTML debe estar en la vista)
        $this->dispatch('open-modal', 'student-form-modal');
    }

    /**
     * Guarda los cambios del estudiante.
     */
    public function saveStudent()
    {
        // Valida usando las reglas y mensajes de estudiante
        $this->validate($this->studentRules(), $this->studentMessages);

        try {
            // Busca al estudiante (asegura que sea el del perfil)
            $student = Student::findOrFail($this->student_id);
            
            // Actualiza los datos
            $student->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'cedula' => $this->cedula,
                'email' => $this->email,
                'mobile_phone' => $this->mobile_phone,
                'home_phone' => $this->home_phone,
                'address' => $this->address,
                'city' => $this->city,
                'sector' => $this->sector,
                'birth_date' => $this->birth_date,
                'gender' => $this->gender,
                'nationality' => $this->nationality,
                'how_found' => $this->how_found,
                'is_minor' => $this->is_minor,
                'tutor_name' => $this->is_minor ? $this->tutor_name : null,
                'tutor_cedula' => $this->is_minor ? $this->tutor_cedula : null,
                'tutor_phone' => $this->is_minor ? $this->tutor_phone : null,
                'tutor_relationship' => $this->is_minor ? $this->tutor_relationship : null,
            ]);

            session()->flash('message', 'Estudiante actualizado exitosamente.');
            $this->closeStudentModal(); // Cierra el modal
            
            // Emite un evento para refrescar el componente del perfil
            $this->dispatch('studentUpdated'); 
            
            // Recarga los datos del estudiante en la propiedad $student
            $this->student = $student->fresh();

        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Cierra el modal de estudiante y resetea los campos.
     */
    public function closeStudentModal()
    {
        $this->dispatch('close-modal', 'student-form-modal');
        $this->resetStudentInputFields();
        $this->resetValidation(); // Limpia los errores de validación
    }

    /**
     * Resetea todos los campos del formulario de estudiante.
     */
    private function resetStudentInputFields()
    {
        $this->student_id = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->cedula = '';
        $this->email = '';
        $this->mobile_phone = '';
        $this->home_phone = '';
        $this->address = '';
        $this->city = '';
        $this->sector = '';
        $this->birth_date = null;
        $this->gender = '';
        $this->nationality = '';
        $this->how_found = '';
        $this->is_minor = false;
        $this->tutor_name = '';
        $this->tutor_cedula = '';
        $this->tutor_phone = '';
        $this->tutor_relationship = '';
        $this->modalTitle = '';
    }
    // --- ¡¡¡FIN CÓDIGO AÑADIDO!!! ---
}