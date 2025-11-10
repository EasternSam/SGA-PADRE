<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Student; // Importar Student
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout; // Importar Layout
use Illuminate\Support\Facades\Log; // Importar Log

// ¡¡¡REPARACIÓN #1!!!
// Se corrige el layout a 'app' para ser consistente con tus
// otros componentes (Profile, Courses) y asegurar que carga
// el layout correcto.
#[Layout('app')]
class Dashboard extends Component
{
    public $student;

    /**
     * Mount the component.
     * Carga el perfil del estudiante basado en el usuario autenticado.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            // Si no hay usuario (sesión expirada, etc.), redirigir al login
            redirect()->route('login');
            return;
        }

        // Nos aseguramos de cargar las relaciones necesarias
        try {
            // Esta es la consulta que fallaba (Línea 25)
            $studentProfile = $user->student()->with(
                'enrollments.courseSchedule.module.course',
                'enrollments.courseSchedule.teacher', // Añadido para consistencia
                'payments.concept',
                'payments.user'
            )->first();

            // Si por alguna razón el usuario "Estudiante" no tiene un perfil vinculado,
            // (lo cual sería un error de datos), mostramos un error.
            if (!$studentProfile) {
                Log::warning("Usuario Estudiante (ID: {$user->id}, Email: {$user->email}) no tiene perfil de estudiante vinculado.");
                // Asignamos null y salimos; el render() manejará el estado vacío
                $this->student = null;
                session()->flash('error', 'Perfil de estudiante no encontrado para este usuario.');
                return;
            }

            $this->student = $studentProfile;

        } catch (\Exception $e) {
            // Si las relaciones fallan (como pasaba antes), esto lo capturaría
            Log::error("Error al cargar dashboard de estudiante para User ID {$user->id}: " . $e->getMessage());
            $this->student = null;
            session()->flash('error', 'Ocurrió un error al cargar tu perfil. Contacta a soporte.');
        }
    }

    public function render(): View
    {
        // ¡¡¡REPARACIÓN #2!!!
        // La vista (dashboard.blade.php) espera una variable '$enrollments'.
        // Ya la tenemos cargada en '$this->student->enrollments', así que
        // la extraemos y la pasamos explícitamente a la vista.

        // Filtramos solo las inscripciones activas para el dashboard principal
        // Usamos el 'optional helper' por si $this->student es null
        $enrollments = optional($this->student)->enrollments
            ? $this->student->enrollments->where('status', 'active')
            : collect(); // Si no hay estudiante o inscripciones, colección vacía

        // ¡¡¡REPARACIÓN #3!!! (La solución a tu error)
        // La vista también espera '$pendingPayments'. Los filtramos de la
        // colección que ya cargamos en mount().
        $allPayments = optional($this->student)->payments;

        $pendingPayments = $allPayments
            ? $allPayments->where('status', 'pending')
            : collect(); // Colección vacía si no hay estudiante o pagos

        // El nombre de la vista debe ser 'livewire.student-portal.dashboard'
        return view('livewire.student-portal.dashboard', [
            'enrollments' => $enrollments,
            'pendingPayments' => $pendingPayments // <-- ¡¡¡AÑADIDO!!!
            // $student ya es público, así que la vista también tiene acceso a él.
        ]);
    }
}