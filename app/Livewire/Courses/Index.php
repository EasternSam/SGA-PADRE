<?php

namespace App\Livewire\Courses;

use Livewire\Component;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\User; 
use App\Models\Classroom; 
use App\Services\ClassroomService;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Validation\Rule; 
use Carbon\Carbon; 
use App\Models\CourseMapping;
use App\Services\WordpressApiService;
use App\Models\ScheduleMapping;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    
    // IDs de selección
    public $selectedCourse;
    public $selectedModule;
    
    // --- Propiedades para el modal de Cursos ---
    public $course_id, $course_name, $course_code;
    public $is_sequential = false;
    public $registration_fee = 0; 
    public $monthly_fee = 0;      
    public $courseModalTitle = '';

    // --- Propiedades para el modal de Módulos ---
    public $module_id, $module_name, $module_price; 
    public $moduleModalTitle = '';
    
    // --- Propiedades para el modal de Horarios ---
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $classroom_id; 
    public $modality = 'Presencial'; 
    public $scheduleModalTitle = '';
    
    // --- Propiedades para Enlace WP ---
    public $currentLinkingCourse;
    public $wpCourses = []; 
    public $selectedWpCourseId = '';
    public $linkFeedbackMessage = '';
    public $linkErrorMessage = '';

    // --- Propiedades para Enlace Sección ---
    public $currentLinkingSection;
    public $wpSchedules = [];
    public $selectedWpScheduleId = '';
    public $sectionLinkErrorMessage = '';

    // === PROPIEDADES PARA ELIMINACIÓN Y LIMPIEZA ===
    public $confirmingClearUnused = false;
    public $unusedCoursesCount = 0;

    // Nuevas propiedades para eliminar individualmente
    public $confirmingDeleteType = ''; // 'course', 'module', 'schedule'
    public $deleteId = null;
    public $deleteMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
    ];

    public function mount()
    {
        // Inicio ligero.
    }

    public function render()
    {
        // 1. CARGA DE CURSOS
        $coursesQuery = Course::query();

        if ($this->search) {
            $coursesQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $courses = $coursesQuery->select('id', 'name', 'code', 'is_sequential', 'registration_fee', 'monthly_fee')
                                ->with('mapping')
                                ->paginate(10); 

        // 2. CARGA DE MÓDULOS
        $selectedCourseObject = null;
        $modules = collect(); 

        if ($this->selectedCourse) {
            $selectedCourseObject = Course::select('id', 'name', 'code')
                ->with('mapping')
                ->find($this->selectedCourse);
            
            if ($selectedCourseObject) {
                $modules = Module::where('course_id', $this->selectedCourse)
                    ->select('id', 'course_id', 'name')
                    ->withCount('schedules')
                    ->paginate(20, ['*'], 'modules-page'); 
            } else {
                $this->reset(['selectedCourse', 'selectedModule']);
            }
        }

        // 3. CARGA DE SECCIONES
        $schedules = collect(); 
        $selectedModuleName = null;

        if ($this->selectedModule) {
            $currentModule = Module::select('id', 'name')->find($this->selectedModule);
            
            if ($currentModule) {
                $selectedModuleName = $currentModule->name;
                
                $schedules = CourseSchedule::where('module_id', $this->selectedModule)
                    ->select('id', 'module_id', 'teacher_id', 'classroom_id', 'days_of_week', 'section_name', 'modality', 'start_time', 'end_time', 'start_date', 'end_date')
                    ->with(['teacher:id,name', 'mapping', 'classroom']) 
                    ->orderBy('start_time')
                    ->paginate(50, ['*'], 'schedules-page');
            } else {
                $this->selectedModule = null;
            }
        }

        // 4. CARGA DE PROFESORES
        $teachersList = Cache::remember('teachers_list_select', 3600, function () {
            try {
                return User::role('Profesor')->select('id', 'name')->orderBy('name')->get();
            } catch (\Exception $e) {
                 return collect();
            }
        });
            
        if($teachersList->isEmpty()) {
             $teachersList = User::select('id', 'name')->orderBy('name')->limit(100)->get();
        }

        // 5. CARGA DE AULAS
        $classroomsList = Cache::remember('classrooms_list_grouped', 3600, function () {
            return Classroom::with('building')->where('is_active', true)->get()->groupBy('building.name');
        });

        return view('livewire.courses.index', [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name,
            'selectedModuleName' => $selectedModuleName,
            'selectedCourseObject' => $selectedCourseObject,
            'teachers' => $teachersList,
            'classroomsGrouped' => $classroomsList
        ]);
    }

    // --- Métodos de Selección ---

    public function selectCourse($courseId)
    {
        $this->selectedCourse = $courseId;
        $this->selectedModule = null; 
        $this->resetPage('modules-page');
        $this->resetPage('schedules-page');
    }

    public function selectModule($moduleId)
    {
        $this->selectedModule = $moduleId;
        $this->resetPage('schedules-page');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedCourse = null;
        $this->selectedModule = null;
    }

    // --- MÉTODOS PARA ELIMINACIÓN INDIVIDUAL (SOFT DELETE) ---
    
    public function confirmDelete($type, $id)
    {
        $this->confirmingDeleteType = $type;
        $this->deleteId = $id;

        if ($type === 'course') {
            $course = Course::find($id);
            $this->deleteMessage = "¿Estás seguro de eliminar el curso '{$course->name}'? Se eliminarán también sus módulos y secciones, pero se mantendrá el historial de los estudiantes.";
        } elseif ($type === 'module') {
            $module = Module::find($id);
            $this->deleteMessage = "¿Estás seguro de eliminar el módulo '{$module->name}'? Se eliminarán sus secciones, pero se mantendrá el historial.";
        } elseif ($type === 'schedule') {
            $schedule = CourseSchedule::find($id);
            $name = $schedule->section_name ?? 'Sección ' . $schedule->id;
            $this->deleteMessage = "¿Estás seguro de eliminar la sección '{$name}'? Los estudiantes inscritos mantendrán este registro en su historial.";
        }

        $this->dispatch('open-modal', 'confirm-delete-modal');
    }

    public function deleteItem()
    {
        if ($this->confirmingDeleteType === 'course') {
            $course = Course::find($this->deleteId);
            if ($course) {
                // Eliminar en cascada suave (Soft Delete manual para limpiar vista)
                foreach ($course->modules as $module) {
                    $module->schedules()->delete(); 
                    $module->delete(); 
                }
                $course->delete(); 
                
                if ($this->selectedCourse == $this->deleteId) {
                    $this->selectedCourse = null;
                    $this->selectedModule = null;
                }
                session()->flash('message', 'Curso eliminado correctamente (Historial preservado).');
            }
        } elseif ($this->confirmingDeleteType === 'module') {
            $module = Module::find($this->deleteId);
            if ($module) {
                $module->schedules()->delete();
                $module->delete(); 
                
                if ($this->selectedModule == $this->deleteId) {
                    $this->selectedModule = null;
                }
                session()->flash('message', 'Módulo eliminado correctamente.');
            }
        } elseif ($this->confirmingDeleteType === 'schedule') {
            $schedule = CourseSchedule::find($this->deleteId);
            if ($schedule) {
                $schedule->delete();
                session()->flash('message', 'Sección eliminada correctamente.');
            }
        }

        $this->dispatch('close-modal', 'confirm-delete-modal');
        $this->reset(['confirmingDeleteType', 'deleteId', 'deleteMessage']);
    }

    // --- MÉTODOS PARA LIMPIEZA DE CURSOS ---

    public function confirmClearUnusedCourses()
    {
        $this->unusedCoursesCount = Course::whereDoesntHave('modules.enrollments')->count();

        if ($this->unusedCoursesCount > 0) {
            $this->confirmingClearUnused = true;
            $this->dispatch('open-modal', 'confirm-clear-unused-modal');
        } else {
            session()->flash('message', 'No hay cursos sin estudiantes para eliminar.');
        }
    }

    public function clearUnusedCourses()
    {
        Course::whereDoesntHave('modules.enrollments')->chunk(100, function ($courses) {
            foreach ($courses as $course) {
                foreach ($course->modules as $module) {
                    $module->schedules()->delete();
                    $module->delete();
                }
                if ($course->mapping) {
                    $course->mapping->delete();
                }
                $course->delete();
            }
        });

        $this->confirmingClearUnused = false;
        $this->unusedCoursesCount = 0;
        
        if ($this->selectedCourse && !Course::find($this->selectedCourse)) {
            $this->selectedCourse = null;
            $this->selectedModule = null;
        }

        session()->flash('message', "Limpieza completada.");
        $this->dispatch('close-modal', 'confirm-clear-unused-modal');
    }

    // --- MÉTODOS PARA MODAL DE CURSO ---
    protected function courseRules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'course_code' => [
                'required', 'string', Rule::unique('courses', 'code')->ignore($this->course_id)
            ],
            'is_sequential' => 'boolean', 
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
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
            $this->course_code = $course->code;
            $this->is_sequential = $course->is_sequential;
            $this->registration_fee = $course->registration_fee;
            $this->monthly_fee = $course->monthly_fee;
            
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
                'code' => $this->course_code,
                'is_sequential' => $this->is_sequential, 
                'registration_fee' => $this->registration_fee,
                'monthly_fee' => $this->monthly_fee,
            ]
        );

        session()->flash('message', $this->course_id ? 'Curso actualizado.' : 'Curso creado.');
        $this->dispatch('close-modal', 'course-modal'); 
    }

    private function resetCourseFields()
    {
        $this->course_id = null;
        $this->course_name = '';
        $this->course_code = '';
        $this->is_sequential = false;
        $this->registration_fee = 0;
        $this->monthly_fee = 0;
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
            $this->classroom_id = $schedule->classroom_id; 
            
            $this->days = $schedule->days_of_week ?? []; 
            
            $this->section_name = $schedule->section_name;
            $this->modality = $schedule->modality ?? 'Presencial';
            
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

    public function saveSchedule(ClassroomService $classroomService) 
    {
        $this->validate([
            'teacher_id' => 'required|exists:users,id',
            'days' => 'required|array|min:1', 
            'section_name' => 'nullable|string|max:100',
            'modality' => 'required|in:Presencial,Virtual,Semi-Presencial',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'classroom_id' => 'nullable|exists:classrooms,id', 
        ]);

        if ($this->classroom_id && in_array($this->modality, ['Presencial', 'Semi-Presencial'])) {
            $availability = $classroomService->checkAvailability(
                $this->classroom_id,
                $this->days,
                $this->start_time,
                $this->end_time,
                $this->start_date,
                $this->end_date,
                $this->schedule_id 
            );

            if ($availability !== true) {
                $this->addError('classroom_id', $availability);
                return; 
            }
        }
        
        if (empty($this->section_name) && $this->classroom_id) {
            $classroom = Classroom::find($this->classroom_id);
            $daysInitials = collect($this->days)->map(fn($d) => substr($d, 0, 2))->join('-');
            $this->section_name = "{$classroom->name} ({$daysInitials})";
        }

        CourseSchedule::updateOrCreate(
            ['id' => $this->schedule_id],
            [
                'module_id' => $this->selectedModule,
                'teacher_id' => $this->teacher_id,
                'classroom_id' => $this->classroom_id, 
                'days_of_week' => $this->days, 
                'section_name' => $this->section_name,
                'modality' => $this->modality,
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
        $this->classroom_id = null; 
        $this->days = [];
        $this->start_time = '';
        $this->end_time = '';
        $this->section_name = '';
        $this->modality = 'Presencial';
        $this->start_date = '';
        $this->end_date = '';
    }

    // --- MÉTODOS PARA ENLACE CON WP (Sin cambios) ---
    public function closeLinkModal() { 
        $this->reset(['currentLinkingCourse', 'selectedWpCourseId', 'linkFeedbackMessage', 'linkErrorMessage']);
    }

    public function openLinkModal($courseId, WordpressApiService $wpService) { 
        $this->reset(['currentLinkingCourse', 'selectedWpCourseId', 'linkFeedbackMessage', 'linkErrorMessage']); 
        try {
            $this->currentLinkingCourse = Course::with('mapping')->findOrFail($courseId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró el curso.');
            return;
        }
        $this->selectedWpCourseId = $this->currentLinkingCourse->mapping->wp_course_id ?? '';
        if (empty($this->wpCourses)) {
            try {
                $this->wpCourses = $wpService->getSgaCourses();
                if (empty($this->wpCourses)) { $this->linkErrorMessage = 'No se pudieron cargar los cursos de WordPress.'; }
            } catch (\Exception $e) {
                Log::error('Error al llamar a WordpressApiService', ['exception' => $e->getMessage()]);
                $this->linkErrorMessage = 'Error al conectar con WordPress.';
            }
        }
        $this->dispatch('open-modal', 'link-wp-modal');
    }

    public function saveLink() { 
        $this->reset(['linkFeedbackMessage', 'linkErrorMessage']);
        if (empty($this->selectedWpCourseId)) {
            if ($this->currentLinkingCourse->mapping) {
                $course = $this->currentLinkingCourse;
                $moduleIds = $course->modules()->pluck('id');
                $scheduleIds = CourseSchedule::whereIn('module_id', $moduleIds)->pluck('id');
                ScheduleMapping::whereIn('course_schedule_id', $scheduleIds)->delete();
                $this->currentLinkingCourse->mapping->delete();
                session()->flash('message', 'Enlace de curso eliminado.');
                $this->currentLinkingCourse->refresh(); 
            }
            $this->dispatch('close-modal', 'link-wp-modal');
            $this->closeLinkModal(); 
            return;
        }
        $selectedWpCourseName = 'Nombre no encontrado';
        foreach ($this->wpCourses as $wpCourse) {
            if ($wpCourse['wp_course_id'] == $this->selectedWpCourseId) {
                $selectedWpCourseName = $wpCourse['wp_course_name'];
                break;
            }
        }
        try {
            CourseMapping::updateOrCreate(
                [ 'course_id' => $this->currentLinkingCourse->id ],
                [ 'wp_course_id' => $this->selectedWpCourseId, 'wp_course_name' => $selectedWpCourseName, ]
            );
            session()->flash('message', 'Curso enlazado exitosamente.');
            $this->currentLinkingCourse->refresh(); 
            $this->dispatch('close-modal', 'link-wp-modal'); 
            $this->closeLinkModal(); 
        } catch (\Exception $e) {
            Log::error('Error al guardar el CourseMapping', ['exception' => $e->getMessage()]);
            $this->linkErrorMessage = 'Error al guardar el enlace.';
        }
    }

    public function closeSectionLinkModal() { $this->reset(['currentLinkingSection', 'wpSchedules', 'selectedWpScheduleId', 'sectionLinkErrorMessage']); }
    
    public function openMapSectionModal($scheduleId, WordpressApiService $wpService) { 
        $this->closeSectionLinkModal(); 
        try {
            $this->currentLinkingSection = CourseSchedule::with('module.course.mapping')->findOrFail($scheduleId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró la sección.');
            return;
        }
        if (!$this->currentLinkingSection->module?->course?->mapping) {
            session()->flash('error', 'Enlace el curso principal primero.');
            return;
        }
        $wpCourseId = $this->currentLinkingSection->module->course->mapping->wp_course_id;
        try {
            $this->wpSchedules = $wpService->getSchedulesForWpCourse($wpCourseId);
            if (empty($this->wpSchedules)) { $this->sectionLinkErrorMessage = 'No se encontraron horarios definidos en WP.'; }
        } catch (\Exception $e) {
            Log::error('Error WP API Schedules', ['exception' => $e->getMessage()]);
            $this->sectionLinkErrorMessage = 'Error al obtener horarios de WP.';
        }
        $existingMapping = ScheduleMapping::where('course_schedule_id', $scheduleId)->first();
        $this->selectedWpScheduleId = $existingMapping->wp_schedule_string ?? ''; 
        $this->dispatch('open-modal', 'link-section-modal');
    }

    public function saveSectionLink() { 
        $this->reset(['sectionLinkErrorMessage']);
        if (!$this->currentLinkingSection || !$this->currentLinkingSection->module?->course?->mapping) { $this->sectionLinkErrorMessage = 'Error de validación.'; return; }
        if (empty($this->selectedWpScheduleId)) {
            ScheduleMapping::where('course_schedule_id', $this->currentLinkingSection->id)->delete();
            session()->flash('message', 'Enlace eliminado.');
            $this->dispatch('close-modal', 'link-section-modal');
            $this->closeSectionLinkModal();
            return;
        }
        try {
            $wpCourseId = $this->currentLinkingSection->module->course->mapping->wp_course_id;
            ScheduleMapping::updateOrCreate(
                [ 'course_schedule_id' => $this->currentLinkingSection->id ],
                [ 'wp_course_id' => $wpCourseId, 'wp_schedule_string' => $this->selectedWpScheduleId, ]
            );
            session()->flash('message', 'Sección enlazada exitosamente.');
            $this->dispatch('close-modal', 'link-section-modal');
            $this->closeSectionLinkModal();
        } catch (\Exception $e) {
            Log::error('Error ScheduleMapping', ['exception' => $e->getMessage()]);
            $this->sectionLinkErrorMessage = 'Error al guardar.';
        }
    }
}