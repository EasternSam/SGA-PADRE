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

#[Layout('layouts.app')]
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

        // Cargamos cursos CON sus módulos
        $courses = $query->with('modules.schedules.teacher')->paginate(10);

        // 1. Encontramos el curso seleccionado DE LA COLECCIÓN que ya cargamos (sin consultar la DB de nuevo)
        $selectedCourseObject = $this->selectedCourse ? $courses->find($this->selectedCourse) : null;
        
        // 2. Obtenemos los módulos de ese objeto (ya están cargados por el ->with('modules...'))
        $modules = $selectedCourseObject ? $selectedCourseObject->modules : collect();

        // Cargar los horarios (secciones) para el módulo seleccionado
        $schedules = $this->selectedModule
            ? CourseSchedule::where('module_id', $this->selectedModule)->with('teacher')->get()
            : collect(); // Retorna una colección vacía

        return view('livewire.courses.index', [
            'courses' => $courses,
            'modules' => $modules, // <-- 3. Pasamos la variable $modules a la vista
            'schedules' => $schedules,
            'selectedCourseName' => $selectedCourseObject?->name, // Usamos el objeto para el nombre
            'selectedModuleName' => $this->selectedModule ? Module::find($this->selectedModule)?->name : null,
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
}