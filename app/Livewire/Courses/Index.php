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
use Illuminate\Validation\Rule; // Para validación 'unique'
use Carbon\Carbon; // Para manejar fechas/horas

// ====================================================================
// IMPORTS AÑADIDOS PARA ENLACE CON WP (PUNTO 3)
// ====================================================================
use App\Models\CourseMapping;
use App\Services\WordpressApiService;
// INICIO: Import añadido para mapeo de secciones
use App\Models\ScheduleMapping;
// FIN: Import añadido
// ====================================================================
// FIN DE IMPORTS AÑADIDOS
// ====================================================================

#[Layout('layouts.dashboard')] // Tuve que corregir 'Layouts.dashboard' a 'layouts.dashboard'
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCourse;
    public $selectedModule;
    
    // --- Propiedades para el modal de Cursos (Ajustadas a la DB) ---
    public $course_id, $course_name, $course_code;
    public $courseModalTitle = '';

    // --- Propiedades para el modal de Módulos (Ajustadas a la DB) ---
    public $module_id, $module_name, $module_price; // Se añadió module_price
    public $moduleModalTitle = '';
    
    // --- Propiedades para el modal de Horarios (Ajustadas a la DB) ---
    public $schedule_id, $teacher_id, $days = [], $start_time, $end_time, $section_name, $start_date, $end_date;
    public $scheduleModalTitle = '';
    public $teachers = []; // Para el dropdown de profesores

    // ====================================================================
    // NUEVAS PROPIEDADES PARA MODAL DE ENLACE (PUNTO 3)
    // ====================================================================
    
    /**
     * Almacena el curso que se está enlazando (el objeto completo).
     * @var Course|null
     */
    public $currentLinkingCourse;

    /**
     * Almacena la lista de cursos obtenidos desde WordPress.
     * @var array
     */
    public $wpCourses = [];

    /**
     * Vinculado al <select> del modal, almacena el ID del curso de WP seleccionado.
     * @var string|int|null
     */
    public $selectedWpCourseId = '';

    /**
     * Mensaje de feedback dentro del modal de enlace.
     * @var string
     */
    public $linkFeedbackMessage = '';

    /**
     * Mensaje de error dentro del modal de enlace.
     * @var string
     */
    public $linkErrorMessage = '';
    // ====================================================================
    // FIN DE NUEVAS PROPIEDADES
    // ====================================================================

    // INICIO: Propiedades añadidas para el modal de mapeo de secciones
    /**
     * Almacena la sección (CourseSchedule) que se está enlazando.
     * @var CourseSchedule|null
     */
    public $currentLinkingSection;

    /**
     * Almacena la lista de horarios obtenidos desde WordPress para el curso enlazado.
     * @var array
     */
    public $wpSchedules = [];

    /**
     * Vinculado al <select> del modal de secciones, almacena el ID del horario de WP seleccionado.
     * @var string|null
     */
    public $selectedWpScheduleId = '';

    /**
     * Mensaje de error dentro del modal de enlace de sección.
     * @var string
     */
    public $sectionLinkErrorMessage = '';
    // FIN: Propiedades añadidas


    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCourse' => ['except' => null],
        'selectedModule' => ['except' => null],
    ];

    /**
     * Cargar profesores cuando el componente se inicia
     */
    public function mount()
    {
        try {
            $this->teachers = User::role('Profesor')->orderBy('name')->get();
        } catch (\Exception $e) {
            // Fallback por si el rol no existe
            Log::error("No se pudo cargar el rol 'Profesor': " . $e->getMessage());
            $this->teachers = User::orderBy('name')->get(); // Carga todos como fallback
        }
    }

    public function render()
    {
        $query = Course::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        // ====================================================================
        // MODIFICACIÓN PARA EAGER LOADING (PUNTO 3)
        // ====================================================================
        // Cargamos cursos CON sus módulos Y su enlace de WP
        $courses = $query->with('modules.schedules.teacher', 'mapping')->paginate(10);
        // ====================================================================
        // FIN DE MODIFICACIÓN
        // ====================================================================

        // 1. Encontramos el curso seleccionado DE LA COLECCIÓN que ya cargamos (sin consultar la DB de nuevo)
        // INICIO: Modificación para Eager Loading
        // Necesitamos $selectedCourseObject para la Columna 3 (Secciones), así que lo cargamos aquí
        $selectedCourseObject = $this->selectedCourse ? $courses->find($this->selectedCourse) : null;
        // FIN: Modificación
        
        // 2. Obtenemos los módulos de ese objeto (ya están cargados por el ->with('modules...'))
        $modules = $selectedCourseObject ? $selectedCourseObject->modules : collect();

        // Cargar los horarios (secciones) para el módulo seleccionado
        // INICIO: Modificación para Eager Loading de mapeo de sección
        $schedules = $this->selectedModule
            ? CourseSchedule::where('module_id', $this->selectedModule)->with('teacher', 'mapping')->get()
            : collect(); // Retorna una colección vacía
        // FIN: Modificación

        return view('livewire.courses.index', [
            'courses' => $courses,
            'modules' => $modules, // <-- 3. Pasamos la variable $modules a la vista
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name, // Usamos el objeto para el nombre
            'selectedModuleName' => $this->selectedModule ? Module::find($this->selectedModule)?->name : null,
            // INICIO: Pasar el objeto de curso seleccionado a la vista
            'selectedCourseObject' => $selectedCourseObject // Necesario para el botón de enlace de sección
            // FIN: Pasar el objeto
        ]);
    }

    // --- Métodos de Selección ---

    public function selectCourse($courseId)
    {
        $this->selectedCourse = $courseId;
        $this->selectedModule = null; // Resetea el módulo al cambiar de curso
    }

    public function selectModule($moduleId)
    {
        $this->selectedModule = $moduleId;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedCourse = null;
        $this->selectedModule = null;
    }

    // --- MÉTODOS PARA MODAL DE CURSO ---

    /**
     * Reglas de validación dinámicas para Cursos
     */
    protected function courseRules()
    {
        return [
            'course_name' => 'required|string|max:255',
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
        $this->resetValidation(); // Limpiar errores
        $this->courseModalTitle = 'Crear Nuevo Curso';
        $this->dispatch('open-modal', 'course-modal'); 
    }

    public function editCourse($courseId)
    {
        $this->resetValidation(); // Limpiar errores
        try {
            $course = Course::findOrFail($courseId);
            $this->course_id = $course->id;
            $this->course_name = $course->name;
            // $this->course_credits = $course->credits; // Se elimina esta línea
            $this->course_code = $course->code;
            
            $this->courseModalTitle = 'Editar Curso: ' . $course->name;
            $this->dispatch('open-modal', 'course-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Curso no encontrado.');
        }
    }

    public function saveCourse()
    {
        // Validar usando las reglas dinámicas
        $this->validate($this->courseRules());

        Course::updateOrCreate(
            ['id' => $this->course_id],
            [
                'name' => $this->course_name,
                // 'credits' => $this->course_credits, // Se elimina esta línea
                'code' => $this->course_code,
                // 'description' no está en tu formulario, así que no se incluye aquí
            ]
        );

        session()->flash('message', $this->course_id ? 'Curso actualizado.' : 'Curso creado.');
        
        // --- ¡CORRECCIÓN! ---
        // Despachar el evento 'close-modal' con el nombre del modal.
        $this->dispatch('close-modal', 'course-modal'); 
    }

    private function resetCourseFields()
    {
        $this->course_id = null;
        $this->course_name = '';
        // $this->course_credits = 0; // Se elimina esta línea
        $this->course_code = '';
    }

    // --- MÉTODOS PARA MODAL DE MÓDULO ---

    /**
     * Reglas de validación dinámicas para Módulos
     */
    protected function moduleRules()
    {
        return [
            'module_name' => 'required|string|max:255',
            'module_price' => 'required|numeric|min:0', // Añadida validación para precio
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
            $this->module_price = $module->price; // Cargar el precio

            $this->moduleModalTitle = 'Editar Módulo: ' . $module->name;
            $this->dispatch('open-modal', 'module-modal'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Módulo no encontrado.');
        }
    }

    public function saveModule()
    {
        // Validar usando las reglas dinámicas
        $this->validate($this->moduleRules());

        Module::updateOrCreate(
            ['id' => $this->module_id],
            [
                'course_id' => $this->selectedCourse,
                'name' => $this->module_name,
                'price' => $this->module_price, // Guardar el precio
            ]
        );

        session()->flash('message', $this->module_id ? 'Módulo actualizado.' : 'Módulo creado.');
        
        // --- ¡CORRECCIÓN! ---
        // Despachar el evento 'close-modal' con el nombre del modal.
        $this->dispatch('close-modal', 'module-modal'); 
    }

    private function resetModuleFields()
    {
        $this->module_id = null;
        $this->module_name = '';
        $this->module_price = 0.00; // Resetear precio
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
            
            // ¡¡¡CORRECCIÓN!!! Leer de 'days_of_week'
            $this->days = $schedule->days_of_week ?? []; // <-- Corregido
            
            $this->section_name = $schedule->section_name;
            
            // Usar Carbon para formatear (asegura que Carbon está importado)
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
            'days' => 'required|array|min:1', // 'days' es el nombre de la propiedad pública
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
                
                // ¡¡¡CORRECCIÓN!!! Guardar en la columna 'days_of_week'
                'days_of_week' => $this->days, // <-- Corregido
                
                'section_name' => $this->section_name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]
        );

        session()->flash('message', $this->schedule_id ? 'Sección actualizada.' : 'Sección creada.');
        
        // --- ¡CORRECCIÓN! ---
        // Despachar el evento 'close-modal' con el nombre del modal.
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


    // ====================================================================
    // NUEVAS FUNCIONES PARA ENLACE CON WP (PUNTO 3)
    // ====================================================================

    /**
     * Cierra el modal de enlace y resetea sus propiedades.
     */
    public function closeLinkModal()
    {
        $this->reset(['currentLinkingCourse', 'wpCourses', 'selectedWpCourseId', 'linkFeedbackMessage', 'linkErrorMessage']);
        // No disparamos 'close-modal' aquí, lo hacemos desde el botón "Cancelar" en la vista (x-on:click)
    }

    /**
     * Abre el modal de enlace para un curso específico.
     * Carga el curso actual y obtiene la lista de cursos de WP.
     *
     * @param int $courseId
     * @param WordpressApiService $wpService // Inyección de dependencias de Livewire
     */
    public function openLinkModal($courseId, WordpressApiService $wpService)
    {
        $this->closeLinkModal(); // Resetea por si estaba abierto
        
        try {
            $this->currentLinkingCourse = Course::with('mapping')->findOrFail($courseId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró el curso.');
            return;
        }

        // Valor actual (si ya existe un enlace)
        $this->selectedWpCourseId = $this->currentLinkingCourse->mapping->wp_course_id ?? '';

        try {
            // Llama al servicio para obtener los cursos de WP
            $this->wpCourses = $wpService->getSgaCourses();
            
            if (empty($this->wpCourses)) {
                $this->linkErrorMessage = 'No se pudieron cargar los cursos de WordPress. Revisa la configuración de la API o la conexión.';
                Log::warning('No se recibieron cursos del endpoint de WP.', ['curso_id' => $courseId]);
            }

        } catch (\Exception $e) {
            Log::error('Error al llamar a WordpressApiService', ['exception' => $e->getMessage()]);
            $this->linkErrorMessage = 'Error fatal al conectar con WordPress. Revisa los logs.';
        }

        // Disparamos el evento para abrir el modal (que definimos en la vista)
        $this->dispatch('open-modal', 'link-wp-modal');
    }

    /**
     * Guarda o actualiza el enlace del curso de Laravel con el curso de WP.
     */
    public function saveLink()
    {
        $this->reset(['linkFeedbackMessage', 'linkErrorMessage']);

        if (empty($this->selectedWpCourseId)) {
            // Si el usuario selecciona "Ninguno", borramos el enlace
            if ($this->currentLinkingCourse->mapping) {
                
                // INICIO: Borrar también mapeos de secciones hijas
                $course = $this->currentLinkingCourse;
                $moduleIds = $course->modules()->pluck('id');
                $scheduleIds = CourseSchedule::whereIn('module_id', $moduleIds)->pluck('id');
                ScheduleMapping::whereIn('course_schedule_id', $scheduleIds)->delete();
                // FIN: Borrar también mapeos de secciones hijas
                
                $this->currentLinkingCourse->mapping->delete();
                session()->flash('message', 'Enlace de curso y secciones asociadas eliminado.');
                $this->currentLinkingCourse->refresh(); // Refresca la relación
            }
            $this->dispatch('close-modal', 'link-wp-modal');
            $this->closeLinkModal(); // Resetea las propiedades
            return;
        }

        // Buscar el nombre del curso de WP en el array que ya tenemos
        $selectedWpCourseName = 'Nombre no encontrado';
        foreach ($this->wpCourses as $wpCourse) {
            if ($wpCourse['wp_course_id'] == $this->selectedWpCourseId) {
                $selectedWpCourseName = $wpCourse['wp_course_name'];
                break;
            }
        }

        // Usamos updateOrCreate para insertar o actualizar el enlace basado en el course_id
        try {
            CourseMapping::updateOrCreate(
                [
                    'course_id' => $this->currentLinkingCourse->id, // Condición de búsqueda
                ],
                [
                    'wp_course_id' => $this->selectedWpCourseId, // Valores a insertar/actualizar
                    'wp_course_name' => $selectedWpCourseName,
                ]
            );

            session()->flash('message', 'Curso enlazado exitosamente.');
            $this->currentLinkingCourse->refresh(); // Refresca la relación
            $this->dispatch('close-modal', 'link-wp-modal'); // Cierra el modal al guardar
            $this->closeLinkModal(); // Resetea las propiedades

        } catch (\Exception $e) {
            Log::error('Error al guardar el CourseMapping', ['exception' => $e->getMessage()]);
            $this->linkErrorMessage = 'Error al guardar el enlace en la base de datos.';
        }
    }

    // ====================================================================
    // FIN DE NUEVAS FUNCIONES
    // ====================================================================

    // INICIO: Métodos añadidos para mapeo de secciones
    /**
     * Resetea las propiedades del modal de enlace de sección.
     */
    public function closeSectionLinkModal()
    {
        $this->reset(['currentLinkingSection', 'wpSchedules', 'selectedWpScheduleId', 'sectionLinkErrorMessage']);
    }

    /**
     * Abre el modal de enlace para una sección (CourseSchedule) específica.
     *
     * @param int $scheduleId
     * @param WordpressApiService $wpService
     */
    public function openMapSectionModal($scheduleId, WordpressApiService $wpService)
    {
        $this->closeSectionLinkModal(); // Resetea estado anterior

        try {
            // Cargamos la sección con su módulo, curso y el mapeo del curso
            $this->currentLinkingSection = CourseSchedule::with('module.course.mapping')->findOrFail($scheduleId);
        } catch (\Exception $e) {
            session()->flash('error', 'No se encontró la sección.');
            return;
        }

        // Validar que el curso padre esté mapeado
        if (!$this->currentLinkingSection->module?->course?->mapping) {
            session()->flash('error', 'El curso principal de esta sección no está mapeado. Por favor, enlace el curso primero.');
            return;
        }

        $wpCourseId = $this->currentLinkingSection->module->course->mapping->wp_course_id;

        try {
            // Llama al servicio para obtener los horarios de WP
            $this->wpSchedules = $wpService->getSchedulesForWpCourse($wpCourseId);
            
            if (empty($this->wpSchedules)) {
                $this->sectionLinkErrorMessage = 'No se encontraron horarios definidos en WordPress para este curso. (Asegúrate de haberlos guardado en el metabox del curso en WP).';
            }

        } catch (\Exception $e) {
            Log::error('Error al llamar a getSchedulesForWpCourse', ['exception' => $e->getMessage()]);
            $this->sectionLinkErrorMessage = 'Error fatal al conectar con WordPress para obtener horarios. Revisa los logs.';
        }

        // Buscar el mapeo existente para esta sección
        // INICIO: CORRECCIÓN DE TYPO
        $existingMapping = ScheduleMapping::where('course_schedule_id', $scheduleId)->first();
        $this->selectedWpScheduleId = $existingMapping->wp_schedule_string ?? ''; // Corregido de wp_schedule_data
        // FIN: CORRECCIÓN DE TYPO

        // Disparamos el evento para abrir el nuevo modal
        $this->dispatch('open-modal', 'link-section-modal');
    }

    /**
     * Guarda o actualiza el enlace de la sección de Laravel con el horario de WP.
     */
    public function saveSectionLink()
    {
        $this->reset(['sectionLinkErrorMessage']);

        if (!$this->currentLinkingSection || !$this->currentLinkingSection->module?->course?->mapping) {
            $this->sectionLinkErrorMessage = 'Error: No se pudo encontrar la sección o el mapeo del curso padre.';
            return;
        }

        // Si el usuario selecciona "Ninguno", borramos el enlace
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
                    'course_schedule_id' => $this->currentLinkingSection->id, // Condición de búsqueda
                ],
                [
                    'wp_course_id' => $wpCourseId,
                    // INICIO: CORRECCIÓN DE TYPO
                    'wp_schedule_string' => $this->selectedWpScheduleId, // Corregido de wp_schedule_data
                    // FIN: CORRECCIÓN DE TYPO
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
    // FIN: Métodos añadidos

}