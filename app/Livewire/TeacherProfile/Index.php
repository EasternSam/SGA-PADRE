<?php

namespace App\Livewire\TeacherProfile;

use Livewire\Component;
use App\Models\User;
use App\Models\CourseSchedule;
use App\Models\Module; // Importar el modelo Module
use App\Models\Course; // <-- ¡NUEVO! Importar el modelo Course
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')] // Asumiendo 'layouts.dashboard'
class Index extends Component
{
    use WithPagination;

    public User $teacher; // Recibe el User (profesor)

    // Propiedades para el modal de edición
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $userId = null;
    public $showModal = false;

    // --- ¡ACTUALIZADO! Propiedades para el modal de ASIGNACIÓN ---
    public $showAssignModal = false;
    public $availableSchedules = []; // Colección de secciones disponibles
    public $scheduleToAssign = null; // ID de la sección seleccionada
    public $modalView = 'assign'; // 'assign' o 'create'

    // --- ¡NUEVO! Propiedades para el formulario de CREAR sección ---
    public $courses = []; // <-- ¡NUEVO! Para el primer dropdown
    public $modules = []; // Ahora estará filtrado
    public $weekDays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    public $new_course_id = ''; // <-- ¡NUEVO! wire:model para el curso
    public $new_module_id = '';
    public $new_section_name = '';
    public $new_days_of_week = [];
    public $new_start_time = '';
    public $new_end_time = '';
    public $new_start_date = '';
    public $new_end_date = '';


    public function mount(User $teacher)
    {
        // Verificamos que el usuario sea un profesor
        if (!$teacher->hasRole('Profesor')) {
            abort(404, 'Usuario no encontrado o no es un profesor.');
        }
        $this->teacher = $teacher;
    }

