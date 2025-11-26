<?php

namespace App\Livewire\Students;

use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    // --- Propiedades del Formulario ---
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
    
    // Propiedades del Tutor
    public $is_minor = false;
    public $tutor_name = '';
    public $tutor_cedula = '';
    public $tutor_phone = '';
    public $tutor_relationship = '';

    // Campos para el Usuario
    public $password = '';
    public $password_confirmation = '';
    
    // Propiedades de la UI (Optimizadas)
    #[Url(history: true)] // Mantiene la búsqueda en la URL
    public $search = '';
    
    public $modalTitle = '';

    // Propiedad para capturar el ID de edición desde la URL
    #[Url]
    public $editing = null;

    public function mount()
    {
        if ($this->editing) {
            $this->edit($this->editing);
        }
    }

    // Resetear paginación al buscar para evitar páginas vacías
    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
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
                'required', 'email', 'max:255',
                Rule::unique('students')->ignore($this->student_id),
                Rule::unique('users')->ignore($userId),
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

        if ($this->student_id && !empty($this->password)) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

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
     * Renderiza el componente (OPTIMIZADO PARA 200k REGISTROS)
     */
    public function render()
    {
        // 1. Seleccionamos solo las columnas necesarias para la tabla.
        // Esto reduce drásticamente el uso de memoria RAM.
        $query = Student::query()
            ->select([
                'id', 
                'first_name', 
                'last_name', 
                'email', 
                'cedula', 
                'mobile_phone', 
                'student_code', // Necesario para búsqueda
                'user_id'       // Necesario para la relación user
            ]);

        // 2. Lógica de búsqueda optimizada
        if ($this->search) {
            $query->where(function ($q) {
                // Usamos 'like' con prefijo (sin % al inicio) si es posible para usar índices,
                // pero mantenemos % al inicio para flexibilidad si lo necesitas.
                // Nota: % al inicio anula el índice, pero es necesario para buscar apellido.
                $q->where('first_name', 'like', $this->search . '%') // Optimización prefijo
                    ->orWhere('last_name', 'like', $this->search . '%')
                    ->orWhere('cedula', 'like', $this->search . '%') // Optimización prefijo
                    ->orWhere('student_code', 'like', $this->search . '%')
                    ->orWhere('email', 'like', $this->search . '%');
            });
        }

        // 3. Ordenamiento y Paginación
        // Ordenar por ID desc es mucho más rápido que ordenar por string en tablas gigantes.
        // Si necesitas orden alfabético estricto, asegúrate de tener el índice creado.
        $students = $query->orderBy('id', 'desc') // Cambiado a ID desc para velocidad por defecto
            ->paginate(10);

        return view('livewire.students.index', [
            'students' => $students,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->modalTitle = 'Registrar Nuevo Estudiante';
        $this->dispatch('open-modal', 'student-form-modal');
    }

    public function edit($id)
    {
        try {
            $student = Student::findOrFail($id);
            $this->student_id = $id;
            
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
            $this->is_minor = (bool)$student->is_minor;
            $this->tutor_name = $student->tutor_name;
            $this->tutor_cedula = $student->tutor_cedula;
            $this->tutor_phone = $student->tutor_phone;
            $this->tutor_relationship = $student->tutor_relationship;

            $this->password = '';
            $this->password_confirmation = '';

            $this->modalTitle = 'Editar Estudiante: ' . $student->fullName;
            $this->resetValidation();
            
            $this->dispatch('open-modal', 'student-form-modal');

        } catch (\Exception $e) {
            session()->flash('error', 'Estudiante no encontrado.');
            Log::error('Error al editar estudiante: ' . $e->getMessage());
        }
    }

    public function saveStudent()
    {
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
                    $student = Student::findOrFail($this->student_id);
                    $student->update($studentData);

                    if ($student->user) {
                        $userData = [
                            'name' => $this->first_name . ' ' . $this->last_name,
                        ];

                        $user = $student->user;
                        $emailChanged = $user->email !== $this->email;

                        if (!\Illuminate\Support\Str::endsWith($user->getOriginal('email'), '@centu.edu.do')) {
                            $userData['email'] = $this->email;
                        }

                        if (!empty($this->password)) {
                            $userData['password'] = Hash::make($this->password);
                        }
                        
                        if ($emailChanged && $user->email !== $this->email) {
                            $userData['email_verified_at'] = null; 
                        }

                        $user->update($userData);
                    }

                    session()->flash('message', 'Estudiante actualizado exitosamente.');

                } else {
                    $user = User::create([
                        'name' => $this->first_name . ' ' . $this->last_name,
                        'email' => $this->email,
                        'password' => Hash::make($this->cedula),
                        'access_expires_at' => Carbon::now()->addMonths(3),
                    ]);

                    $user->assignRole('Estudiante');

                    $studentData['user_id'] = $user->id;
                    $studentData['status'] = 'Activo';
                    Student::create($studentData);

                    session()->flash('message', 'Estudiante creado exitosamente.');
                }
            });

            $this->closeModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al guardar estudiante: Línea ' . $e->getLine() . ' ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el estudiante: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $student = Student::with('user')->find($id);
            if ($student) {
                DB::transaction(function() use ($student) {
                    $student->delete();
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
            session()->flash('error', 'No se pudo eliminar al estudiante. Verifique si tiene matrículas asociadas.');
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'student-form-modal');
        $this->resetInputFields();
        $this->resetValidation();
    }

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
        $this->gender = '';
        $this->nationality = '';
        $this->how_found = '';
        $this->is_minor = false;
        $this->tutor_name = '';
        $this->tutor_cedula = '';
        $this->tutor_phone = '';
        $this->tutor_relationship = '';
        $this->modalTitle = '';
        $this->editing = null;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['email', 'cedula', 'password', 'password_confirmation'])) {
            $this->validateOnly($propertyName, $this->rules(), $this->messages);
        }
    }
}