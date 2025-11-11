<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Grades extends Component
{
    public CourseSchedule $section;
    public $enrollments = [];
    
    // Usamos un array para vincular las calificaciones a los inputs
    // La llave será el ID de la inscripción (enrollment_id)
    public $grades = [];

    /**
     * Carga la sección, sus estudiantes y las calificaciones existentes.
     */
    public function mount(CourseSchedule $section)
    {
        $this->section = $section->load('module.course', 'enrollments.student');
        $this->enrollments = $this->section->enrollments;

        // Llenamos el array $grades con las calificaciones actuales de la BD
        // pluck() creará un array como [ 'enrollment_id_1' => 85, 'enrollment_id_2' => 90, ... ]
        $this->grades = $this->enrollments->pluck('final_grade', 'id')->map(function ($grade) {
            // Asegurarnos de que no sea null para los inputs, aunque la BD lo permita
            return $grade; 
        })->toArray();
    }

    /**
     * Guarda todas las calificaciones de la sección.
     */
    public function saveGrades()
    {
        // Validamos que todas las calificaciones en el array sean números entre 0 y 100
        $this->validate([
            'grades.*' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->grades as $enrollmentId => $grade) {
                    $enrollment = Enrollment::find($enrollmentId);
                    if ($enrollment) {
                        
                        // Si la nota está vacía (""), la guardamos como NULL
                        $gradeValue = $grade === '' ? null : $grade;

                        $enrollment->update([
                            'final_grade' => $gradeValue,
                            // Lógica adicional: Si ponemos una nota, marcamos como Completado.
                            // Si la quitamos, vuelve a Cursando.
                            'status' => !is_null($gradeValue) ? 'Completado' : 'Cursando'
                        ]);
                    }
                }
            });

            session()->flash('message', 'Calificaciones guardadas exitosamente.');
            
            // Opcional: Redirigir de vuelta al dashboard del profesor
            // return $this->redirectRoute('teacher.dashboard');

        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al guardar las calificaciones: ' . $e->getMessage());
        }
    }

    public function render(): View
    {
        // Apuntamos a la nueva vista que crearemos
        return view('livewire.teacher-portal.grades', [
            'title' => 'Registrar Calificaciones - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}