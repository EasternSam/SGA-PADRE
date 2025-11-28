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

    // --- Propiedades para el Modal de Completar Perfil (NUEVO) ---
    public bool $showProfileModal = false;
    public $phone;
    public $address;

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
        
        // --- LÓGICA DE ONBOARDING / COMPLETAR PERFIL (NUEVO) ---
        // Cargar datos actuales en las propiedades del formulario
        $this->phone = $this->student->phone;
        $this->address = $this->student->address;

        // Verificar si falta información o es "N/A" (Data sucia de importación)
        // Si el teléfono o la dirección son "N/A", null o vacíos, abrimos el modal.
        if (
            $this->isIncomplete($this->phone) || 
            $this->isIncomplete($this->address)
        ) {
            $this->showProfileModal = true;
        }
        // -------------------------------------------------------

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

    // --- Métodos de Onboarding (NUEVO) ---

    /**
     * Helper para verificar si un campo está incompleto o tiene N/A
     */
    private function isIncomplete($value)
    {
        // Se considera incompleto si es null, vacío, o dice "N/A" (sin importar mayúsculas/minúsculas)
        return empty($value) || strtoupper(trim($value)) === 'N/A';
    }

    /**
     * Guardar la información del perfil (Opcional)
     */
    public function saveProfile()
    {
        // Reglas 'nullable' porque pediste que sea opcional
        $this->validate([
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        if ($this->student) {
            $this->student->update([
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            
            // Refrescamos el modelo para que la UI principal se actualice si muestra estos datos
            $this->student->refresh();

            session()->flash('message', 'Información de perfil actualizada correctamente.');
        }

        $this->showProfileModal = false;
    }

    /**
     * Cerrar el modal sin guardar (Botón "Más tarde")
     */
    public function closeProfileModal()
    {
        $this->showProfileModal = false;
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}