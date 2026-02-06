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
use Livewire\Attributes\Lazy; // <-- Optimización: Carga diferida
use Illuminate\Support\Collection;
use Carbon\Carbon;

#[Lazy] // Indica a Livewire que cargue este componente después de pintar el HTML inicial
#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
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

    // Variables modal
    public $searchAvailableCourse = '';
    public $selectedScheduleId = null;
    public $availableSchedules = [];

    // Placeholder que se muestra mientras cargan los datos pesados
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

        // Inicializamos vacío para evitar errores antes de que loadData se ejecute
        $this->initEmptyCollections();

        if ($this->student) {
            // Cargar datos ligeros de perfil inmediatamente
            $this->mobile_phone = $this->student->mobile_phone ?? $this->student->phone; 
            $this->birth_date = $this->student->birth_date ? $this->student->birth_date->format('Y-m-d') : null;
            $this->address = $this->student->address;
            $this->gender = $this->student->gender;
            $this->city = $this->student->city;
            $this->sector = $this->student->sector;

            // Verificar si faltan datos
            $hasIncompleteData = (
                $this->isIncomplete($this->mobile_phone) || 
                $this->isIncomplete($this->address) ||
                $this->isIncomplete($this->birth_date) ||
                $this->isIncomplete($this->city)
            );

            if ($hasIncompleteData && !session()->has('profile_onboarding_seen')) {
                $this->showProfileModal = true;
            }
        }
    }

    // Método mágico que se ejecuta automáticamente al usar #[Lazy]
    public function loadData()
    {
        if (!$this->student) return;

        // 1. Cargar Carrera Activa
        $admission = Admission::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->whereHas('course', fn($q) => $q->where('program_type', 'degree'))
            ->with('course') // Eager load
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

    // Versión OPTIMIZADA: Reduce consultas de N+1 a 2 consultas maestras
    private function loadStudentDataOptimized()
    {
        // Consulta 1: Traer TODAS las inscripciones con sus relaciones en una sola ida a la DB
        $allEnrollments = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher',
                'payment' // Relación directa si existe
            ])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Filtrado en MEMORIA (PHP) - Mucho más rápido que múltiples SQL
        
        // 1. Materias de Carrera (Incluye 'Pendiente' porque la inscripción habilita el cursado)
        $this->activeDegreeEnrollments = $allEnrollments->filter(function ($e) {
            $isDegree = optional($e->courseSchedule->module->course)->program_type === 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo', 'pendiente', 'pendiente pago', 'enrolled']);
            return $isDegree && $isActive;
        });

        // 2. Cursos Técnicos (Solo activos reales)
        $this->activeCourseEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isActive = in_array(strtolower($e->status), ['cursando', 'activo']);
            return $isTechnical && $isActive;
        });

        // 3. Pendientes de Pago (Solo Técnicos)
        $this->pendingEnrollments = $allEnrollments->filter(function ($e) {
            $courseType = optional($e->courseSchedule->module->course)->program_type;
            $isTechnical = $courseType !== 'degree';
            $isPending = in_array(strtolower($e->status), ['pendiente', 'enrolled', 'pendiente pago']);
            return $isTechnical && $isPending;
        });

        // 4. Completados
        $this->completedEnrollments = $allEnrollments->filter(function ($e) {
            return in_array(strtolower($e->status), ['completado', 'aprobado']);
        });

        // Consulta 2: Traer TODOS los pagos
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
            // Optimización: Select específico para no traer datos basura
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
        // Ajuste de lógica de negocio: Carreras entran Cursando, Cursos Técnicos entran Pendiente
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
        $this->loadStudentDataOptimized(); // Recarga optimizada
    }
    
    public function confirmUnenroll($id) {}

    public function render()
    {
        // Disparador manual para Lazy Loading si por alguna razón falla el automático
        if (empty($this->activeDegreeEnrollments) && $this->student) {
            $this->loadData();
        }
        return view('livewire.student-portal.dashboard');
    }
}