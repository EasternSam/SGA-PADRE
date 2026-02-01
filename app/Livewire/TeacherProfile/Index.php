<?php

namespace App\Livewire\TeacherProfile;

use Livewire\Component;
use App\Models\User;
use App\Models\CourseSchedule;
use App\Models\Module;
use App\Models\Course;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public User $teacher;

    // Propiedades para el modal de edición de perfil
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $userId = null;
    public $showModal = false;

    // Propiedades para el modal de asignación de carga académica
    public $showAssignModal = false;
    public $availableSchedules = []; 
    public $scheduleToAssign = null; 
    public $modalView = 'assign'; // 'assign' (existente) o 'create' (nueva)

    // Propiedades para el formulario de CREAR sección
    public $courses = []; 
    public $modules = []; 
    public $weekDays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    // Campos del formulario
    public $new_course_id = '';
    public $new_module_id = '';
    public $new_section_name = '';
    public $new_days_of_week = [];
    public $new_start_time = '';
    public $new_end_time = '';
    public $new_start_date = '';
    public $new_end_date = '';

    public function mount(User $teacher)
    {
        if (!$teacher->hasRole('Profesor')) {
            abort(404, 'Usuario no encontrado o no es un profesor.');
        }
        $this->teacher = $teacher;
    }

    protected function rules()
    {
        if ($this->showAssignModal) {
            if ($this->modalView === 'create') {
                return [
                    'new_course_id' => 'required|exists:courses,id',
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

            return [
                'scheduleToAssign' => 'required|exists:course_schedules,id',
            ];
        }

        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ];
        if (!empty($this->password)) {
            $rules['password'] = 'min:8|confirmed';
        }
        return $rules;
    }

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
            'scheduleToAssign.required' => 'Debe seleccionar una sección para asignar.',
            'scheduleToAssign.exists' => 'La sección seleccionada no es válida.',
            'new_course_id.required' => 'Debe seleccionar un curso.',
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
        $this->teacher = $user->fresh();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'userId']);
        $this->resetValidation();
    }

    public function openAssignModal()
    {
        $this->resetFormulariosAsignacion();
        $this->loadAvailableSchedules(); // Cargar solo para asignar
        
        $this->modalView = 'assign';
        $this->showAssignModal = true;
    }

    // Helper para cargar horarios (Optimizado)
    private function loadAvailableSchedules()
    {
        $this->availableSchedules = CourseSchedule::whereNull('teacher_id')
            ->where('status', 'Activo') // OPTIMIZACIÓN: Solo activos
            ->with('module.course')
            ->orderBy('id', 'desc')
            ->limit(300) // OPTIMIZACIÓN: Limite de seguridad para no saturar
            ->get();
    }

    // Hook: Cuando cambia el curso, cargar sus módulos
    public function updatedNewCourseId($value)
    {
        if (!empty($value)) {
            $this->modules = Module::where('course_id', $value)
                ->where('status', 'Activo')
                ->select('id', 'name') // OPTIMIZACIÓN: Solo columnas necesarias
                ->orderBy('name')
                ->get();
        } else {
            $this->modules = [];
        }
        $this->reset('new_module_id');
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->resetFormulariosAsignacion();
    }

    private function resetFormulariosAsignacion()
    {
        $this->reset([
            'scheduleToAssign',
            'availableSchedules',
            'modules',
            'new_course_id',
            'new_module_id',
            'new_section_name',
            'new_days_of_week',
            'new_start_time',
            'new_end_time',
            'new_start_date',
            'new_end_date'
        ]);
        $this->resetValidation();
    }

    public function switchToCreateView()
    {
        $this->modalView = 'create';
        $this->resetValidation();

        // OPTIMIZACIÓN CLAVE: Vaciamos availableSchedules para que Livewire no envíe
        // esa lista gigante en cada request al servidor (seleccionar curso, etc).
        $this->availableSchedules = [];

        if (empty($this->courses)) {
            // OPTIMIZACIÓN: Solo columnas necesarias
            $this->courses = Course::where('status', 'Activo')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }
    }

    public function switchToAssignView()
    {
        $this->modalView = 'assign';
        $this->resetValidation();
        // Recargamos la lista al volver
        $this->loadAvailableSchedules();
    }

    public function handleAssignment()
    {
        $this->validate();

        if ($this->modalView === 'assign') {
            $schedule = CourseSchedule::find($this->scheduleToAssign);

            if ($schedule->teacher_id) {
                session()->flash('error', 'Esta sección ya fue asignada a otro profesor.');
                $this->closeAssignModal();
                return;
            }

            $schedule->teacher_id = $this->teacher->id;
            $schedule->save();

            session()->flash('message', 'Sección asignada exitosamente.');
            $this->closeAssignModal();

        } elseif ($this->modalView === 'create') {
            CourseSchedule::create([
                'module_id' => $this->new_module_id,
                'teacher_id' => $this->teacher->id,
                'section_name' => $this->new_section_name,
                'days_of_week' => $this->new_days_of_week,
                'start_time' => $this->new_start_time,
                'end_time' => $this->new_end_time,
                'start_date' => $this->new_start_date,
                'end_date' => $this->new_end_date,
                'modality' => 'Presencial', // Valor por defecto
                'capacity' => 30, // Valor por defecto
                'status' => 'Activo',
            ]);

            session()->flash('message', 'Nueva sección creada y asignada exitosamente.');
            $this->closeAssignModal();
        }
    }

    public function render()
    {
        $sections = $this->teacher->schedules()
            ->with('module.course')
            ->orderBy('start_date', 'desc')
            ->paginate(10, ['*'], 'sectionsPage');

        return view('livewire.teacher-profile.index', [
            'sections' => $sections,
        ]);
    }
}