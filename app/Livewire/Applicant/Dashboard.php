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

    // Campos del formulario (Para nueva solicitud)
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

    // Archivos (Para nueva solicitud)
    public $file_birth_certificate;
    public $file_id_card;
    public $file_high_school_record;
    public $file_medical_certificate;
    public $file_payment_receipt;
    public $file_bachelor_certificate;
    public $file_photo;

    // Para re-subida de documentos rechazados
    public $reupload_files = []; 

    public $success_message = false;

    public function mount()
    {
        $user = Auth::user();
        $this->admission = Admission::where('user_id', $user->id)->with('course')->first();

        if ($this->admission) {
            $this->existing_application = true;
            $this->course_id = $this->admission->course_id;
        } else {
            // Pre-llenar datos
            if ($user->student) {
                $this->first_name = $user->student->first_name;
                $this->last_name = $user->student->last_name;
                $this->identification_id = $user->student->cedula;
                $this->email = $user->email;
            } else {
                $this->first_name = $user->name;
                $this->email = $user->email;
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
        'file_birth_certificate' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_id_card' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_high_school_record' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        'file_photo' => 'required|image|max:5120',
    ];

    public function save()
    {
        $this->validate();

        $documents = [
            'birth_certificate' => $this->file_birth_certificate->store('admissions/birth_certificates', 'public'),
            'id_card' => $this->file_id_card->store('admissions/id_cards', 'public'),
            'high_school_record' => $this->file_high_school_record->store('admissions/records', 'public'),
            'medical_certificate' => $this->file_medical_certificate ? $this->file_medical_certificate->store('admissions/medical', 'public') : null,
            'payment_receipt' => $this->file_payment_receipt ? $this->file_payment_receipt->store('admissions/payments', 'public') : null,
            'bachelor_certificate' => $this->file_bachelor_certificate ? $this->file_bachelor_certificate->store('admissions/bachelor', 'public') : null,
            'photo' => $this->file_photo->store('admissions/photos', 'public'),
        ];

        // Estado inicial de documentos: todos pendientes
        $docStatus = array_fill_keys(array_keys($documents), 'pending');

        Admission::create([
            'user_id' => Auth::id(),
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
            'document_status' => $docStatus,
            'status' => 'pending',
        ]);

        $this->success_message = true;
        $this->existing_application = true;
        $this->admission = Admission::where('user_id', Auth::id())->with('course')->first();
    }

    public function reuploadDocument($key)
    {
        $this->validate([
            'reupload_files.'.$key => 'required|file|max:5120', // 5MB max
        ]);

        $file = $this->reupload_files[$key];
        
        // Determinar carpeta basada en la clave
        $folder = match($key) {
            'birth_certificate' => 'admissions/birth_certificates',
            'id_card' => 'admissions/id_cards',
            'high_school_record' => 'admissions/records',
            'photo' => 'admissions/photos',
            default => 'admissions/others',
        };

        // Guardar nuevo archivo
        $path = $file->store($folder, 'public');

        // Actualizar registro
        $documents = $this->admission->documents;
        $documents[$key] = $path;

        $statuses = $this->admission->document_status ?? [];
        $statuses[$key] = 'pending'; // Volver a poner en pendiente para revisión

        $this->admission->update([
            'documents' => $documents,
            'document_status' => $statuses,
            'status' => 'pending', // La solicitud vuelve a pendiente global
        ]);

        // Limpiar input
        $this->reupload_files[$key] = null;
        
        session()->flash('message', 'Documento actualizado correctamente. Pendiente de revisión.');
    }

    public function render()
    {
        $courses = Course::where('status', 'Activo')->orderBy('name')->get();
        return view('livewire.applicant.dashboard', [
            'courses' => $courses
        ]);
    }
}