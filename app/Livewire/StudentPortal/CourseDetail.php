<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Module; // Añadido
use App\Models\Course; // Añadido
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseDetail extends Component
{
    // Usar '?' para permitir que $enrollment sea nulo temporalmente si falla la carga
    public ?Enrollment $enrollment = null; 
    public $attendances = [];
    public $totalClasses = 0;
    public $attendedClasses = 0;
    public $absentClasses = 0;
    public $tardyClasses = 0;

    // NUEVAS PROPIEDADES PARA NAVEGACIÓN Y LÓGICA SECUENCIAL
    public $course = null;
    public $allModules = [];

    /**
     * Mounta el componente y carga la información de la inscripción (enrollment).
     *
     * @param int $enrollmentId El ID de la inscripción a mostrar.
     */
    public function mount($enrollmentId)
    {
        // 1. Obtener el ID del estudiante de forma segura
        $studentId = Auth::user()?->student?->id;

        // 2. Si no hay ID de estudiante, no podemos continuar.
        if (!$studentId) {
            session()->flash('error', 'No se pudo verificar su perfil de estudiante.');
            return redirect()->route('student.dashboard'); 
        }

        // 3. Buscar la inscripción
        $enrollment = Enrollment::with(['student', 'courseSchedule.module.course', 'courseSchedule.teacher'])
                                ->where('id', $enrollmentId)
                                ->where('student_id', $studentId)
                                ->first();

        // 4. Verificar si se encontró la inscripción
        if (!$enrollment) {
            session()->flash('error', 'La inscripción solicitada no se encontró o no le pertenece.');
            return redirect()->route('student.dashboard');
        }

        // 5. Si todo está bien, asignamos la inscripción y cargamos la asistencia
        $this->enrollment = $enrollment;
        
        // --- NUEVO: Cargar contexto del curso y otros módulos ---
        if ($this->enrollment->courseSchedule && $this->enrollment->courseSchedule->module) {
            $this->course = $this->enrollment->courseSchedule->module->course;
            // Cargar todos los módulos del curso para mostrarlos
            $this->allModules = $this->course->modules()->orderBy('id')->get();
        }
        // --- FIN NUEVO ---

        $this->loadAttendanceSummary();
    }

    /**
     * NUEVA FUNCIÓN: Inscribirse en un módulo siguiente (Lógica Secuencial)
     */
    public function enroll($moduleId)
    {
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

        // Verificar si ya está inscrito
        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('module_id', $moduleId)
            ->exists();

        if ($alreadyEnrolled) {
            $this->dispatch('info', 'Ya estás inscrito en este módulo.');
            return;
        }

        // LÓGICA DE PRERREQUISITOS (Secuencial)
        if ($this->course && $this->course->is_sequential) {
            
            // Buscar el primer módulo del curso (asumiendo orden por ID)
            $firstModule = $this->course->modules()->orderBy('id', 'asc')->first();

            // Si el módulo al que queremos inscribirnos NO es el primero
            if ($firstModule && $moduleToEnroll->id !== $firstModule->id) {
                
                // Buscar el módulo inmediatamente anterior
                $previousModule = $this->course->modules()
                    ->where('id', '<', $moduleToEnroll->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($previousModule) {
                    // Verificar si el estudiante aprobó (o completó) el módulo anterior
                    $passedPrevious = Enrollment::where('student_id', $student->id)
                        ->where('module_id', $previousModule->id)
                        ->where(function($q) {
                            $q->where('status', 'aprobado')
                              ->orWhere('status', 'completado'); 
                        })
                        ->exists();

                    if (!$passedPrevious) {
                        session()->flash('error', 'Requisito no cumplido: Debes aprobar el módulo "' . $previousModule->name . '" antes de inscribirte en este.');
                        return; // Detener proceso
                    }
                }
            }
        }

        // Si pasa validaciones, crear inscripción
        Enrollment::create([
            'student_id' => $student->id,
            'module_id' => $moduleId,
            'status' => 'inscrito', 
            'enrollment_date' => now(),
        ]);

        session()->flash('success', 'Inscripción exitosa en el módulo: ' . $moduleToEnroll->name);
        
        // Recargar la página para ver cambios
        return redirect()->route('student.course-detail', ['enrollmentId' => $this->enrollment->id]);
    }

    /**
     * Carga y calcula el resumen de asistencia para esta inscripción.
     */
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

    /**
     * Renderiza la vista.
     */
    public function render()
    {
        if (!$this->enrollment) {
            return view('livewire.student-portal.course-detail-empty')
                    ->layout('layouts.dashboard');
        }

        return view('livewire.student-portal.course-detail', [
            'course' => $this->course, // Pasar variable extra a la vista
            'allModules' => $this->allModules // Pasar módulos para listar opciones
        ])->layout('layouts.dashboard'); 
    }
}