    /**
     * Reglas de validación (para el modal de edición).
     */
    protected function rules()
    {
        // Reglas dinámicas dependiendo del modal que esté abierto
        if ($this->showAssignModal) {
            // Reglas para la vista "Crear"
            if ($this->modalView === 'create') {
                return [
                    'new_course_id' => 'required|exists:courses,id', // <-- ¡NUEVO!
                    'new_module_id' => 'required|exists:modules,id',
                    'new_section_name' => 'required|string|max:255',
                    'new_days_of_week' => 'required|array|min:1',
                    'new_days_of_week.*' => Rule::in($this->weekDays),
                    'new_start_time' => 'required|date_format:H:i',
                    'new_end_time' => 'required|date_format:H:i|after:new_start_time',
                    'new_start_date' => 'required|date',
                    'new_end_date' => 'required|date|after_or_equal:new_start_date',
                ];
            }

            // Reglas para la vista "Asignar"
            return [
                'scheduleToAssign' => 'required|exists:course_schedules,id',
            ];
        }

        // Reglas para el modal "Editar Profesor"
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ];
        if (!empty($this->password)) {
            $rules['password'] = 'min:8|confirmed';
        }
        return $rules;
    }

    /**
     * --- MEJORA: Añadidos mensajes en español ---
     * Mensajes de validación personalizados.
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            // --- ¡NUEVO! Mensajes para el modal de asignación/creación ---
            'scheduleToAssign.required' => 'Debe seleccionar una sección para asignar.',
            'scheduleToAssign.exists' => 'La sección seleccionada no es válida.',
            'new_course_id.required' => 'Debe seleccionar un curso.', // <-- ¡NUEVO!
            'new_module_id.required' => 'Debe seleccionar un módulo.',
            'new_section_name.required' => 'El nombre de la sección es obligatorio.',
            'new_days_of_week.required' => 'Debe seleccionar al menos un día de la semana.',
            'new_start_time.required' => 'La hora de inicio es obligatoria.',
            'new_end_time.required' => 'La hora de fin es obligatoria.',
            'new_end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'new_start_date.required' => 'La fecha de inicio es obligatoria.',
            'new_end_date.required' => 'La fecha de fin es obligatoria.',
            'new_end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    /**
     * Muestra el modal en modo "Editar".
     */
    public function edit()
    {
        $this->userId = $this->teacher->id;
        $this->name = $this->teacher->name;
        $this->email = $this->teacher->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    /**
     * Guarda los cambios del profesor (Usuario).
     */
    public function save()
    {
        $this->validate();
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::findOrFail($this->userId);
        $user->update($data);

        session()->flash('message', 'Profesor actualizado exitosamente.');
        $this->closeModal();
        $this->teacher = $user->fresh(); // Refresca los datos del profesor en la página
    }

    /**
     * Cierra el modal y resetea el formulario.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'userId']);
        $this->resetValidation();
    }

    // --- ¡MÉTODOS ACTUALIZADOS Y NUEVOS PARA ASIGNAR/CREAR SECCIÓN! ---

    /**
     * Muestra el modal para asignar una sección.
     */
    public function openAssignModal()
    {
        // ----- ¡¡¡INICIO DE LA CORRECCIÓN!!! -----
        // 1. Reseteamos los formularios PRIMERO
        $this->resetFormulariosAsignacion();

        // 2. Buscamos todas las secciones que no tengan profesor asignado
        $this->availableSchedules = CourseSchedule::whereNull('teacher_id')
            ->with('module.course') // Cargamos relaciones para mostrarlas
            ->orderBy('id', 'desc')
            ->get();

        // 3. Cargamos todos los CURSOS
        $this->courses = Course::orderBy('name')->get();
        $this->modules = []; // Los módulos inician vacíos
        
        // 4. Mostramos el modal
        $this->modalView = 'assign'; // Vista inicial
        $this->showAssignModal = true;
        // ----- ¡¡¡FIN DE LA CORRECCIÓN!!! -----
    }

    /**
     * ¡NUEVO! Hook de Livewire que se dispara cuando $new_course_id cambia.
     */
    public function updatedNewCourseId($value)
    {
        // Si se selecciona un curso, filtramos los módulos
        if (!empty($value)) {
            $this->modules = Module::where('course_id', $value)->orderBy('name')->get();
        } else {
            // Si se deselecciona, vaciamos la lista de módulos
            $this->modules = [];
        }
        // Reseteamos la selección de módulo anterior
        $this->reset('new_module_id');
    }

    /**
     * Cierra el modal de asignación.
     */
    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->resetFormulariosAsignacion();
    }

    /**
     * Resetea todos los campos de los modales de asignación/creación
     */
    private function resetFormulariosAsignacion()
    {
        // ----- ¡¡¡INICIO DE LA CORRECCIÓN!!! -----
        // Quitamos 'courses' del array, porque no es un campo de formulario
        // sino una lista de opciones que no debe borrarse.
        $this->reset([
            'scheduleToAssign',
            'availableSchedules',
            // 'courses', // <-- ELIMINADO DE AQUÍ
            'modules',
            'new_course_id', // <-- ¡NUEVO!
            'new_module_id',
            'new_section_name',
            'new_days_of_week',
            'new_start_time',
            'new_end_time',
            'new_start_date',
            'new_end_date'
        ]);
        // ----- ¡¡¡FIN DE LA CORRECCIÓN!!! -----
        $this->resetValidation();
    }

    /**
     * ¡NUEVO! Cambia la vista del modal a "Crear"
     */
    public function switchToCreateView()
    {
        $this->modalView = 'create';
        $this->resetValidation();
        // --- Añadido por robustez: nos aseguramos que los cursos estén cargados ---
        if (empty($this->courses)) {
            $this->courses = Course::orderBy('name')->get();
        }
    }

    /**
     * ¡NUEVO! Cambia la vista del modal a "Asignar"
     */
    public function switchToAssignView()
    {
        $this->modalView = 'assign';
        $this->resetValidation();
    }

    /**
     * ¡ACTUALIZADO! Maneja el guardado de CUALQUIERA de las dos vistas
     */
    public function handleAssignment()
    {
        $this->validate();

        // --- Lógica para la vista "Asignar" ---
        if ($this->modalView === 'assign') {

            $schedule = CourseSchedule::find($this->scheduleToAssign);

            // Doble chequeo por si acaso la sección fue asignada por otro admin
            if ($schedule->teacher_id) {
                session()->flash('error', 'Esta sección ya fue asignada a otro profesor.');
                $this->closeAssignModal();
                return;
            }

            // ¡Asignamos!
            $schedule->teacher_id = $this->teacher->id;
            $schedule->save();

            session()->flash('message', 'Sección asignada exitosamente.');
            $this->closeAssignModal();

            // --- Lógica para la vista "Crear" ---
        } elseif ($this->modalView === 'create') {

            CourseSchedule::create([
                'module_id' => $this->new_module_id,
                'teacher_id' => $this->teacher->id, // Asignar al profesor actual
                'section_name' => $this->new_section_name,
                'days_of_week' => $this->new_days_of_week,
                'start_time' => $this->new_start_time,
                'end_time' => $this->new_end_time,
                'start_date' => $this->new_start_date,
                'end_date' => $this->new_end_date,
            ]);

            session()->flash('message', 'Nueva sección creada y asignada exitosamente.');
            $this->closeAssignModal();
        }
    }


    public function render()
    {
        // --- ¡¡¡ESTA ES LA CORRECCIÓN IDEAL!!! ---
        // Usamos la relación 'schedules()' que SÍ existe en tu User.php
        // y la asignamos a la variable '$sections' que la vista espera.
        $sections = $this->teacher->schedules() // <-- Usamos el nombre correcto de tu modelo
            ->with('module.course')
            ->orderBy('start_date', 'desc')
            ->paginate(10, ['*'], 'sectionsPage');

        return view('livewire.teacher-profile.index', [
            'sections' => $sections, // <-- Mantenemos este nombre para la vista
        ]);
    }
}