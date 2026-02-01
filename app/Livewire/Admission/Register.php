<?php

namespace App\Livewire\Admissions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads; // Importar Trait
use App\Models\Admission;
use App\Models\Course;

#[Layout('layouts.guest')] 
class Register extends Component
{
    use WithFileUploads; // Usar Trait para archivos

    // Datos Personales
    public $first_name;
    public $last_name;
    public $identification_id;
    public $birth_date;
    public $nationality; // Nuevo campo sugerido por el texto
    
    // Contacto
    public $address;
    public $email;
    public $phone;
    public $phone2; // Opcional

    // Otros
    public $works = 'no'; // Si/No
    public $work_place;
    public $disease;

    // Académico
    public $course_id;
    public $previous_school;
    public $previous_gpa;

    // Archivos (Requisitos)
    public $file_birth_certificate;
    public $file_id_card;
    public $file_high_school_record;
    public $file_medical_certificate;
    public $file_payment_receipt;
    public $file_bachelor_certificate;
    public $file_photo;

    public $success = false;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'identification_id' => 'required|string|max:20',
        'birth_date' => 'required|date',
        'nationality' => 'required|string|max:100',
        
        'address' => 'required|string|max:500',
        'email' => 'required|email|unique:admissions,email|unique:users,email',
        'phone' => 'required|string|max:20',
        'phone2' => 'nullable|string|max:20',

        'works' => 'required|in:si,no',
        'work_place' => 'required_if:works,si|nullable|string|max:255',
        'disease' => 'nullable|string|max:255',

        'course_id' => 'required|exists:courses,id',
        'previous_school' => 'required|string|max:255',
        'previous_gpa' => 'nullable|numeric|between:0,100',

        // Validación de Archivos (max 5MB c/u)
        'file_birth_certificate' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_id_card' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_high_school_record' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_medical_certificate' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_payment_receipt' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_bachelor_certificate' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_photo' => 'required|image|max:5120', // Solo imagen para la foto
    ];

    public function save()
    {
        $this->validate();

        // Subir archivos y guardar rutas
        $documents = [
            'birth_certificate' => $this->file_birth_certificate->store('admissions/birth_certificates', 'public'),
            'id_card' => $this->file_id_card->store('admissions/id_cards', 'public'),
            'high_school_record' => $this->file_high_school_record->store('admissions/records', 'public'),
            'medical_certificate' => $this->file_medical_certificate->store('admissions/medical', 'public'),
            'payment_receipt' => $this->file_payment_receipt->store('admissions/payments', 'public'),
            'bachelor_certificate' => $this->file_bachelor_certificate->store('admissions/bachelor', 'public'),
            'photo' => $this->file_photo->store('admissions/photos', 'public'),
        ];

        Admission::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'identification_id' => $this->identification_id,
            'birth_date' => $this->birth_date,
            'address' => $this->address . ' (Nacionalidad: ' . $this->nationality . ')',
            'email' => $this->email,
            'phone' => $this->phone . ($this->phone2 ? ' / ' . $this->phone2 : ''),
            'work_place' => $this->works === 'si' ? $this->work_place : null,
            'disease' => $this->disease,
            'course_id' => $this->course_id,
            'previous_school' => $this->previous_school,
            'previous_gpa' => $this->previous_gpa,
            'documents' => $documents,
            'status' => 'pending',
        ]);

        $this->success = true;
        $this->reset();
    }

    public function render()
    {
        $courses = Course::where('program_type', 'degree')
            ->where('status', 'Activo')
            ->orderBy('name')
            ->get();

        return view('livewire.admissions.register', [
            'courses' => $courses
        ]);
    }
}