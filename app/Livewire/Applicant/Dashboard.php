<?php

namespace App\Livewire\Applicant;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Admission;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')] // Usa el layout autenticado, no el guest
class Dashboard extends Component
{
    use WithFileUploads;

    public $admission; // Instancia de la admisión si existe
    public $existing_application = false;

    // Campos del formulario (igual que antes)
    public $first_name;
    public $last_name;
    public $identification_id;
    public $birth_date;
    public $nationality;
    public $address;
    public $phone;
    public $phone2;
    public $works = 'no';
    public $work_place;
    public $disease;
    public $course_id;
    public $previous_school;
    public $previous_gpa;

    // Archivos
    public $file_birth_certificate;
    public $file_id_card;
    public $file_high_school_record;
    public $file_medical_certificate;
    public $file_payment_receipt;
    public $file_bachelor_certificate;
    public $file_photo;

    // Feedback visual
    public $success_message = false;

    public function mount()
    {
        $user = Auth::user();
        
        // Verificar si el usuario ya tiene una solicitud
        $this->admission = Admission::where('user_id', $user->id)->first();

        if ($this->admission) {
            $this->existing_application = true;
            // Cargar datos existentes por si necesitan verlos (modo lectura o re-envío)
            $this->course_id = $this->admission->course_id;
            // ... cargar otros si es necesario para edición
        } else {
            // Pre-llenar datos conocidos del usuario
            $this->first_name = $user->name; // Asumiendo que name tiene el nombre completo o parcial
            $this->email = $user->email;
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
        
        // Archivos requeridos solo si es nueva solicitud
        'file_birth_certificate' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_id_card' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_high_school_record' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_photo' => 'required|image|max:5120',
    ];

    public function save()
    {
        $this->validate();

        // Subir archivos
        $documents = [
            'birth_certificate' => $this->file_birth_certificate->store('admissions/birth_certificates', 'public'),
            'id_card' => $this->file_id_card->store('admissions/id_cards', 'public'),
            'high_school_record' => $this->file_high_school_record->store('admissions/records', 'public'),
            'medical_certificate' => $this->file_medical_certificate ? $this->file_medical_certificate->store('admissions/medical', 'public') : null,
            'payment_receipt' => $this->file_payment_receipt ? $this->file_payment_receipt->store('admissions/payments', 'public') : null,
            'bachelor_certificate' => $this->file_bachelor_certificate ? $this->file_bachelor_certificate->store('admissions/bachelor', 'public') : null,
            'photo' => $this->file_photo->store('admissions/photos', 'public'),
        ];

        Admission::create([
            'user_id' => Auth::id(), // Vinculación CLAVE
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => Auth::user()->email,
            'identification_id' => $this->identification_id,
            'birth_date' => $this->birth_date,
            'address' => $this->address . ' (Nacionalidad: ' . $this->nationality . ')',
            'phone' => $this->phone . ($this->phone2 ? ' / ' . $this->phone2 : ''),
            'work_place' => $this->works === 'si' ? $this->work_place : null,
            'disease' => $this->disease,
            'course_id' => $this->course_id,
            'previous_school' => $this->previous_school,
            'previous_gpa' => $this->previous_gpa,
            'documents' => $documents,
            'status' => 'pending',
        ]);

        $this->success_message = true;
        $this->existing_application = true;
        $this->admission = Admission::where('user_id', Auth::id())->first();
    }

    public function render()
    {
        $courses = Course::where('status', 'Activo')->orderBy('name')->get();
        return view('livewire.applicant.dashboard', [
            'courses' => $courses
        ]);
    }
}