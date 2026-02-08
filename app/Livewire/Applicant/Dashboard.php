<?php

namespace App\Livewire\Applicant;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    use WithFileUploads;

    public $admission;
    public $existing_application = false;

    // Campos del formulario
    public $first_name;
    public $last_name;
    public $identification_id;
    public $birth_date;
    public $nationality;
    public $address;
    public $phone;
    
    // Académico
    public $course_id;
    public $previous_school;
    public $previous_gpa;

    // Archivos
    public $file_birth_certificate;
    public $file_id_card;
    public $file_high_school_record;
    public $file_photo;

    // Propiedades para re-subida de documentos (si fuera necesario en el futuro)
    public $reupload_files = []; 

    public function mount()
    {
        $user = Auth::user();
        
        // Buscar si ya existe una admisión para este usuario
        $this->admission = Admission::where('user_id', $user->id)->with('course')->first();

        if ($this->admission) {
            $this->existing_application = true;
            $this->course_id = $this->admission->course_id;
        } else {
            // Pre-llenar datos disponibles del usuario
            if ($user->student) {
                $this->first_name = $user->student->first_name;
                $this->last_name = $user->student->last_name;
                $this->identification_id = $user->student->cedula ?? ''; 
            } else {
                $parts = explode(' ', $user->name, 2);
                $this->first_name = $parts[0];
                $this->last_name = $parts[1] ?? '';
            }
        }
    }

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'identification_id' => 'required|string|max:20',
        'birth_date' => 'required|date',
        'nationality' => 'required|string|max:100',
        'address' => 'required|string|max:500',
        'phone' => 'required|string|max:20',
        'course_id' => 'required|exists:courses,id',
        'previous_school' => 'required|string|max:255',
        'previous_gpa' => 'nullable|numeric|between:0,100',
        
        // Validación de Archivos BLINDADA (Max 5MB = 5120KB)
        'file_birth_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'file_id_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'file_high_school_record' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'file_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120', 
    ];

    protected $validationAttributes = [
        'first_name' => 'nombres',
        'last_name' => 'apellidos',
        'identification_id' => 'cédula',
        'birth_date' => 'fecha de nacimiento',
        'nationality' => 'nacionalidad',
        'address' => 'dirección',
        'phone' => 'teléfono',
        'course_id' => 'carrera',
        'previous_school' => 'escuela de procedencia',
        'previous_gpa' => 'promedio',
        'file_birth_certificate' => 'acta de nacimiento',
        'file_id_card' => 'cédula de identidad',
        'file_high_school_record' => 'récord de notas',
        'file_photo' => 'fotografía',
    ];

    public function save()
    {
        $this->validate();

        // Subir archivos a DISCO PRIVADO ('local')
        // Esto los guarda en /storage/app/admissions/... y NO son accesibles por URL pública
        $documents = [
            'birth_certificate' => $this->file_birth_certificate->store('admissions/birth_certificates', 'local'),
            'id_card' => $this->file_id_card->store('admissions/id_cards', 'local'),
            'high_school_record' => $this->file_high_school_record->store('admissions/records', 'local'),
            'photo' => $this->file_photo->store('admissions/photos', 'local'),
        ];

        $docStatus = array_fill_keys(array_keys($documents), 'pending');

        $admission = Admission::create([
            'user_id' => Auth::id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => Auth::user()->email,
            'identification_id' => $this->identification_id,
            'birth_date' => $this->birth_date,
            'address' => $this->address . ' (Nacionalidad: ' . $this->nationality . ')',
            'phone' => $this->phone,
            'course_id' => $this->course_id,
            'previous_school' => $this->previous_school,
            'previous_gpa' => $this->previous_gpa,
            'documents' => $documents,
            'document_status' => $docStatus,
            'status' => 'pending',
        ]);

        $this->admission = $admission;
        $this->existing_application = true;
        
        session()->flash('message', 'Solicitud enviada correctamente. Tus documentos están seguros y en revisión.');
    }

    public function render()
    {
        $courses = Course::where('program_type', 'degree')
            ->where('status', 'Activo')
            ->orderBy('name')
            ->get();

        return view('livewire.applicant.dashboard', [
            'courses' => $courses
        ]);
    }
}