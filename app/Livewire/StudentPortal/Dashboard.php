<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public Student $student;
    
    // Almacenaremos las colecciones aquí
    public $activeEnrollments;
    public $completedEnrollments;
    public $payments;

    /**
     * Carga el perfil del estudiante logueado y sus relaciones.
     */
    public function mount(): void
    {
        // Obtenemos el usuario autenticado y cargamos su perfil de estudiante
        // Tu middleware 'role:Estudiante' ya protege esta ruta.
        $student = Auth::user()->student;

        if (!$student) {
            // Esto no debería pasar si la lógica de registro es correcta
            abort(404, 'Perfil de estudiante no encontrado.');
        }

        // Cargar todas las relaciones necesarias de una sola vez
        $student->load([
            'enrollments' => function ($query) {
                // Ordenar las inscripciones por la fecha de inicio del curso
                $query->with([
                    'courseSchedule.module.course', 
                    'courseSchedule.teacher'
                ])->join('course_schedules', 'enrollments.course_schedule_id', '=', 'course_schedules.id')
                  ->orderBy('course_schedules.start_date', 'desc');
            },
            'payments.paymentConcept'
        ]);

        $this->student = $student;

        // --- ¡¡¡INICIO DE LA CORRECCIÓN!!! ---
        // Obtenemos todas las inscripciones cargadas
        $allEnrollments = $this->student->enrollments;

        // Cursos Completados: Son los que tienen el estado 'completed'.
        $this->completedEnrollments = $allEnrollments->filter(function ($enrollment) {
            return $enrollment->status === 'completed';
        });

        // Cursos Activos: Son todos los demás que NO estén 'completed' NI 'cancelled'.
        // Esto incluirá 'active', 'Enrolled', 'pending', etc.
        $this->activeEnrollments = $allEnrollments->filter(function ($enrollment) {
            return $enrollment->status !== 'completed' && $enrollment->status !== 'cancelled';
        });
        // --- ¡¡¡FIN DE LA CORRECCIÓN!!! ---
        
        // Cargar los 5 pagos más recientes
        $this->payments = $this->student->payments->sortByDesc('created_at')->take(5);
    }

    /**
     * Renderiza la vista del dashboard del estudiante.
     */
    public function render(): View
    {
        return view('livewire.student-portal.dashboard', [
            'title' => 'Mi Expediente'
        ])->layout('layouts.dashboard');
    }
}