<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    // --- Propiedades ---
    public ?Student $student;
    
    // Colecciones de datos
    public Collection $activeEnrollments;    // Cursos 'Cursando' o 'Activo'
    public Collection $pendingEnrollments;   // Inscripciones 'Pendiente' (de pago)
    public Collection $completedEnrollments; // Cursos 'Completado'
    public Collection $pendingPayments;      // Pagos con estado 'Pendiente' (para alertas)
    public Collection $paymentHistory;       // Historial completo de pagos (NUEVO)

    /**
     * Mounta el componente y carga todos los datos del estudiante.
     */
    public function mount()
    {
        // 1. Obtener el estudiante de forma segura
        $student = Auth::user()?->student; 

        // 2. Verificar si el estudiante existe
        if (!$student) {
            if (!request()->routeIs('profile.edit')) {
                session()->flash('error', 'Su cuenta de usuario no está enlazada a un perfil de estudiante. Por favor, contacte a soporte.');
                return redirect()->route('profile.edit');
            }
            
            $this->student = null; 
            // Inicializar colecciones vacías para evitar errores en la vista
            $this->activeEnrollments = collect();
            $this->pendingEnrollments = collect();
            $this->completedEnrollments = collect();
            $this->pendingPayments = collect();
            $this->paymentHistory = collect();
            return;
        }

        // 3. Si existe, asignamos las propiedades
        $this->student = $student;
        
        // Base query para inscripciones
        $baseQuery = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher'
            ])
            ->where('student_id', $this->student->id);

        // --- Carga de Datos ---

        // A. Cursos Activos
        $this->activeEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Cursando', 'cursando', 'Activo', 'activo'])
            ->get();

        // B. Inscripciones Pendientes (Inscrito pero no cursando aun)
        $this->pendingEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Pendiente', 'pendiente', 'Enrolled', 'enrolled'])
            ->get();

        // C. Cursos Completados
        $this->completedEnrollments = (clone $baseQuery)
            ->whereIn('status', ['Completado', 'completado'])
            ->get();

        // D. Pagos Pendientes (Para la alerta amarilla)
        $this->pendingPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->where('student_id', $this->student->id)
            ->where('status', 'Pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        // E. Historial Completo de Pagos (Para la tabla lateral)
        // Usamos tus relaciones: paymentConcept, enrollment->...
        $this->paymentHistory = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc') // Ordenar por fecha de creación (más reciente primero)
            ->get();
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}