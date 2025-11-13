<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Grades extends Component
{
    public CourseSchedule $section;
    public $enrollments = [];

    /**
     * @var array<int, float|null>
     * Almacenará las calificaciones, ej: [enrollment_id => 95.5]
     */
    public $grades = [];

    /**
     * Reglas de validación para el array de calificaciones.
     * Valida cada entrada en el array 'grades'.
     */
    protected $rules = [
        'grades.*' => 'nullable|numeric|min:0|max:100',
    ];

    /**
     * Mensajes de validación personalizados.
     */
    protected $messages = [
        'grades.*.numeric' => 'La calificación debe ser un número.',
        'grades.*.min' => 'La calificación no puede ser menor que 0.',
        'grades.*.max' => 'La calificación no puede ser mayor que 100.',
    ];

    /**
     * Carga la sección y prepara el array de calificaciones.
     */
    public function mount(CourseSchedule $section): void
    {
        // Validar que el profesor (o admin) pueda ver esta sección
        // (Esto asume que un admin también puede entrar)
        if (Auth::user()->hasRole('Profesor') && $section->teacher_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        $this->section = $section->load('module.course', 'enrollments.student');
        
        // Ordenar a los estudiantes por nombre para la lista
        $this->enrollments = $this->section->enrollments->sortBy('student.fullName');

        // Llenar el array $grades con las calificaciones existentes
        $this->grades = $this->enrollments->mapWithKeys(function ($enrollment) {
            return [$enrollment->id => $enrollment->final_grade];
        })->toArray();
    }

    /**
     * Guarda todas las calificaciones de la sección.
     */
    public function saveGrades(): void
    {
        $this->validate();

        try {
            // Usar una transacción para asegurar que todas las notas se guarden
            // o ninguna lo haga si ocurre un error.
            DB::transaction(function () {
                foreach ($this->grades as $enrollmentId => $grade) {
                    $enrollment = Enrollment::find($enrollmentId);
                    
                    // Asegurarse de que la inscripción pertenece a esta sección
                    if ($enrollment && $enrollment->course_schedule_id === $this->section->id) {
                        $enrollment->update([
                            'final_grade' => $grade ? round($grade, 2) : null // Redondear y guardar (o null si está vacío)
                        ]);
                    }
                }
            });

            session()->flash('message', 'Calificaciones guardadas exitosamente.');
            
            // Refrescar los datos en la página después de guardar
            $this->section->refresh();
            $this->enrollments = $this->section->enrollments->sortBy('student.fullName');

        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al guardar las calificaciones: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza la vista.
     */
    public function render(): View
    {
        return view('livewire.teacher-portal.grades', [
            'title' => 'Calificaciones - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}