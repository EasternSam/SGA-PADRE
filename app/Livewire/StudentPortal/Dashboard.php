<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student; // Asegúrate de importar Student
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout; // Opcional, pero buena práctica
use Illuminate\Support\Collection; // <-- IMPORTADO

#[Layout('layouts.dashboard')] // Define el layout aquí
class Dashboard extends Component
{
    // --- Propiedades Actualizadas ---
    public ?Student $student;
    public Collection $activeEnrollments;    // Cursos 'Cursando' o 'Activo'
    public Collection $pendingEnrollments;   // Inscripciones 'Pendiente' (de pago)
    public Collection $completedEnrollments; // Cursos 'Completado'
    public Collection $pendingPayments;      // Pagos 'Pendiente'

    /**
     * Mounta el componente y carga todos los datos del estudiante.
     */
    public function mount()
    {
        // 1. Obtener el estudiante de forma segura (Tu lógica original)
        $student = Auth::user()?->student; 

        // 2. Verificar si el estudiante existe (Tu lógica original)
        if (!$student) {
            if (!request()->routeIs('profile.edit')) {
                session()->flash('error', 'Su cuenta de usuario no está enlazada a un perfil de estudiante. Por favor, contacte a soporte.');
                return redirect()->route('profile.edit');
            }
            
            $this->student = null; 
            // Inicializar colecciones vacías
            $this->activeEnrollments = collect();
            $this->pendingEnrollments = collect();
            $this->completedEnrollments = collect();
            $this->pendingPayments = collect();
            return;
        }

        // 3. Si existe, asignamos las propiedades
        $this->student = $student;
        
        // Se crea una consulta base para reutilizar (Tu lógica original)
        $baseQuery = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher'
            ])
            ->where('student_id', $this->student->id);

        // --- Lógica de Carga ACTUALIZADA ---

        // Cursos Activos (Solo Cursando o Activo)
        $this->activeEnrollments = (clone $baseQuery)
            ->whereIn('status', [
                'Cursando', 'cursando', 
                'Activo', 'activo'
                // Se quita 'Pendiente' y 'Enrolled' de esta lista
            ])
            ->get();

        // Inscripciones Pendientes (NUEVO)
        $this->pendingEnrollments = (clone $baseQuery)
            ->whereIn('status', [
                'Pendiente', 'pendiente',
                'Enrolled', 'enrolled' // Mantenemos 'Enrolled' como un estado pendiente
            ])
            ->get();

        // Cursos Completados (Tu lógica original)
        $this->completedEnrollments = (clone $baseQuery)
            ->whereIn('status', [
                'Completado', 'completado'
            ])
            ->get();

        // Pagos Pendientes (NUEVO)
        // Cargar los pagos que están pendientes para mostrar alerta
        $this->pendingPayments = Payment::with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->where('student_id', $this->student->id)
            ->where('status', 'Pendiente')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Renderiza la vista.
     */
    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}