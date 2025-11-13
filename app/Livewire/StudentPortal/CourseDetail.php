<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Enrollment;
use App\Models\Attendance;
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

    /**
     * Mounta el componente y carga la información de la inscripción (enrollment).
     *
     * @param int $enrollmentId El ID de la inscripción a mostrar.
     */
    public function mount($enrollmentId)
    {
        // --- ¡CAMBIO! ---
        // Vamos a hacer la verificación de forma más segura.
        
        // 1. Obtener el ID del estudiante de forma segura
        // Se usa el operador '?' (nullsafe) por si 'user' o 'student' son nulos.
        $studentId = Auth::user()?->student?->id;

        // 2. Si no hay ID de estudiante, no podemos continuar.
        if (!$studentId) {
            // Esto es un error grave, probablemente el usuario no es un estudiante.
            // Redirigimos al dashboard con un error.
            session()->flash('error', 'No se pudo verificar su perfil de estudiante.');
            
            // --- ¡¡¡ESTA ES LA CORRECCIÓN DEL BUCLE!!! ---
            // Redirigimos directamente al dashboard de estudiante, NO a la ruta genérica 'dashboard'.
            return redirect()->route('student.dashboard'); 
        }

        // 3. Buscar la inscripción
        // Usamos first() en lugar de firstOrFail()
        $enrollment = Enrollment::with(['student', 'courseSchedule.module.course', 'courseSchedule.teacher'])
                            ->where('id', $enrollmentId)
                            ->where('student_id', $studentId) // Asegurarnos de que la inscripción le pertenece
                            ->first();

        // 4. Verificar si se encontró la inscripción
        // Si $enrollment es nulo, significa que no se encontró o no le pertenece.
        if (!$enrollment) {
            // Esto es lo que probablemente causaba el 404 (a través de firstOrFail).
            // Ahora, redirigimos al dashboard del estudiante con un mensaje de error claro.
            session()->flash('error', 'La inscripción solicitada no se encontró o no le pertenece.');
            return redirect()->route('student.dashboard');
        }

        // 5. Si todo está bien, asignamos la inscripción y cargamos la asistencia
        $this->enrollment = $enrollment;
        $this->loadAttendanceSummary();
    }

    /**
     * Carga y calcula el resumen de asistencia para esta inscripción.
     */
    public function loadAttendanceSummary()
    {
        // Nos aseguramos de que $enrollment no sea nulo antes de usarlo
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

    /**
     * Maneja la solicitud de retiro del curso.
     */
    public function requestWithdrawal()
    {
        session()->flash('message', 'Tu solicitud de retiro ha sido enviada.');
    }
    
    /**
     * Maneja la solicitud de cambio de sección.
     */
    public function requestSectionChange()
    {
        session()->flash('message', 'Tu solicitud de cambio de sección ha sido enviada.');
    }


    /**
     * Renderiza la vista.
     */
    public function render()
    {
        // Si $enrollment es nulo (porque falló el mount), 
        // mostramos la vista pero estará mayormente vacía, 
        // lo cual está bien porque el 'mount' ya habrá redirigido.
        // O podemos ser más explícitos:
        if (!$this->enrollment) {
            // Renderiza una vista "vacía" o de "carga" si la redirección falla
            return view('livewire.student-portal.course-detail-empty')
                    ->layout('layouts.dashboard');
        }

        return view('livewire.student-portal.course-detail')
                ->layout('layouts.dashboard'); // Usa el layout principal
    }
}