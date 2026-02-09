<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Admission;
use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
// use Livewire\Attributes\Lazy; 
use Illuminate\Support\Collection;
use Carbon\Carbon;

// #[Lazy] // Mantener desactivado mientras depuras para evitar latencia en la carga inicial
#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    use WithFileUploads;

    public ?Student $student;
    public $user;
    
    // Colecciones
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
    
    // Foto de Perfil
    public $photo; 
    public $current_photo_url; 

    // Variables modal
    public $searchAvailableCourse = '';
    public $selectedScheduleId = null;
    public $availableSchedules = [];

    public function placeholder()
    {
        return <<<'HTML'
        <div class="min-h-screen bg-gray-50/50 p-8">
            <div class="animate-pulse space-y-8">
                <div class="h-8 bg-gray-200 rounded w-1/4"></div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                    <div class="h-32 bg-gray-200 rounded-xl"></div>
                </div>
                <div class="h-64 bg-gray-200 rounded-xl"></div>
            </div>
        </div>
        HTML;
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->student = $this->user?->student;

        $this->initEmptyCollections();

        if ($this->student) {
            $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; 
            $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
            $this->address = $this->student->address;
            $this->gender = $this->student->gender;
            $this->city = $this->student->city;
            $this->sector = $this->student->sector;
            $this->current_photo_url = $this->student->profile_photo_url; 

            $hasIncompleteData = (
                $this->isIncomplete($this->mobile_phone) || 
                $this->isIncomplete($this->address) || 
                $this->isIncomplete($this->birth_date) || 
                $this->isIncomplete($this->city)
            );

            if ($hasIncompleteData && !session()->has('profile_onboarding_seen')) {
                $this->showProfileModal = true;
            }

            $this->loadData();
        }
    }

    // --- Hook de Ciclo de Vida: Se ejecuta automáticamente al recibir un archivo ---
    public function updatedPhoto()
    {
        Log::info('[BACKEND] Hook updatedPhoto disparado.');
        
        try {
            $this->validate([
                'photo' => 'image|max:10240', // 10MB Máximo
            ]);
            Log::info('[BACKEND] Foto validada temporalmente correctamente.');
        } catch (\Exception $e) {
            Log::error('[BACKEND] Error validando foto temporal: ' . $e->getMessage());
            // Reiniciamos la foto si no es válida para evitar errores al guardar
            $this->reset('photo'); 
        }
    }

    public function loadData()
    {
        if (!$this->student) return;

        $admission = Admission::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->whereHas('course', fn($q) => $q->where('program_type', 'degree'))
            ->with('course')
            ->latest()
            ->first();

        if ($admission) {
            $this->activeCareer = $admission->course;
        }

        $this->loadStudentDataOptimized();
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

    private function loadStudentDataOptimized()
    {
        $allEnrollments = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher',
                'payment'
            ])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->activeDegreeEnrollments = $allEnrollments->filter(function ($e) {
            $isDegree = optional($e->courseSchedule->module->course)->program_type === 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo', 'pendiente', 'pendiente pago', 'enrolled']);
            return $isDegree && $isActive;
        });

        $this->activeCourseEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo']);
            return $isTechnical && $isActive;
        });

        $this->pendingEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isPending = in_array(strtolower($e->status), ['pendiente', 'enrolled', 'pendiente pago']);
            return $isTechnical && $isPending;
        });

        $this->completedEnrollments = $allEnrollments->filter(function ($e) {
            return in_array(strtolower($e->status), ['completado', 'aprobado']);
        });

        $allPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module.course'])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->pendingPayments = $allPayments->whereIn('status', ['Pendiente', 'pendiente']);
        $this->paymentHistory = $allPayments;
    }

    private function isIncomplete($value)
    {
        return empty($value) || strtoupper(trim($value)) === 'N/A';
    }

    private function sanitizeInput($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if (empty($trimmed) || strtoupper($trimmed) === 'N/A') return null;
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
            'photo' => 'nullable|image|max:10240', // 10MB
        ]);

        $dataToUpdate = [
            'mobile_phone' => $this->sanitizeInput($this->mobile_phone),
            'birth_date' => $this->sanitizeInput($this->birth_date),
            'address' => $this->sanitizeInput($this->address),
            'gender' => $this->sanitizeInput($this->gender),
            'city' => $this->sanitizeInput($this->city),
            'sector' => $this->sanitizeInput($this->sector),
        ];

        if ($this->photo) {
            try {
                if ($this->student->profile_photo_path && Storage::disk('public')->exists($this->student->profile_photo_path)) {
                    Storage::disk('public')->delete($this->student->profile_photo_path);
                }
                
                $path = $this->photo->store('profile-photos', 'public');
                $dataToUpdate['profile_photo_path'] = $path;
                Log::info('[BACKEND] Nueva foto guardada: ' . $path);
            } catch (\Exception $e) {
                Log::error('[BACKEND] Error guardando foto en disco: ' . $e->getMessage());
                session()->flash('error', 'Error al guardar la imagen.');
                return; // Detener para no guardar datos parciales si falla la foto crítica
            }
        }

        if ($this->student) {
            if ($this->isIncomplete($this->student->phone) || empty($this->student->phone)) {
                $dataToUpdate['phone'] = $dataToUpdate['mobile_phone'];
            }
            
            $this->student->update($dataToUpdate);
            $this->student->refresh();
            
            // Actualizar estado local
            $this->mobile_phone = $this->student->mobile_phone;
            $this->address = $this->student->address;
            $this->current_photo_url = $this->student->profile_photo_url; 
            $this->photo = null; 

            session()->flash('message', 'Perfil actualizado exitosamente.');
        }
        $this->closeProfileModal();
    }

    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        $this->photo = null; 
        $this->dispatch('close-modal', 'complete-profile-modal');
        session()->put('profile_onboarding_seen', true);
    }

    public function openEnrollmentModal()
    {
        $this->reset('searchAvailableCourse', 'selectedScheduleId', 'availableSchedules');
        $this->dispatch('open-modal', 'enroll-student-modal');
    }

    public function updatedSearchAvailableCourse()
    {
        if (strlen($this->searchAvailableCourse) > 2) {
            $this->availableSchedules = CourseSchedule::with([
                    'module:id,course_id,name,code',
                    'module.course:id,name',
                    'teacher:id,first_name,last_name'
                ])
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
        
        $schedule = CourseSchedule::with('module.course')->find($this->selectedScheduleId);
        
        $isDegree = $schedule->module->course->program_type === 'degree';
        $initialStatus = $isDegree ? 'Cursando' : 'Pendiente';

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_schedule_id' => $schedule->id,
            'status' => $initialStatus,
            'enrollment_date' => now(),
        ]);
        
        if ($isDegree) {
             session()->flash('message', 'Materia inscrita correctamente.');
        } else {
             session()->flash('message', 'Solicitud creada. Proceda al pago.');
        }
        
        $this->dispatch('close-modal', 'enroll-student-modal');
        $this->loadStudentDataOptimized(); 
    }
    
    public function confirmUnenroll($id) {}

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}