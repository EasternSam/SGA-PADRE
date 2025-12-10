<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Module;
use App\Models\Course;
use App\Models\Payment; // Importante para verificar pagos
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseDetail extends Component
{
    public ?Enrollment $enrollment = null; 
    public $attendances = [];
    public $totalClasses = 0;
    public $attendedClasses = 0;
    public $absentClasses = 0;
    public $tardyClasses = 0;

    public $course = null;
    public $allModules = [];

    // --- NUEVO: Control de Bloqueo ---
    public $isBlocked = false;
    public $pendingAmount = 0;
    // ---------------------------------

    public function mount($enrollmentId)
    {
        $studentId = Auth::user()?->student?->id;

        if (!$studentId) {
            session()->flash('error', 'No se pudo verificar su perfil de estudiante.');
            return redirect()->route('student.dashboard'); 
        }

        $enrollment = Enrollment::with(['student', 'courseSchedule.module.course', 'courseSchedule.teacher'])
                                ->where('id', $enrollmentId)
                                ->where('student_id', $studentId)
                                ->first();

        if (!$enrollment) {
            session()->flash('error', 'La inscripción solicitada no se encontró o no le pertenece.');
            return redirect()->route('student.dashboard');
        }

        $this->enrollment = $enrollment;
        
        // Cargar contexto del curso
        if ($this->enrollment->courseSchedule && $this->enrollment->courseSchedule->module) {
            $this->course = $this->enrollment->courseSchedule->module->course;
            $this->allModules = $this->course->modules()->orderBy('id')->get();
        }

        // --- LÓGICA DE BLOQUEO POR MENSUALIDAD VENCIDA ---
        $this->checkOutstandingDebt($studentId);

        if (!$this->isBlocked) {
            // Solo cargamos los datos sensibles si NO está bloqueado
            $this->loadAttendanceSummary();
        }
    }

    /**
     * Verifica si el estudiante tiene pagos vencidos para este curso/inscripción
     */
    private function checkOutstandingDebt($studentId)
    {
        // Buscamos pagos pendientes asociados a esta inscripción cuya fecha de vencimiento ya pasó
        $pendingPayment = Payment::where('student_id', $studentId)
            ->where('enrollment_id', $this->enrollment->id) // Específico de este curso
            ->where('status', 'Pendiente')
            ->where('due_date', '<', now()) // Si la fecha límite ya pasó
            ->first();

        if ($pendingPayment) {
            $this->isBlocked = true;
            $this->pendingAmount = $pendingPayment->amount;
        }
    }

    public function enroll($moduleId)
    {
        // Evitar inscripción si está bloqueado
        if ($this->isBlocked) {
            $this->dispatch('error', 'Debes regularizar tus pagos antes de inscribirte en nuevos módulos.');
            return;
        }

        $student = Auth::user()->student;

        if (!$student) {
            $this->dispatch('error', 'Perfil de estudiante no encontrado.');
            return;
        }

        $moduleToEnroll = Module::find($moduleId);
        if (!$moduleToEnroll) {
            $this->dispatch('error', 'Módulo no encontrado.');
            return;
        }

        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('module_id', $moduleId)
            ->exists();

        if ($alreadyEnrolled) {
            $this->dispatch('info', 'Ya estás inscrito en este módulo.');
            return;
        }

        if ($this->course && $this->course->is_sequential) {
            
            $firstModule = $this->course->modules()->orderBy('id', 'asc')->first();

            if ($firstModule && $moduleToEnroll->id !== $firstModule->id) {
                
                $previousModule = $this->course->modules()
                    ->where('id', '<', $moduleToEnroll->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($previousModule) {
                    $passedPrevious = Enrollment::where('student_id', $student->id)
                        ->where('module_id', $previousModule->id)
                        ->where(function($q) {
                            $q->where('status', 'aprobado')
                              ->orWhere('status', 'completado'); 
                        })
                        ->exists();

                    if (!$passedPrevious) {
                        session()->flash('error', 'Requisito no cumplido: Debes aprobar el módulo "' . $previousModule->name . '" antes de inscribirte en este.');
                        return; 
                    }
                }
            }
        }

        Enrollment::create([
            'student_id' => $student->id,
            'module_id' => $moduleId,
            'status' => 'inscrito', 
            'enrollment_date' => now(),
        ]);

        session()->flash('success', 'Inscripción exitosa en el módulo: ' . $moduleToEnroll->name);
        
        return redirect()->route('student.course-detail', ['enrollmentId' => $this->enrollment->id]);
    }

    public function loadAttendanceSummary()
    {
        if (!$this->enrollment) {
            return;
        }

        try {
            $this->attendances = Attendance::where('enrollment_id', $this->enrollment->id)
                ->orderBy('attendance_date', 'desc')
                ->get();
            
            $this->totalClasses = $this->attendances->count();
            $this->attendedClasses = $this->attendances->where('status', 'Presente')->count();
            $this->absentClasses = $this->attendances->where('status', 'Ausente')->count();
            $this->tardyClasses = $this->attendances->where('status', 'Tardanza')->count();

        } catch (\Exception $e) {
            Log::error("Error al cargar asistencias para enrollment {$this->enrollment->id}: " . $e->getMessage());
            session()->flash('error', 'No se pudo cargar el historial de asistencia.');
        }
    }

    public function requestWithdrawal()
    {
        session()->flash('message', 'Tu solicitud de retiro ha sido enviada.');
    }
    
    public function requestSectionChange()
    {
        session()->flash('message', 'Tu solicitud de cambio de sección ha sido enviada.');
    }

    public function render()
    {
        if (!$this->enrollment) {
            return view('livewire.student-portal.course-detail-empty')
                    ->layout('layouts.dashboard');
        }

        // --- RENDERIZADO CONDICIONAL DE BLOQUEO ---
        // Si hay deuda, retornamos una vista bloqueada o pasamos la variable para que la vista lo maneje
        if ($this->isBlocked) {
             // Opción A: Renderizar una vista específica de bloqueo (Recomendado)
             // return view('livewire.student-portal.course-blocked', ['amount' => $this->pendingAmount]);
             
             // Opción B: Usar la misma vista pero pasar la bandera (requiere modificar el blade)
             // Asumiré Opción B para mantener compatibilidad con tu estructura actual.
             // En tu blade deberías poner: @if($isBlocked) <div...>PAGUE AHORA</div> @else ... @endif
        }
        // -------------------------------------------

        return view('livewire.student-portal.course-detail', [
            'course' => $this->course, 
            'allModules' => $this->allModules,
            'isBlocked' => $this->isBlocked, // Pasamos la variable a la vista
            'pendingAmount' => $this->pendingAmount
        ])->layout('layouts.dashboard'); 
    }
}