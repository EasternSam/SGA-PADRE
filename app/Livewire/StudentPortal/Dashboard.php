<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student; // Asegúrate de importar Student
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout; // Opcional, pero buena práctica

#[Layout('layouts.dashboard')] // Define el layout aquí
class Dashboard extends Component
{
    // Propiedades públicas que se pasarán a la vista
    // Hacemos $student anulable (nullable) por si falla la carga
    public ?Student $student;
    public $activeEnrollments = [];
    public $completedEnrollments = [];
    public $payments = [];

    /**
     * Mounta el componente y carga todos los datos del estudiante.
     */
    public function mount()
    {
        // 1. Obtener el estudiante de forma segura
        $student = Auth::user()?->student; // Usamos la relación que ya existe

        // 2. Verificar si el estudiante existe
        if (!$student) {
            // Si no existe, es el problema de datos.
            // Registramos el error y redirigimos a la página de perfil
            // para evitar un 'Invalid route action' y un bucle.
            
            if (!request()->routeIs('profile.edit')) {
                session()->flash('error', 'Su cuenta de usuario no está enlazada a un perfil de estudiante. Por favor, contacte a soporte.');
                return redirect()->route('profile.edit');
            }
            
            $this->student = null; // Se queda nulo
            return;
        }

        // 3. Si existe, asignamos las propiedades
        $this->student = $student;


        // --- ¡DIAGNÓSTICO ELIMINADO! ---
        // Se quitó el 'dd()' para que la página cargue.
        
        // Se crea una consulta base para reutilizar
        $baseQuery = Enrollment::with([
                'courseSchedule.module.course',
                'courseSchedule.teacher'
            ])
            ->where('student_id', $this->student->id);

        // --- ¡¡¡ESTA ES LA SOLUCIÓN!!! ---
        // Consulta para Cursos Activos (ahora incluye "Enrolled")
        $this->activeEnrollments = (clone $baseQuery) // Clonamos la consulta base
            ->whereIn('status', [
                'Cursando', 'Pendiente', // Original
                'cursando', 'pendiente', // Failsafe para minúsculas
                'Enrolled', 'enrolled'   // ¡AÑADIDO GRACIAS A TU DIAGNÓSTICO!
            ])
            ->get();

        // Consulta para Cursos Completados (ahora incluye minúsculas)
        $this->completedEnrollments = (clone $baseQuery) // Clonamos la consulta base
            ->whereIn('status', [
                'Completado', // Original
                'completado'  // Failsafe para minúsculas
            ])
            ->get();


        // Cargar los pagos
        $this->payments = Payment::with('paymentConcept')
            ->where('student_id', $this->student->id)
            ->orderBy('created_at', 'desc')
            ->take(5) // Tomar solo los últimos 5
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