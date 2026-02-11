<?php

namespace App\Livewire\StudentProfile;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Models\Student;
use App\Models\PaymentConcept; 
use App\Models\ActivityLog; // Importado
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
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash; 

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    // --- Propiedades del Estudiante y Datos ---
    public Student $student;
    public ?User $user; 
    
    // --- Propiedades de Filtro y Búsqueda ---
    public $search = '';
    public $selectedCourse;
    public $selectedModule;
    public $enrollmentStatusFilter = 'all'; 
    public $paymentsPage = 1; 

    // --- Propiedades del Modal de Cursos ---
    public $course_id, $course_name, $course_credits, $course_code;
    public $courseModalTitle = '';

    // --- Propiedades del Modal de Módulos ---
    public $module_id, $module_name, $module_price; 
    public $moduleModalTitle = '';
    
    // --- Propiedades del Modal de Horarios ---
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $scheduleModalTitle = '';
    public $teachers = [];

    // --- Propiedades del Modal de Inscripción ---
    public $isEnrollModalOpen = false;
    public Collection $availableSchedules; 
    public $searchAvailableCourse = '';
    public $selectedScheduleId;
    public $selectedScheduleInfo;

    // --- Propiedades del Modal de Anulación ---
    public $isUnenrollModalOpen = false;
    public $enrollmentToCancel; 
    public $enrollmentToCancelId = null;

    // --- Propiedades del Modal de Estudiante ---
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

    public function mount(Student $student)
    {
        $this->teachers = User::role('Profesor')->orderBy('name')->get();
        $this->availableSchedules = collect(); 
        $this->loadStudentData($student->id); 
    }

    public function loadStudentData($studentId)
    {
        try {
            $student = Student::with('user')->findOrFail($studentId);
            $this->student = $student;
            $this->user = $student->user; 

        } catch (\Exception $e) {
            Log::error("Error cargando datos del estudiante: " . $e->getMessage());
            session()->flash('error', 'No se pudieron cargar los datos del estudiante.');
        }
    }
    
    #[On('paymentAdded')]
    #[On('studentUpdated')]
    public function refreshData()
    {
        $this->loadStudentData($this->student->id); 
        $this->resetPage('paymentsPage'); 
        $this->resetPage('enrollmentsPage'); 
    }
    
    #[On('flashMessage')]
    public function handleFlashMessage()
    {
        // Listener
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingEnrollmentStatusFilter()
    {
        $this->resetPage('enrollmentsPage');
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

        $courses = $coursesQuery->with(['modules.schedules.teacher'])->paginate(10, ['*'], 'coursesPage');

        $selectedCourseObject = $this->selectedCourse ? Course::find($this->selectedCourse) : null;
        $modules = $selectedCourseObject ? $selectedCourseObject->modules : collect();

        $schedules = $this->selectedModule
            ? CourseSchedule::where('module_id', $this->selectedModule)->with('teacher')->get()
            : collect();
        
        $selectedModuleName = $this->selectedModule ? Module::find($this->selectedModule)?->name : null;

        $pendingEnrollments = collect();
        $activeEnrollments = collect();
        $completedEnrollments = collect();
        $filteredEnrollments = collect();
        $payments = collect();
        $pendingPayments = collect(); 

        if ($this->student) {
            $allEnrollments = $this->student->enrollments()
                ->with('courseSchedule.module.course', 'courseSchedule.teacher', 'payment')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $pendingEnrollments = $allEnrollments->whereIn('status', ['Pendiente', 'pendiente', 'Enrolled', 'enrolled']);
            $activeEnrollments = $allEnrollments->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo']);
            $completedEnrollments = $allEnrollments->whereIn('status', ['Completado', 'completado']);

            $filteredEnrollmentsQuery = $this->student->enrollments()
                                                     ->with('courseSchedule.module.course', 'courseSchedule.teacher')
                                                     ->orderBy('created_at', 'desc');

            if ($this->enrollmentStatusFilter !== 'all') {
                $filteredEnrollmentsQuery->where('status', $this->enrollmentStatusFilter);
            }

            $filteredEnrollments = $filteredEnrollmentsQuery->paginate(5, ['*'], 'enrollmentsPage');

            $allPayments = $this->student->payments()
                ->with('paymentConcept', 'enrollment.courseSchedule.module.course', 'user') 
                ->orderBy('created_at', 'desc')
                ->get();

            $payments = new LengthAwarePaginator(
                $allPayments->forPage($this->getPage('paymentsPage'), 5),
                $allPayments->count(),
                5,
                $this->getPage('paymentsPage'),
                ['pageName' => 'paymentsPage']
            );
            
            $pendingPayments = $allPayments->where('status', 'Pendiente');
        }

        return view('livewire.student-profile.index', [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name,
            'selectedModuleName' => $selectedModuleName,
            'pendingEnrollments' => $pendingEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'pendingPayments' => $pendingPayments, 
            'enrollments' => $filteredEnrollments, 
            'payments' => $payments,
        ]);
    }

    public function generateReport()
    {
        $url = route('reports.student-report', $this->student->id);
        $this->dispatch('open-pdf-modal', url: $url);
    }

    public function openEnrollmentModal()
    {
        $this->resetEnrollmentSelection();
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect(); 
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function updatedSearchAvailableCourse()
    {
        if (empty($this->searchAvailableCourse)) {
            $this->availableSchedules = collect();
            return;
        }

        $term = $this->searchAvailableCourse;

        $this->availableSchedules = CourseSchedule::with(['module.course', 'teacher'])
            ->where(function ($query) use ($term) {
                $query->where('section_name', 'like', '%' . $term . '%')
                    ->orWhereHas('module', function ($q) use ($term) {
                        $q->where('name', 'like', '%' . $term . '%')
                            ->orWhereHas('course', function ($sq) use ($term) {
                                $sq->where('name', 'like', '%' . $term . '%')
                                    ->orWhere('code', 'like', '%' . $term . '%');
                            });
                    });
            })
            ->whereDoesntHave('enrollments', function ($q) {
                $q->where('student_id', $this->student->id);
            })
            ->limit(20) 
            ->get();
    }
    
    private function resetEnrollmentSelection()
    {
        $this->searchAvailableCourse = '';
        $this->availableSchedules = collect();
        $this->selectedScheduleId = null;
        $this->selectedScheduleInfo = null;
        $this->resetErrorBag();
    }

    public function updatedSelectedScheduleId($value)
    {
        if ($value) {
            $this->selectedScheduleInfo = CourseSchedule::with('module.course', 'teacher')->find($value);
        } else {
            $this->selectedScheduleInfo = null;
        }
    }

    public function enrollStudent()
    {
        $this->validate([
            'selectedScheduleId' => 'required|exists:course_schedules,id',
        ]);

        try {
            $schedule = CourseSchedule::with('module.course')->findOrFail($this->selectedScheduleId);

            $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
                                       ->whereIn('status', ['Activo', 'Cursando', 'Pendiente'])
                                       ->count();
            if ($schedule->capacity !== null && $schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) {
                throw new \Exception('La sección está llena.');
            }

            if ($schedule->start_date && Carbon::parse($schedule->start_date)->lt(Carbon::today())) {
                throw new \Exception('Este curso ya ha comenzado.');
            }

            $isAlreadyEnrolled = Enrollment::where('student_id', $this->student->id)
                ->where('course_schedule_id', $this->selectedScheduleId)
                ->exists();

            if ($isAlreadyEnrolled) {
                throw new \Exception('El estudiante ya está inscrito en esta sección.');
            }

            DB::transaction(function () use ($schedule) {
                $enrollment = Enrollment::create([
                    'student_id' => $this->student->id,
                    'course_id' => $schedule->module->course_id, 
                    'course_schedule_id' => $this->selectedScheduleId,
                    'status' => 'Pendiente',
                ]);

                // --- CORREGIDO: Eliminar amount de PaymentConcept ---
                
                $inscriptionConcept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'], 
                    ['description' => 'Pago único de inscripción al curso'] // Sin amount
                );

                $amount = $schedule->module->course->registration_fee ?? 0;

                Payment::create([
                    'student_id' => $this->student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $inscriptionConcept->id,
                    'amount' => $amount, 
                    'currency' => 'DOP',
                    'status' => 'Pendiente',
                    'gateway' => 'Sistema',
                    'user_id' => Auth::id(),
                    'due_date' => now()->addDays(3),
                ]);
            });

            // LOGICA DE LOG (Ahora redundante con el observer, pero útil para debug)
            // El Observer se encargará.

            session()->flash('message', 'Inscripción creada exitosamente. Se generó el cargo de inscripción.');
            $this->dispatch('close-modal', 'enroll-student-modal');
            $this->refreshData(); 

        } catch (\Exception $e) {
            Log::error('Error al inscribir: ' . $e->getMessage());
            $this->addError('selectedScheduleId', $e->getMessage());
        }
    }

    public function confirmUnenroll($enrollmentId)
    {
        $this->enrollmentToCancelId = $enrollmentId;
        $this->dispatch('open-modal', 'confirm-unenroll-modal');
    }

    public function unenroll()
    {
        if ($this->enrollmentToCancelId) {
            try {
                $enrollment = Enrollment::with('payment')->find($this->enrollmentToCancelId);
                
                if ($enrollment && $enrollment->student_id == $this->student->id) {
                    // Log Antes de eliminar (si el observer de delete falla o son soft deletes)
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'Anulación Manual',
                        'description' => "Usuario anuló inscripción de {$this->student->full_name} ID #{$enrollment->id}",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);

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
        $this->refreshData(); 
        $this->enrollmentToCancelId = null;
    }

    // --- Métodos CRUD ---
    protected function courseRules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'course_credits' => 'required|integer|min:0',
            'course_code' => [
                'required', 'string', Rule::unique('courses', 'code')->ignore($this->course_id)
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
            $this->course_credits = $course->credits ?? 0;
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
        $this->refreshData(); 
    }

    private function resetCourseFields()
    {
        $this->course_id = null;
        $this->course_name = '';
        $this->course_credits = 0;
        $this->course_code = '';
    }

    protected function moduleRules()
    {
        return [
            'module_name' => 'required|string|max:255',
            'module_price' => 'required|numeric|min:0', 
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
        $courseName = Course::find($this->selectedCourse)->name ?? 'Curso';
        $this->moduleModalTitle = 'Nuevo Módulo para ' . $courseName;
        $this->dispatch('open-modal', 'module-modal'); 
    }

    public function editModule($moduleId)
    {
        $this->resetValidation();
        try {
            $module = Module::findOrFail($moduleId);
            $this->module_id = $module->id;
            $this->module_name = $module->name;
            $this->module_price = $module->price ?? 0; 

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
                'price' => $this->module_price, 
            ]
        );

        session()->flash('message', $this->module_id ? 'Módulo actualizado.' : 'Módulo creado.');
        $this->dispatch('close-modal', 'module-modal'); 
        $this->refreshData(); 
    }

    private function resetModuleFields()
    {
        $this->module_id = null;
        $this->module_name = '';
        $this->module_price = 0; 
    }
    
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
        $this->refreshData(); 
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

    protected function studentRules()
    {
        if (!$this->user) {
            $this->loadStudentData($this->student->id);
        }
        
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('students')->ignore($this->student_id), 
                Rule::unique('users')->ignore($this->user ? $this->user->id : null), 
            ],
            'cedula' => [
                'required', 'string', 'max:20',
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

    protected $studentMessages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El formato del correo no es válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'cedula.required' => 'La cédula es obligatoria.', 
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'tutor_name.required_if' => 'El nombre del tutor es obligatorio si el estudiante es menor de edad.',
        'tutor_phone.required_if' => 'El teléfono del tutor es obligatorio si el estudiante es menor de edad.',
    ];

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

    public function saveStudent()
    {
        $this->validate($this->studentRules(), $this->studentMessages);

        try {
            DB::transaction(function() {
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
                    'tutor_phone' => $this->is_minor ? $this->tutor_phone : null, 
                    'tutor_relationship' => $this->is_minor ? $this->tutor_relationship : null,
                ]);

                if ($this->user) { 
                    $userData = [
                        'name' => $this->first_name . ' ' . $this->last_name,
                    ];

                    $emailChanged = $this->user->email !== $this->email;
                    
                    if (!Str::endsWith($this->user->getOriginal('email'), '@centu.edu.do')) {
                         $userData['email'] = $this->email;
                    }

                    if ($emailChanged && $this->user->email !== $this->email) {
                        $userData['email_verified_at'] = null; 
                    }

                    $this->user->update($userData);
                }

                // NUEVO LOG DE ACTUALIZACIÓN
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Actualización Perfil',
                    'description' => "Se actualizaron los datos del estudiante {$this->student->full_name}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }); 

            session()->flash('message', 'Estudiante actualizado exitosamente.');
            $this->closeStudentModal();
            $this->dispatch('studentUpdated'); 
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el estudiante: ' . $e->getMessage());
        }
    }

    public function closeStudentModal()
    {
        $this->dispatch('close-modal', 'student-form-modal');
        $this->resetStudentInputFields();
        $this->resetValidation();
    }

    private function resetStudentInputFields()
    {
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