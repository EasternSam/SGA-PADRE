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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator; 
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\DB; // <-- AÑADIDO: Para transacciones
use Illuminate\Support\Facades\Hash; // <-- AÑADIDO: No estaba, pero es bueno tenerlo por si acaso

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    // --- Propiedades del Estudiante y Datos ---
    public Student $student;
    public ?User $user; 
    
    // --- Propiedades de Filtro y Búsqueda (Originales) ---
    public $search = '';
    public $selectedCourse;
    public $selectedModule;
    public $enrollmentStatusFilter = 'all'; 
    public $paymentsPage = 1; // El 'name' de la paginación de pagos

    // --- Propiedades del Modal de Cursos (Originales) ---
    public $course_id, $course_name, $course_credits, $course_code;
    public $courseModalTitle = '';

    // --- Propiedades del Modal de Módulos (Originales) ---
    public $module_id, $module_name, $module_price; // <-- AÑADIDO module_price
    public $moduleModalTitle = '';
    
    // --- Propiedades del Modal de Horarios (Originales) ---
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $scheduleModalTitle = '';
    public $teachers = [];

    // --- Propiedades del Modal de Inscripción (Originales) ---
    public $isEnrollModalOpen = false;
    public $availableSchedules = [];
    public $searchAvailableCourse = '';
    public $selectedScheduleId;
    public $selectedScheduleInfo;

    // --- Propiedades del Modal de Anulación (Originales) ---
    public $isUnenrollModalOpen = false;
    public $enrollmentToCancel; // <-- Mantenemos este por si lo usas en la vista
    public $enrollmentToCancelId = null;

    // --- Propiedades del Modal de Estudiante (Originales) ---
    public $student_id;
    public $first_name = '';
    public $last_name = '';
    public $cedula = '';
    public $email = '';
    public $mobile_phone = '';
    public $home_phone = '';
    public $address = '';
    public $city = '';
    public $sector = '';
    public $birth_date;
    public $gender = '';
    public $nationality = '';
    public $how_found = '';
    public $is_minor = false;
    public $tutor_name = '';
    public $tutor_cedula = '';
    public $tutor_phone = '';
    public $tutor_relationship = '';
    public $modalTitle = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
        'enrollmentStatusFilter' => ['except' => 'all'],
    ];

    /**
     * Carga inicial del componente.
     */
    public function mount(Student $student)
    {
        $this->teachers = User::role('Profesor')->orderBy('name')->get();
        $this->loadStudentData($student->id); // Cargar solo datos del estudiante
    }

    /**
     * (ACTUALIZADO) Carga/Recarga solo los datos del estudiante.
     * Las inscripciones se manejan en render()
     */
    public function loadStudentData($studentId)
    {
        try {
            // Cargar estudiante CON su usuario
            $student = Student::with('user')->findOrFail($studentId);
            $this->student = $student;
            $this->user = $student->user; // Cargar el usuario vinculado

        } catch (\Exception $e) {
            Log::error("Error cargando datos del estudiante: " . $e->getMessage());
            session()->flash('error', 'No se pudieron cargar los datos del estudiante.');
            // Opcional: Redirigir si el estudiante no se encuentra
            // return redirect()->route('admin.students.index');
        }
    }
    
    /**
     * (ACTUALIZADO) Se dispara cuando se añade un pago o se actualiza el estudiante.
     * Recarga toda la data.
     */
    #[On('paymentAdded')]
    #[On('studentUpdated')]
    public function refreshData()
    {
        // Recarga solo los datos base del estudiante (email, nombre, etc.)
        $this->loadStudentData($this->student->id); 
        
        // Resetea las paginaciones. Esto forzará a render() a re-ejecutarse
        // y re-consultar la BD para las inscripciones y pagos.
        $this->resetPage('paymentsPage'); 
        $this->resetPage('enrollmentsPage'); 
    }
    
    /**
     * (ORIGINAL) Escucha el evento 'flashMessage'.
     */
    #[On('flashMessage')]
    public function handleFlashMessage()
    {
        // Este método existe para "atrapar" el evento.
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingEnrollmentStatusFilter()
    {
        $this->resetPage('enrollmentsPage');
    }

    /**
     * (ACTUALIZADO) Renderiza la vista.
     */
    public function render()
    {
        // --- Lógica de Gestión Académica (Original) ---
        $coursesQuery = Course::query();

        if ($this->search) {
            $coursesQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        // Paginar cursos
        $courses = $coursesQuery->with(['modules.schedules.teacher'])->paginate(10, ['*'], 'coursesPage'); // Añadido nombre a paginación

        $selectedCourseObject = $this->selectedCourse ? Course::find($this->selectedCourse) : null;
        $modules = $selectedCourseObject ? $selectedCourseObject->modules : collect();

        $schedules = $this->selectedModule
            ? CourseSchedule::where('module_id', $this->selectedModule)->with('teacher')->get()
            : collect();
        
        $selectedModuleName = $this->selectedModule ? Module::find($this->selectedModule)?->name : null;
        // --- Fin Lógica Gestión Académica ---


        // --- Lógica de Carga de Inscripciones y Pagos ---
        
        $pendingEnrollments = collect();
        $activeEnrollments = collect();
        $completedEnrollments = collect();
        $filteredEnrollments = collect();
        $payments = collect();
        $pendingPayments = collect(); // Para la alerta

        if ($this->student) {
            // 1. Cargar inscripciones para la VISTA GENERAL (Overview)
            $allEnrollments = $this->student->enrollments()
                ->with('courseSchedule.module.course', 'courseSchedule.teacher', 'payment')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $pendingEnrollments = $allEnrollments->whereIn('status', ['Pendiente', 'pendiente', 'Enrolled', 'enrolled']);
            $activeEnrollments = $allEnrollments->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo']);
            $completedEnrollments = $allEnrollments->whereIn('status', ['Completado', 'completado']);

            // 2. Cargar inscripciones para la PESTAÑA DE INSCRIPCIONES (Paginada)
            $filteredEnrollmentsQuery = $this->student->enrollments()
                                 ->with('courseSchedule.module.course', 'courseSchedule.teacher')
                                 ->orderBy('created_at', 'desc');

            if ($this->enrollmentStatusFilter !== 'all') {
                $filteredEnrollmentsQuery->where('status', $this->enrollmentStatusFilter);
            }

            $filteredEnrollments = $filteredEnrollmentsQuery->paginate(5, ['*'], 'enrollmentsPage');


            // 3. Cargar PAGOS (Paginado)
            $allPayments = $this->student->payments()
                ->with('paymentConcept', 'enrollment.courseSchedule.module.course', 'user') //Añadido 'user'
                ->orderBy('created_at', 'desc')
                ->get();

            $payments = new LengthAwarePaginator(
                $allPayments->forPage($this->getPage('paymentsPage'), 5),
                $allPayments->count(),
                5,
                $this->getPage('paymentsPage'),
                ['pageName' => 'paymentsPage']
            );
            
            // 4. Cargar Pagos Pendientes (para la alerta)
            $pendingPayments = $allPayments->where('status', 'Pendiente');
        }


        return view('livewire.student-profile.index', [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name,
            'selectedModuleName' => $selectedModuleName,
            
            // Pasar las variables calculadas a la vista
            'pendingEnrollments' => $pendingEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'pendingPayments' => $pendingPayments, // Para la alerta
            'enrollments' => $filteredEnrollments, 
            'payments' => $payments,
        ]);
    }


    /**
     * (ORIGINAL) Prepara y emite el evento para abrir el reporte en una nueva pestaña.
     */
    public function generateReport()
    {
        Log::info('El método generateReport FUE LLAMADO.');
        $url = route('reports.student-report', $this->student->id);
        Log::info('URL Generada para el Reporte: ' . $url);
        $this->dispatch('open-pdf-modal', url: $url);
    }

    // --- Métodos de Inscripción (Enrollment) ---

    // (ORIGINAL)
    public function openEnrollmentModal()
    {
        $this->resetEnrollmentSelection();
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect();
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    // (ORIGINAL)
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
            // Excluir secciones donde el estudiante ya está inscrito (independientemente del estado)
            ->whereDoesntHave('enrollments', function ($q) {
                $q->where('student_id', $this->student->id);
            })
            ->get();
    }
    
    // (ORIGINAL)
    private function resetEnrollmentSelection()
    {
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect();
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
        $this->resetErrorBag();
    }

    /**
     * (ACTUALIZADO) Inscribir estudiante
     * Ahora crea la inscripción como 'Pendiente' y genera un pago pendiente.
     */
    public function enrollStudent()
    {
        $this->validate([
            'selectedScheduleId' => 'required|exists:course_schedules,id',
        ]);

        try {
            $schedule = CourseSchedule::with('module')->findOrFail($this->selectedScheduleId);

            // Validar reglas de negocio (Cupos, Balance, Fecha)
            
            // 1. Balance
            if ($this->student->balance > 0) {
                throw new \Exception('El estudiante tiene un balance pendiente y no puede inscribirse.');
            }

            // 2. Cupos
            $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
                                         ->whereIn('status', ['Activo', 'Cursando', 'Pendiente']) // Contar también pendientes
                                         ->count();
            if ($schedule->capacity !== null && $schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) {
                throw new \Exception('La sección está llena. No hay cupos disponibles.');
            }

            // 3. Fecha de inicio (Corregido: no se puede inscribir si la fecha de inicio YA PASÓ)
            if ($schedule->start_date && Carbon::parse($schedule->start_date)->lt(Carbon::today())) {
                throw new \Exception('Este curso ya ha comenzado. No se permiten nuevas inscripciones.');
            }

            // 4. Verificar si ya está inscrito
            $isAlreadyEnrolled = Enrollment::where('student_id', $this->student->id)
                ->where('course_schedule_id', $this->selectedScheduleId)
                ->exists();

            if ($isAlreadyEnrolled) {
                throw new \Exception('El estudiante ya está inscrito en esta sección.');
            }

            // Crear la inscripción como PENDIENTE
            $enrollment = Enrollment::create([
                'student_id' => $this->student->id,
                'course_schedule_id' => $this->selectedScheduleId,
                'status' => 'Pendiente', // <-- ACTUALIZADO
            ]);

            // Crear el PAGO PENDIENTE
            Payment::create([
                'student_id' => $this->student->id,
                'enrollment_id' => $enrollment->id,
                'payment_concept_id' => $schedule->module->payment_concept_id ?? null,
                'amount' => $schedule->module->price ?? 0,
                'currency' => 'DOP',
                'status' => 'Pendiente', // <-- ACTUALIZADO
                'gateway' => 'Por Pagar',
                'user_id' => Auth::id(), // <-- AÑADIDO: Quién registró el pago pendiente
            ]);

            session()->flash('message', 'Inscripción pendiente de pago creada exitosamente.');
            $this->dispatch('close-modal', 'enroll-student-modal');
            $this->refreshData(); // Recargar todos los datos

        } catch (\Exception $e) {
            Log::error('Error al inscribir estudiante: ' . $e->getMessage());
            // Mostrar el error de la regla de negocio
            $this->addError('selectedScheduleId', $e->getMessage());
            // No cerramos el modal si hay error
        }
    }


    // --- Métodos para Desinscribir (Unenroll) ---
    // (ORIGINAL)
    public function confirmUnenroll($enrollmentId)
    {
        $this->enrollmentToCancelId = $enrollmentId;
        $this->dispatch('open-modal', 'confirm-unenroll-modal');
    }

    /**
     * (ACTUALIZADO) Desinscribir estudiante.
     * Ahora también borra el pago pendiente si existe.
     */
    public function unenroll()
    {
        if ($this->enrollmentToCancelId) {
            try {
                $enrollment = Enrollment::with('payment')->find($this->enrollmentToCancelId);
                
                if ($enrollment && $enrollment->student_id == $this->student->id) {
                    
                    // Si la inscripción tiene un pago asociado Y está pendiente, bórralo también
                    if ($enrollment->payment && $enrollment->payment->status == 'Pendiente') {
                        $enrollment->payment->delete();
                    }
                    
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
        $this->refreshData(); // Recargar todos los datos
        $this->enrollmentToCancelId = null;
    }

    // --- Métodos para Modal de Curso ---
    // (El resto de métodos de Curso, Módulo y Horario se mantienen como en tu original)
    // (... métodos createCourse, editCourse, saveCourse, resetCourseFields ...)
    // (... métodos createModule, editModule, saveModule, resetModuleFields ...)
    // (... métodos createSchedule, editSchedule, saveSchedule, resetScheduleFields ...)
    
    // (ORIGINAL)
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

    // (ORIGINAL)
    public function createCourse()
    {
        $this->resetCourseFields();
        $this->resetValidation();
        $this->courseModalTitle = 'Crear Nuevo Curso';
        $this->dispatch('open-modal', 'course-modal'); 
    }

    // (ORIGINAL)
    public function editCourse($courseId)
    {
        $this->resetValidation();
        try {
            $course = Course::findOrFail($courseId);
            $this->course_id = $course->id;
            $this->course_name = $course->name;
            $this->course_credits = $course->credits ?? 0;
            $this->course_code = $course->code;
            
            $this->courseModalTitle = 'Editar Curso: ' . $course->name;
            $this->dispatch('open-modal', 'course-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Curso no encontrado.');
        }
    }

    // (ORIGINAL)
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
        $this->refreshData(); // Recargar
    }

    // (ORIGINAL)
    private function resetCourseFields()
    {
        $this->course_id = null;
        $this->course_name = '';
        $this->course_credits = 0;
        $this->course_code = '';
    }

    // --- MÉTODOS PARA MODAL DE MÓDULO ---
    // (ORIGINAL)
    protected function moduleRules()
    {
        return [
            'module_name' => 'required|string|max:255',
            'module_price' => 'required|numeric|min:0', // <-- AÑADIDO
        ];
    }

    // (ORIGINAL - MODIFICADO para precio)
    public function createModule()
    {
        if (!$this->selectedCourse) {
            session()->flash('error', 'Debes seleccionar un curso primero.');
            return;
        }
        $this->resetModuleFields();
        $this->resetValidation();
        $courseName = Course::find($this->selectedCourse)->name ?? 'Curso';
        $this->moduleModalTitle = 'Nuevo Módulo para ' . $courseName;
        $this->dispatch('open-modal', 'module-modal'); 
    }

    // (ORIGINAL - MODIFICADO para precio)
    public function editModule($moduleId)
    {
        $this->resetValidation();
        try {
            $module = Module::findOrFail($moduleId);
            $this->module_id = $module->id;
            $this->module_name = $module->name;
            $this->module_price = $module->price ?? 0; // <-- AÑADIDO

            $this->moduleModalTitle = 'Editar Módulo: ' . $module->name;
            $this->dispatch('open-modal', 'module-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Módulo no encontrado.');
        }
    }

    // (ORIGINAL - MODIFICADO para precio)
    public function saveModule()
    {
        $this->validate($this->moduleRules());

        Module::updateOrCreate(
            ['id' => $this->module_id],
            [
                'course_id' => $this->selectedCourse,
                'name' => $this->module_name,
                'price' => $this->module_price, // <-- AÑADIDO
            ]
        );

        session()->flash('message', $this->module_id ? 'Módulo actualizado.' : 'Módulo creado.');
        $this->dispatch('close-modal', 'module-modal'); 
        $this->refreshData(); // Recargar
    }

    // (ORIGINAL - MODIFICADO para precio)
    private function resetModuleFields()
    {
        $this->module_id = null;
        $this->module_name = '';
        $this->module_price = 0; // <-- AÑADIDO
    }
    
    // --- MÉTODOS PARA MODAL DE HORARIO (SECCIÓN) ---
    // (ORIGINAL)
    public function createSchedule()
    {
        if (!$this->selectedModule) {
            session()->flash('error', 'Debes seleccionar un módulo primero.');
            return;
        }
        $this->resetScheduleFields();
        $this->resetValidation();
        $moduleName = Module::find($this->selectedModule)->name ?? 'Módulo';
        $this->scheduleModalTitle = 'Nueva Sección para ' . $moduleName;
        $this->dispatch('open-modal', 'schedule-modal'); 
    }

    // (ORIGINAL)
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

    // (ORIGINAL)
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
        $this->refreshData(); // Recargar
    }

    // (ORIGINAL)
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


    // --- Métodos para Modal de Estudiante (Edición Perfil) ---
    
    /**
     * (ACTUALIZADO) Reglas de validación para el formulario de estudiante.
     */
    protected function studentRules()
    {
        // Asegurarse de que $this->user está cargado
        if (!$this->user) {
            $this->loadStudentData($this->student->id);
        }
        
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students')->ignore($this->student_id), // Ignora al estudiante actual
                Rule::unique('users')->ignore($this->user ? $this->user->id : null), // Ignora al usuario actual
            ],
            'cedula' => [
                'required', // <-- ACTUALIZADO: Hecho obligatorio
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

    // (ORIGINAL)
    protected $studentMessages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El formato del correo no es válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'cedula.required' => 'La cédula es obligatoria.', // <-- AÑADIDO
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'tutor_name.required_if' => 'El nombre del tutor es obligatorio si el estudiante es menor de edad.',
        'tutor_phone.required_if' => 'El teléfono del tutor es obligatorio si el estudiante es menor de edad.',
    ];

    // (ORIGINAL)
    public function editStudent()
    {
        $this->resetValidation(); 
        $this->student_id = $this->student->id;
        
        $this->first_name = $this->student->first_name;
        $this->last_name = $this->student->last_name;
        $this->cedula = $this->student->cedula;
        $this->email = $this->student->email;
        $this->mobile_phone = $this->student->mobile_phone;
        $this->home_phone = $this->student->home_phone;
        $this->address = $this->student->address;
        $this->city = $this->student->city;
        $this->sector = $this->student->sector;
        $this->birth_date = $this->student->birth_date ? Carbon::parse($this->student->birth_date)->format('Y-m-d') : null;
        $this->gender = $this->student->gender;
        $this->nationality = $this->student->nationality;
        $this->how_found = $this->student->how_found;
        $this->is_minor = (bool)$this->student->is_minor;
        $this->tutor_name = $this->student->tutor_name;
        $this->tutor_cedula = $this->student->tutor_cedula;
        $this->tutor_phone = $this->student->tutor_phone;
        $this->tutor_relationship = $this->student->tutor_relationship;

        $this->modalTitle = 'Editar Estudiante: ' . $this->student->fullName;
        
        $this->dispatch('open-modal', 'student-form-modal');
    }

    /**
     * (ACTUALIZADO) Guarda los cambios del estudiante.
     * Ahora también actualiza el 'User' asociado y maneja la verificación de email.
     */
    public function saveStudent()
    {
        $this->validate($this->studentRules(), $this->studentMessages);

        try {
            DB::transaction(function() {
                // Actualizar el modelo Student (usando $this->student para más claridad)
                $this->student->update([
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
                    'tutor_phone' => $this->is_minor ? $this->tutor_phone : null, // Corregido typo
                    'tutor_relationship' => $this->is_minor ? $this->tutor_relationship : null,
                ]);

                // (ACTUALIZADO) Actualizar el 'User' asociado también
                if ($this->user) { // Usar $this->user que se cargó en mount/loadStudentData
                    $userData = [
                        'name' => $this->first_name . ' ' . $this->last_name,
                    ];

                    $emailChanged = $this->user->email !== $this->email;
                    
                    // Solo actualiza el email si no es el email institucional
                    if (!Str::endsWith($this->user->getOriginal('email'), '@centu.edu.do')) {
                         $userData['email'] = $this->email;
                    }

                    // Si el email cambió, marcar como no verificado
                    if ($emailChanged && $this->user->email !== $this->email) {
                        $userData['email_verified_at'] = null; 
                    }

                    $this->user->update($userData);
                    
                    // Si el email cambió, reenviar verificación
                    if ($emailChanged && !$this->user->hasVerifiedEmail()) {
                        // Nota: El modelo User debe usar 'MustVerifyEmail' para que esto funcione
                        // $this->user->sendEmailVerificationNotification();
                    }
                }
            }); // Fin de la transacción

            session()->flash('message', 'Estudiante actualizado exitosamente.');
            $this->closeStudentModal();
            
            $this->dispatch('studentUpdated'); // <-- Esto disparará 'refreshData()'
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Los errores de validación se mostrarán automáticamente
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el estudiante: ' . $e->getMessage());
        }
    }

    // (ORIGINAL)
    public function closeStudentModal()
    {
        $this->dispatch('close-modal', 'student-form-modal');
        $this->resetStudentInputFields();
        $this->resetValidation();
    }

    // (ORIGINAL)
    private function resetStudentInputFields()
    {
        // Resetea los campos al estado actual del estudiante, no a vacío
        if ($this->student) {
            $this->student_id = $this->student->id;
            $this->first_name = $this->student->first_name;
            $this->last_name = $this->student->last_name;
            $this->cedula = $this->student->cedula;
            $this->email = $this->student->email;
            $this->mobile_phone = $this->student->mobile_phone;
            $this->home_phone = $this->student->home_phone;
            $this->address = $this->student->address;
            $this->city = $this->student->city;
            $this->sector = $this->student->sector;
            $this->birth_date = $this->student->birth_date ? Carbon::parse($this->student->birth_date)->format('Y-m-d') : null;
            $this->gender = $this->student->gender;
            $this->nationality = $this->student->nationality;
            $this->how_found = $this->student->how_found;
            $this->is_minor = (bool)$this->student->is_minor;
            $this->tutor_name = $this->student->tutor_name;
            $this->tutor_cedula = $this->student->tutor_cedula;
            $this->tutor_phone = $this->student->tutor_phone;
            $this->tutor_relationship = $this->student->tutor_relationship;
        }
        $this->modalTitle = '';
    }
}