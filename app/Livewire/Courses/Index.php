<?php

namespace App\Livewire\Courses;

use Livewire\Component;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\User; // Para buscar profesores
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Importar DB para escuchar queries
use Illuminate\Validation\Rule; // Para validación 'unique'
use Carbon\Carbon; // Para manejar fechas/horas

// ====================================================================
// IMPORTS AÑADIDOS PARA ENLACE CON WP
// ====================================================================
use App\Models\CourseMapping;
use App\Services\WordpressApiService;
use App\Models\ScheduleMapping;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCourse;
    public $selectedModule;
    
    // --- Propiedades para el modal de Cursos ---
    public $course_id, $course_name, $course_code;
    public $is_sequential = false;
    public $registration_fee = 0; // Nuevo: Precio de Inscripción
    public $monthly_fee = 0;      // Nuevo: Precio de Mensualidad
    public $courseModalTitle = '';

    // --- Propiedades para el modal de Módulos ---
    public $module_id, $module_name, $module_price; 
    public $moduleModalTitle = '';
    
    // --- Propiedades para el modal de Horarios ---
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $modality = 'Presencial'; 
    public $scheduleModalTitle = '';
    public $teachers = []; 

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

    // === NUEVAS PROPIEDADES PARA LIMPIEZA DE CURSOS ===
    public $confirmingClearUnused = false;
    public $unusedCoursesCount = 0;

    // === DEBUG ===
    public $debugInfo = []; // Variable pública para mostrar debug en la vista si es necesario

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
    ];

    public function mount()
    {
        try {
            // Optimización: Seleccionar solo columnas necesarias si la tabla users es muy grande
            $this->teachers = User::role('Profesor')->select('id', 'name')->orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("No se pudo cargar el rol 'Profesor': " . $e->getMessage());
            // Fallback limitado a 100 para evitar colapso si hay miles de usuarios
            $this->teachers = User::orderBy('name')->limit(100)->get(); 
        }
    }

    public function render()
    {
        // INICIO DEBUG
        $startTime = microtime(true);
        DB::enableQueryLog();
        $this->debugInfo = []; // Reiniciar info de debug

        $query = Course::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        // =================================================================================
        // OPTIMIZACIÓN CRÍTICA DE RENDIMIENTO (BACKEND)
        // =================================================================================
        // 1. CARGA INICIAL LIGERA:
        $courses = $query->select('id', 'name', 'code', 'is_sequential', 'registration_fee', 'monthly_fee')
                         ->with('mapping')
                         ->paginate(10);

        $selectedCourseObject = null;
        $modules = collect();

        // 2. CARGA BAJO DEMANDA (Curso Seleccionado):
        if ($this->selectedCourse) {
            $courseStart = microtime(true);
            $selectedCourseObject = Course::with(['modules', 'mapping'])->find($this->selectedCourse);
            
            if ($selectedCourseObject) {
                $modules = $selectedCourseObject->modules;
            } else {
                $this->reset(['selectedCourse', 'selectedModule']);
            }
            $this->debugInfo['course_load_time'] = round((microtime(true) - $courseStart) * 1000, 2) . 'ms';
        }

        $schedules = collect();
        $selectedModuleName = null;

        // 3. CARGA BAJO DEMANDA (Módulo Seleccionado):
        if ($this->selectedModule) {
            $moduleStart = microtime(true);
            $currentModule = $modules->firstWhere('id', $this->selectedModule);
            
            if ($currentModule) {
                $selectedModuleName = $currentModule->name;
                
                $schedules = CourseSchedule::where('module_id', $this->selectedModule)
                    ->with(['teacher:id,name', 'mapping']) 
                    ->orderBy('start_time')
                    ->get();
            } else {
                $this->selectedModule = null;
            }
            $this->debugInfo['module_load_time'] = round((microtime(true) - $moduleStart) * 1000, 2) . 'ms';
        }

        // FIN DEBUG
        $this->debugInfo['total_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
        $this->debugInfo['query_log'] = DB::getQueryLog();
        $this->debugInfo['query_count'] = count($this->debugInfo['query_log']);
        
        // Imprimir debug en logs para revisión (Revisa storage/logs/laravel.log)
        Log::info('LIVEWIRE RENDER DEBUG:', $this->debugInfo);

        // Opcional: Dump directo si estás en local para ver en pantalla
        // dump($this->debugInfo); 

        return view('livewire.courses.index', [
            'courses' => $courses,
            'modules' => $modules,
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name,
            'selectedModuleName' => $selectedModuleName,
            'selectedCourseObject' => $selectedCourseObject 
        ]);
    }

    // --- Métodos de Selección ---

    public function selectCourse($courseId)
    {
        // DEBUG
        Log::info("Selecting Course ID: {$courseId}");
        
        $this->selectedCourse = $courseId;
        $this->selectedModule = null; 
    }

    public function selectModule($moduleId)
    {
        // DEBUG
        Log::info("Selecting Module ID: {$moduleId}");

        $this->selectedModule = $moduleId;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedCourse = null;
        $this->selectedModule = null;
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
        // Optimización: Usar chunking para grandes volúmenes de datos
        // Si tienes miles de cursos vacíos, esto evita timeout.
        $coursesToDelete = Course::whereDoesntHave('modules.enrollments')->get();
        $count = 0;

        foreach ($coursesToDelete as $course) {
            foreach ($course->modules as $module) {
                $module->schedules()->delete();
                $module->delete();
            }
            if ($course->mapping) {
                $course->mapping->delete();
            }
            $course->delete();
            $count++;
        }

        $this->confirmingClearUnused = false;
        $this->unusedCoursesCount = 0;
        
        if ($this->selectedCourse && !Course::find($this->selectedCourse)) {
            $this->selectedCourse = null;
            $this->selectedModule = null;
        }

        session()->flash('message', "Se eliminaron {$count} cursos que no tenían estudiantes asignados.");
        $this->dispatch('close-modal', 'confirm-clear-unused-modal');
    }

    // --- MÉTODOS PARA MODAL DE CURSO ---

    protected function courseRules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'course_code' => [
                'required',
                'string',
                Rule::unique('courses', 'code')->ignore($this->course_id)
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
            // 'module_price' => 'required|numeric|min:0', // Eliminado según vista anterior
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
            // $this->module_price = $module->price; 

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
                // 'price' => $this->module_price, 
            ]
        );

        session()->flash('message', $this->module_id ? 'Módulo actualizado.' : 'Módulo creado.');
        $this->dispatch('close-modal', 'module-modal'); 
    }

    private function resetModuleFields()
    {
        $this->module_id = null;
        $this->module_name = '';
        // $this->module_price = 0.00; 
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

    public function saveSchedule()
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
        ]);

        CourseSchedule::updateOrCreate(
            ['id' => $this->schedule_id],
            [
                'module_id' => $this->selectedModule,
                'teacher_id' => $this->teacher_id,
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
        $this->days = [];
        $this->start_time = '';
        $this->end_time = '';
        $this->section_name = '';
        $this->modality = 'Presencial';
        $this->start_date = '';
        $this->end_date = '';
    }


    // ====================================================================
    // FUNCIONES PARA ENLACE CON WP
    // ====================================================================

    public function closeLinkModal()
    {
        $this->reset(['currentLinkingCourse', 'wpCourses', 'selectedWpCourseId', 'linkFeedbackMessage', 'linkErrorMessage']);
    }

    public function openLinkModal($courseId, WordpressApiService $wpService)
    {
        $this->closeLinkModal(); 
        
        try {
            $this->currentLinkingCourse = Course::with('mapping')->findOrFail($courseId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró el curso.');
            return;
        }

        $this->selectedWpCourseId = $this->currentLinkingCourse->mapping->wp_course_id ?? '';

        try {
            $this->wpCourses = $wpService->getSgaCourses();
            
            if (empty($this->wpCourses)) {
                $this->linkErrorMessage = 'No se pudieron cargar los cursos de WordPress. Revisa la configuración de la API o la conexión.';
                Log::warning('No se recibieron cursos del endpoint de WP.', ['curso_id' => $courseId]);
            }

        } catch (\Exception $e) {
            Log::error('Error al llamar a WordpressApiService', ['exception' => $e->getMessage()]);
            $this->linkErrorMessage = 'Error fatal al conectar con WordPress. Revisa los logs.';
        }

        $this->dispatch('open-modal', 'link-wp-modal');
    }

    public function saveLink()
    {
        $this->reset(['linkFeedbackMessage', 'linkErrorMessage']);

        if (empty($this->selectedWpCourseId)) {
            if ($this->currentLinkingCourse->mapping) {
                $course = $this->currentLinkingCourse;
                $moduleIds = $course->modules()->pluck('id');
                $scheduleIds = CourseSchedule::whereIn('module_id', $moduleIds)->pluck('id');
                ScheduleMapping::whereIn('course_schedule_id', $scheduleIds)->delete();
                
                $this->currentLinkingCourse->mapping->delete();
                session()->flash('message', 'Enlace de curso y secciones asociadas eliminado.');
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
                [
                    'course_id' => $this->currentLinkingCourse->id, 
                ],
                [
                    'wp_course_id' => $this->selectedWpCourseId, 
                    'wp_course_name' => $selectedWpCourseName,
                ]
            );

            session()->flash('message', 'Curso enlazado exitosamente.');
            $this->currentLinkingCourse->refresh(); 
            $this->dispatch('close-modal', 'link-wp-modal'); 
            $this->closeLinkModal(); 

        } catch (\Exception $e) {
            Log::error('Error al guardar el CourseMapping', ['exception' => $e->getMessage()]);
            $this->linkErrorMessage = 'Error al guardar el enlace en la base de datos.';
        }
    }

    // --- Métodos para mapeo de secciones ---
    
    public function closeSectionLinkModal()
    {
        $this->reset(['currentLinkingSection', 'wpSchedules', 'selectedWpScheduleId', 'sectionLinkErrorMessage']);
    }

    public function openMapSectionModal($scheduleId, WordpressApiService $wpService)
    {
        $this->closeSectionLinkModal(); 

        try {
            $this->currentLinkingSection = CourseSchedule::with('module.course.mapping')->findOrFail($scheduleId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró la sección.');
            return;
        }

        if (!$this->currentLinkingSection->module?->course?->mapping) {
            session()->flash('error', 'El curso principal de esta sección no está mapeado. Por favor, enlace el curso primero.');
            return;
        }

        $wpCourseId = $this->currentLinkingSection->module->course->mapping->wp_course_id;

        try {
            $this->wpSchedules = $wpService->getSchedulesForWpCourse($wpCourseId);
            
            if (empty($this->wpSchedules)) {
                $this->sectionLinkErrorMessage = 'No se encontraron horarios definidos en WordPress para este curso. (Asegúrate de haberlos guardado en el metabox del curso en WP).';
            }

        } catch (\Exception $e) {
            Log::error('Error al llamar a getSchedulesForWpCourse', ['exception' => $e->getMessage()]);
            $this->sectionLinkErrorMessage = 'Error fatal al conectar con WordPress para obtener horarios. Revisa los logs.';
        }

        $existingMapping = ScheduleMapping::where('course_schedule_id', $scheduleId)->first();
        $this->selectedWpScheduleId = $existingMapping->wp_schedule_string ?? ''; 

        $this->dispatch('open-modal', 'link-section-modal');
    }

    public function saveSectionLink()
    {
        $this->reset(['sectionLinkErrorMessage']);

        if (!$this->currentLinkingSection || !$this->currentLinkingSection->module?->course?->mapping) {
            $this->sectionLinkErrorMessage = 'Error: No se pudo encontrar la sección o el mapeo del curso padre.';
            return;
        }

        if (empty($this->selectedWpScheduleId)) {
            ScheduleMapping::where('course_schedule_id', $this->currentLinkingSection->id)->delete();
            session()->flash('message', 'Enlace de sección eliminado.');
            $this->dispatch('close-modal', 'link-section-modal');
            $this->closeSectionLinkModal();
            return;
        }

        try {
            $wpCourseId = $this->currentLinkingSection->module->course->mapping->wp_course_id;

            ScheduleMapping::updateOrCreate(
                [
                    'course_schedule_id' => $this->currentLinkingSection->id, 
                ],
                [
                    'wp_course_id' => $wpCourseId,
                    'wp_schedule_string' => $this->selectedWpScheduleId, 
                ]
            );

            session()->flash('message', 'Sección enlazada exitosamente.');
            $this->dispatch('close-modal', 'link-section-modal');
            $this->closeSectionLinkModal();

        } catch (\Exception $e) {
            Log::error('Error al guardar el ScheduleMapping', ['exception' => $e->getMessage()]);
            $this->sectionLinkErrorMessage = 'Error al guardar el enlace en la base de datos.';
        }
    }
}