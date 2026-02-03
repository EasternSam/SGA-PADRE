<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Admission;
use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public ?Student $student;
    
    // Colecciones separadas para diferenciar tipos de estudios
    public Collection $activeDegreeEnrollments; 
    public Collection $activeCourseEnrollments; 
    
    public Collection $pendingEnrollments;   
    public Collection $completedEnrollments; 
    public Collection $pendingPayments;      
    public Collection $paymentHistory;       

    public ?Course $activeCareer = null;

    public bool $showProfileModal = false;
    
    // Datos perfil
    public $mobile_phone; 
    public $birth_date;   
    public $address;      
    public $gender;       
    public $city;         
    public $sector;       

    // Variables para el modal de inscripción
    public $searchAvailableCourse = '';
    public $selectedScheduleId = null;
    public $availableSchedules = [];

    public function mount()
    {
        $this->user = Auth::user();
        $student = $this->user?->student; 

        if (!$student) {
            if (!request()->routeIs('profile.edit')) {
                session()->flash('error', 'Su cuenta de usuario no está enlazada a un perfil de estudiante.');
                return redirect()->route('profile.edit');
            }
            $this->student = null; 
            $this->initEmptyCollections();
            return;
        }

        $this->student = $student;
        
        // --- Detectar Carrera Activa ---
        // Buscamos la última admisión aprobada que sea de tipo carrera ('degree')
        $admission = Admission::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->whereHas('course', function($q) {
                $q->where('program_type', 'degree');
            })
            ->latest()
            ->first();

        if ($admission) {
            $this->activeCareer = $admission->course;
        }
        
        // --- Cargar datos actuales ---
        $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; 
        $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
        $this->address = $this->student->address;
        $this->gender = $this->student->gender;
        $this->city = $this->student->city;
        $this->sector = $this->student->sector;

        // --- Lógica de Apertura Automática (ONBOARDING) ---
        $hasIncompleteData = (
            $this->isIncomplete($this->mobile_phone) || 
            $this->isIncomplete($this->address) ||
            $this->isIncomplete($this->birth_date) ||
            $this->isIncomplete($this->city)
        );

        if ($hasIncompleteData && !session()->has('profile_onboarding_seen')) {
            $this->showProfileModal = true;
        }

        $this->loadStudentData();
    }

    private function initEmptyCollections()
    {
        $this->activeDegreeEnrollments = collect();
        $this->activeCourseEnrollments = collect();
        $this->pendingEnrollments = collect();
        $this->completedEnrollments = collect();
        $this->pendingPayments = collect();
        $this->paymentHistory = collect();
    }

    private function loadStudentData()
    {
        $baseQuery = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher'
            ])
            ->where('student_id', $this->student->id);

        // 1. Materias de Carrera (program_type = degree)
        $this->activeDegreeEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
            ->whereHas('courseSchedule.module.course', function($q) {
                $q->where('program_type', 'degree');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Cursos Técnicos / Educación Continua (program_type != degree o null)
        $this->activeCourseEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
            ->whereHas('courseSchedule.module.course', function($q) {
                $q->where('program_type', '!=', 'degree')->orWhereNull('program_type');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Pendientes de Pago (General) - Lo mantenemos separado
        $this->pendingEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Pendiente', 'pendiente', 'Enrolled', 'enrolled', 'Pendiente Pago'])
            ->get();

        // Completados
        $this->completedEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Completado', 'completado', 'Aprobado', 'aprobado'])
            ->get();

        // Pagos Pendientes (Deudas)
        $this->pendingPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module.course'])
            ->where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Historial completo
        $this->paymentHistory = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module.course'])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function isIncomplete($value)
    {
        return empty($value) || strtoupper(trim($value)) === 'N/A';
    }

    private function sanitizeInput($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if (empty($trimmed) || strtoupper($trimmed) === 'N/A') {
                return null;
            }
            return $trimmed;
        }
        return $value;
    }

    public function openProfileModal()
    {
        $this->showProfileModal = true;
        $this->dispatch('open-modal', 'complete-profile-modal');
    }

    public function saveProfile()
    {
        $this->validate([
            'mobile_phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Masculino,Femenino,Otro',
            'city' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
        ]);

        $dataToUpdate = [
            'mobile_phone' => $this->sanitizeInput($this->mobile_phone),
            'birth_date' => $this->sanitizeInput($this->birth_date),
            'address' => $this->sanitizeInput($this->address),
            'gender' => $this->sanitizeInput($this->gender),
            'city' => $this->sanitizeInput($this->city),
            'sector' => $this->sanitizeInput($this->sector),
        ];

        if ($this->student) {
            if ($this->isIncomplete($this->student->phone) || empty($this->student->phone)) {
                $dataToUpdate['phone'] = $dataToUpdate['mobile_phone'];
            }

            $this->student->update($dataToUpdate);
            $this->student->refresh();

            session()->flash('message', 'Perfil actualizado exitosamente.');
        }

        $this->closeProfileModal();
    }

    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        $this->dispatch('close-modal', 'complete-profile-modal');
        session()->put('profile_onboarding_seen', true);
    }

    // --- Lógica del Modal de Inscripción ---
    public function openEnrollmentModal()
    {
        $this->reset('searchAvailableCourse', 'selectedScheduleId', 'availableSchedules');
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function updatedSearchAvailableCourse()
    {
        if (strlen($this->searchAvailableCourse) > 2) {
            $this->availableSchedules = CourseSchedule::with(['module.course', 'teacher'])
                ->where('status', 'Activo')
                ->where(function($q) {
                    $q->whereHas('module', function($q2) {
                        $q2->where('name', 'like', '%' . $this->searchAvailableCourse . '%')
                           ->orWhere('code', 'like', '%' . $this->searchAvailableCourse . '%');
                    })
                    ->orWhereHas('module.course', function($q3) {
                        $q3->where('name', 'like', '%' . $this->searchAvailableCourse . '%');
                    });
                })
                ->take(10)
                ->get();
        } else {
            $this->availableSchedules = [];
        }
    }

    public function enrollStudent()
    {
        $this->validate(['selectedScheduleId' => 'required|exists:course_schedules,id']);
        
        // Aquí iría tu lógica de inscripción (puedes copiarla de tu servicio o usar un evento)
        // Por ahora simulamos
        $schedule = CourseSchedule::find($this->selectedScheduleId);
        
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $schedule->id,
            'status' => 'Pendiente', // Asumiendo flujo de pago
            'enrollment_date' => now(),
        ]);
        
        session()->flash('message', 'Solicitud de inscripción creada. Proceda al pago si es necesario.');
        $this->dispatch('close-modal', 'enroll-student-modal');
        $this->loadStudentData();
    }
    
    public function confirmUnenroll($id)
    {
         // Aquí tu lógica para anular
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}