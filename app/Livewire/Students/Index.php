<?php

namespace App\Livewire\Students;

use Livewire\Component;
use App\Models\Student;
use Livewire\WithPagination;
use Livewire\Attributes\Layout; // Para el layout
use Illuminate\Support\Facades\Log; // Para depuración
use Carbon\Carbon; // <-- ¡¡¡CORRECCIÓN ERROR #2!!! Clase importada

#[Layout('Layouts.dashboard')] // Respetamos tu layout
class Index extends Component
{
    use WithPagination;

    // --- Propiedades para el Formulario (¡CORREGIDAS!) ---
    // Deben coincidir con tu migración y el modelo student.php
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
    public $search = '';
    // --- ¡¡¡REPARACIÓN!!! ---
    // Ya no usaremos 'isOpen'. Lo cambiaremos por 'dispatch'
    // public $isOpen = false; 
    public $modalTitle = '';

    // --- ¡AÑADIDO! ---
    // Propiedad para capturar el ID de edición desde la URL
    public $editing = null;

    // --- ¡AÑADIDO! ---
    // Registra la propiedad 'editing' para que se sincronice con la URL
    protected $queryString = [
        'search' => ['except' => ''],
        'editing' => ['except' => null]
    ];

    /**
     * --- ¡AÑADIDO! ---
     * Se ejecuta cuando el componente se carga por primera vez.
     * Comprueba si se pasó un ID de estudiante en la URL para editarlo.
     */
    public function mount()
    {
        if ($this->editing) {
            // Llama a la función 'edit' existente si se encuentra un ID en la URL
            $this->edit($this->editing);
        }
    }

    /**
     * Reglas de Validación (¡CORREGIDAS!)
     *
     * @return array
     */
    protected function rules()
    {
        // El email debe ser único en la tabla 'students', ignorando el ID actual
        $emailRule = 'required|email|unique:students,email';
        if ($this->student_id) {
            $emailRule .= ',' . $this->student_id;
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => $emailRule,
            'cedula' => 'nullable|string|max:20|unique:students,cedula' . ($this->student_id ? ',' . $this->student_id : ''),
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
        ];

        // Si es menor, los campos del tutor son requeridos
        if ($this->is_minor) {
            $rules['tutor_name'] = 'required|string|max:255';
            $rules['tutor_cedula'] = 'nullable|string|max:20';
            $rules['tutor_phone'] = 'required|string|max:20';
            $rules['tutor_relationship'] = 'nullable|string|max:100';
        }

        return $rules;
    }

    /**
     * Personalizar mensajes de validación
     */
    protected $messages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El formato del correo no es válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'tutor_name.required' => 'El nombre del tutor es obligatorio.',
        'tutor_phone.required' => 'El teléfono del tutor es obligatorio.',
    ];

    /**
     * Renderiza el componente
     */
    public function render()
    {
        $query = Student::query();

        if ($this->search) {
            $query->where(function ($q) {
                // Busca por nombre, apellido, email o cédula
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('cedula', 'like', '%' . $this->search . '%');
            });
        }

        // Usamos 'fullName' (el accesor) para ordenar por nombre completo
        // ¡Esto requiere más lógica si se quiere ordenar por 'first_name' y 'last_name'!
        // Simplificamos ordenando por first_name
        $students = $query->orderBy('first_name', 'asc')->paginate(10);

        return view('livewire.students.index', [
            'students' => $students,
        ]);
    }

    /**
     * Abre el modal para crear un nuevo estudiante.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->modalTitle = 'Registrar Nuevo Estudiante';
        
        // --- ¡¡¡REPARACIÓN!!! ---
        // $this->isOpen = true; // Ya no se usa
        $this->dispatch('open-modal', 'student-form-modal');
    }

    /**
     * Abre el modal para editar un estudiante.
     *
     * @param int $id
     */
    public function edit($id)
    {
        try {
            $student = Student::findOrFail($id);
            $this->student_id = $id;
            
            // Asignar todos los campos del modelo a las propiedades públicas
            $this->first_name = $student->first_name;
            $this->last_name = $student->last_name;
            $this->cedula = $student->cedula;
            $this->email = $student->email;
            $this->mobile_phone = $student->mobile_phone;
            $this->home_phone = $student->home_phone;
            $this->address = $student->address;
            $this->city = $student->city;
            $this->sector = $student->sector;
            // Formatear la fecha para el input 'date'
            // ¡ESTA LÍNEA (161) YA FUNCIONA GRACIAS AL 'use Carbon\Carbon;' DE ARRIBA!
            $this->birth_date = $student->birth_date ? Carbon::parse($student->birth_date)->format('Y-m-d') : null;
            $this->gender = $student->gender;
            $this->nationality = $student->nationality;
            $this->how_found = $student->how_found;
            $this->is_minor = $student->is_minor;
            $this->tutor_name = $student->tutor_name;
            $this->tutor_cedula = $student->tutor_cedula;
            $this->tutor_phone = $student->tutor_phone;
            $this->tutor_relationship = $student->tutor_relationship;

            $this->modalTitle = 'Editar Estudiante: ' . $student->fullName; // Usamos el accesor
            
            // --- ¡¡¡REPARACIÓN!!! ---
            // $this->isOpen = true; // Ya no se usa
            $this->dispatch('open-modal', 'student-form-modal');

        } catch (\Exception $e) {
            session()->flash('error', 'Estudiante no encontrado.');
            Log::error('Error al editar estudiante: ' . $e->getMessage()); // <-- LÍNEA COMPLETADA
        }
    }

    /**
     * Guarda el estudiante (nuevo o existente).
     */
    public function saveStudent()
    {
        $this->validate(); // Usa las 'rules'

        try {
            Student::updateOrCreate(
                ['id' => $this->student_id],
                [
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
                    // 'user_id' se podría manejar aquí si se crea un usuario al mismo tiempo
                ]
            );

            session()->flash('message', $this->student_id ? 'Estudiante actualizado exitosamente.' : 'Estudiante creado exitosamente.');
            $this->closeModal(); // Esta función ahora dispara el evento correcto

        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un estudiante.
     *
     * @param int $id
     */
    public function delete($id)
    {
        try {
            // (Podrías añadir lógica para verificar si tiene matrículas)
            Student::find($id)->delete();
            session()->flash('message', 'Estudiante eliminado.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar estudiante: ' . $e->getMessage());
            session()->flash('error', 'No se pudo eliminar al estudiante. Verifique si tiene matrículas activas.');
        }
    }

    /**
     * Cierra el modal.
     */
    public function closeModal()
    {
        // --- ¡¡¡REPARACIÓN!!! ---
        // $this->isOpen = false; // Ya no se usa
        $this->dispatch('close-modal', 'student-form-modal');
        
        $this->resetInputFields();
        $this->resetErrorBag(); // Limpia los errores de validación
    }

    /**
     * Resetea todos los campos del formulario.
     */
    private function resetInputFields()
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
        $this->gender = ''; // Corregido de $this.gender a $this->gender
        $this->nationality = '';
        $this->how_found = '';
        $this->is_minor = false;
        $this->tutor_name = '';
        $this->tutor_cedula = '';
        $this->tutor_phone = '';
        $this->tutor_relationship = '';
        $this->modalTitle = '';
        $this->editing = null; // <-- ¡AÑADIDO! Limpia el parámetro de la URL
    }
}