<?php

namespace App\Livewire\Students;

use App\Models\Student;
use App\Models\User; // <-- AÑADIDO: Para crear el usuario
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- AÑADIDO: Para transacciones
use Illuminate\Support\Facades\Hash; // <-- AÑADIDO: Para hashear la clave
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // <-- AÑADIDO: Para reglas de validación avanzadas
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    // --- Propiedades del Formulario (Coinciden con tu original) ---
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

    // --- AÑADIDO: Campos para el Usuario (SOLO PARA EDITAR) ---
    public $password = '';
    public $password_confirmation = '';
    
    // Propiedades de la UI
    public $search = '';
    public $modalTitle = '';

    // Propiedad para capturar el ID de edición desde la URL
    public $editing = null;

    // Registra la propiedad 'editing' para que se sincronice con la URL
    protected $queryString = [
        'search' => ['except' => ''],
        'editing' => ['except' => null]
    ];

    /**
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
     * Reglas de Validación (¡ACTUALIZADAS!)
     *
     * @return array
     */
    protected function rules()
    {
        // Obtener el user_id SÓLO si estamos editando un estudiante existente
        $userId = null;
        if ($this->student_id) {
            $student = Student::find($this->student_id);
            if ($student) {
                $userId = $student->user_id;
            }
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students')->ignore($this->student_id),
                Rule::unique('users')->ignore($userId), // Validar contra la tabla de usuarios
            ],
            'cedula' => [
                'required', // La cédula debe ser obligatoria
                'string',
                'max:20',
                // La cédula debe ser única en la tabla de estudiantes
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

        // --- CAMBIO LÓGICA DE CONTRASEÑA ---
        // La contraseña solo se pide si se está EDITANDO y si el admin la escribe
        if ($this->student_id && !empty($this->password)) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }
        // Ya no se pide contraseña al CREAR, se usará la cédula

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
        'cedula.required' => 'La cédula es obligatoria.',
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        'tutor_name.required_if' => 'El nombre del tutor es obligatorio si el estudiante es menor de edad.',
        'tutor_phone.required_if' => 'El teléfono del tutor es obligatorio si el estudiante es menor de edad.',
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
                    ->orWhere('cedula', 'like', '%' . $this->search . '%')
                    ->orWhere('student_code', 'like', '%' . $this->search . '%'); // Añadido student_code
            });
        }

        // Usamos 'with' para Eager Loading y optimizar la consulta
        $students = $query->with('user')
            ->orderBy('first_name', 'asc')
            ->paginate(10);

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
        $this->resetValidation(); // <-- AÑADIDO: Limpiar validaciones previas
        $this->modalTitle = 'Registrar Nuevo Estudiante';
        
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
            $this->birth_date = $student->birth_date ? Carbon::parse($student->birth_date)->format('Y-m-d') : null;
            $this->gender = $student->gender;
            $this->nationality = $student->nationality;
            $this->how_found = $student->how_found;
            $this->is_minor = (bool)$student->is_minor; // Asegurarse de que sea boolean
            $this->tutor_name = $student->tutor_name;
            $this->tutor_cedula = $student->tutor_cedula;
            $this->tutor_phone = $student->tutor_phone;
            $this->tutor_relationship = $student->tutor_relationship;

            // Limpiar campos de contraseña al editar
            $this->password = '';
            $this->password_confirmation = '';

            $this->modalTitle = 'Editar Estudiante: ' . $student->fullName; // Usamos el accesor
            $this->resetValidation(); // <-- AÑADIDO: Limpiar validaciones previas
            
            $this->dispatch('open-modal', 'student-form-modal');

        } catch (\Exception $e) {
            session()->flash('error', 'Estudiante no encontrado.');
            Log::error('Error al editar estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Guarda el estudiante (nuevo o existente).
     * ¡¡¡MÉTODO ACTUALIZADO!!!
     */
    public function saveStudent()
    {
        // Validar usando las reglas actualizadas
        $this->validate($this->rules(), $this->messages);

        try {
            DB::transaction(function () {
                $studentData = [
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
                ];

                if ($this->student_id) {
                    // --- ACTUALIZAR ESTUDIANTE Y USUARIO ---
                    $student = Student::findOrFail($this->student_id);
                    $student->update($studentData);

                    // Actualizar usuario asociado
                    if ($student->user) {
                        $userData = [
                            'name' => $this->first_name . ' ' . $this->last_name,
                        ];

                        $user = $student->user;
                        $emailChanged = $user->email !== $this->email;

                        // Solo actualiza el email del usuario si NO es una matrícula
                        if (!\Illuminate\Support\Str::endsWith($user->getOriginal('email'), '@centu.edu.do')) {
                            $userData['email'] = $this->email;
                        }

                        if (!empty($this->password)) {
                            $userData['password'] = Hash::make($this->password);
                        }
                        
                        // Si el email cambió, marcar como no verificado
                        if ($emailChanged && $user->email !== $this->email) {
                            $userData['email_verified_at'] = null; 
                        }

                        $user->update($userData);
                        
                        // Si el email cambió, reenviar verificación
                        if ($emailChanged && $user->email !== $this->email && $user->hasVerifiedEmail()) {
                            // Nota: El usuario debe tener implementado MustVerifyEmail para que esto funcione
                            // $user->sendEmailVerificationNotification();
                        }
                    }

                    session()->flash('message', 'Estudiante actualizado exitosamente.');

                } else {
                    // --- CREAR NUEVO ESTUDIANTE Y USUARIO ---
                    
                    // 1. Crear Usuario
                    // ¡CAMBIO! La contraseña ahora es la CÉDULA
                    $user = User::create([
                        'name' => $this->first_name . ' ' . $this->last_name,
                        'email' => $this->email, // Email personal
                        'password' => Hash::make($this->cedula), // <-- CONTRASEÑA ES LA CÉDULA
                        'access_expires_at' => Carbon::now()->addMonths(3), // <-- ACCESO TEMPORAL
                    ]);

                    // 2. Asignar Rol
                    $user->assignRole('Estudiante'); // Asumiendo que el rol 'Estudiante' existe

                    // 3. Crear Estudiante
                    $studentData['user_id'] = $user->id;
                    $studentData['status'] = 'Activo'; // O 'Prospecto' si se requiere pago                    
                    Student::create($studentData);

                    session()->flash('message', 'Estudiante creado exitosamente.');
                }
            }); // End transaction

            $this->closeModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Los errores de validación se mostrarán automáticamente
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: Línea ' . $e->getLine() . ' ' . $e->getMessage());
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
            $student = Student::with('user')->find($id);
            if ($student) {
                // Opcional: Eliminar el usuario asociado.
                // Es más seguro hacerlo con una transacción y verificar dependencias.
                DB::transaction(function() use ($student) {
                    // Primero borramos el estudiante
                    $student->delete();
                    // Luego el usuario (si existe)
                    if ($student->user) {
                        $student->user->delete();
                    }
                });
                session()->flash('message', 'Estudiante y usuario asociado eliminados.');
            } else {
                session()->flash('error', 'Estudiante no encontrado.');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar estudiante: ' . $e->getMessage());
            session()->flash('error', 'No se pudo eliminar al estudiante. Verifique si tiene matrículas u otros datos asociados.');
        }
    }

    /**
     * Cierra el modal.
     */
    public function closeModal()
    {
        $this->dispatch('close-modal', 'student-form-modal');
        $this->resetInputFields();
        $this->resetValidation(); // <-- AÑADIDO: Limpiar errores
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
        $this->gender = ''; // Corregido
        $this->nationality = '';
        $this->how_found = '';
        $this->is_minor = false;
        $this->tutor_name = '';
        $this->tutor_cedula = '';
        $this->tutor_phone = '';
        $this->tutor_relationship = '';
        $this->modalTitle = '';
        $this->editing = null;
        $this->password = ''; // Limpiar siempre
        $this->password_confirmation = ''; // Limpiar siempre
    }

    /**
     * Validar en tiempo real
     */
    public function updated($propertyName)
    {
        // Validar solo los campos que lo necesitan para evitar sobrecarga
        if (in_array($propertyName, ['email', 'cedula', 'password', 'password_confirmation'])) {
            $this->validateOnly($propertyName, $this->rules(), $this->messages);
        }
    }
}