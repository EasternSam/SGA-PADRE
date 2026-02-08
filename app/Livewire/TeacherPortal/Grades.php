<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\AcademicEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GradePostedMail;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.dashboard')]
class Grades extends Component
{
    public CourseSchedule $section;
    public $enrollments = [];
    public $grades = [];
    public $isLocked = false;
    public $lockReason = '';

    protected $rules = [
        'grades.*' => 'nullable|numeric|min:0|max:100',
    ];

    protected $messages = [
        'grades.*.numeric' => 'La calificación debe ser un número.',
        'grades.*.min' => 'La calificación no puede ser menor que 0.',
        'grades.*.max' => 'La calificación no puede ser mayor que 100.',
    ];

    public function mount(CourseSchedule $section): void
    {
        // 1. Seguridad de Propiedad
        if (Auth::user()->hasRole('Profesor') && $section->teacher_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        $this->section = $section->load('module.course', 'enrollments.student');
        
        // 2. Validación de Fechas (Regla de Negocio)
        $this->checkGradingAvailability();

        $this->enrollments = $this->section->enrollments->sortBy('student.fullName');

        $this->grades = $this->enrollments->mapWithKeys(function ($enrollment) {
            return [$enrollment->id => $enrollment->final_grade];
        })->toArray();
    }

    /**
     * Verifica si el periodo de digitación está activo.
     */
    private function checkGradingAvailability()
    {
        // Admin siempre puede editar
        if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Registro')) {
            return;
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($this->section->end_date);
        
        // REGLA 1: No se puede calificar antes de que termine la materia (Opcional, a veces se permite)
        // if ($now->lt($endDate->subDays(7))) { // Ejemplo: Permitir solo la última semana
        //     $this->isLocked = true;
        //     $this->lockReason = 'El periodo de calificación inicia al finalizar el curso.';
        //     return;
        // }

        // REGLA 2: Plazo estándar de 15 días después de finalizar
        $deadline = $endDate->copy()->addDays(15);

        // REGLA 3: Excepción por Evento Académico (Prórroga)
        $isExtensionActive = AcademicEvent::isActionActive('grading_extension');

        if ($now->gt($deadline) && !$isExtensionActive) {
            $this->isLocked = true;
            $this->lockReason = 'El periodo de calificación cerró el ' . $deadline->format('d/m/Y') . '.';
        }
    }

    public function saveGrades(): void
    {
        if ($this->isLocked) {
            session()->flash('error', $this->lockReason);
            return;
        }

        $this->validate();

        try {
            $enrollmentsToNotify = [];

            DB::transaction(function () use (&$enrollmentsToNotify) {
                foreach ($this->grades as $enrollmentId => $grade) {
                    $enrollment = Enrollment::with('student', 'courseSchedule.module')->find($enrollmentId);
                    
                    if ($enrollment && $enrollment->course_schedule_id === $this->section->id) {
                        
                        // Detectar cambio
                        $oldGrade = $enrollment->final_grade;
                        $newGrade = $grade !== '' && $grade !== null ? round($grade, 2) : null;

                        if ($oldGrade !== $newGrade) {
                            $enrollment->final_grade = $newGrade;
                            
                            // --- AUTOMATIZACIÓN DE ESTADO ---
                            if (!is_null($newGrade)) {
                                // Nota mínima aprobatoria (Configurable, aquí hardcoded 70)
                                if ($newGrade >= 70) {
                                    $enrollment->status = 'Aprobado';
                                } else {
                                    $enrollment->status = 'Reprobado';
                                }
                            } else {
                                // Si borran la nota, vuelve a cursando
                                $enrollment->status = 'Cursando';
                            }

                            $enrollment->save();

                            // Solo notificar si hay nota real
                            if (!is_null($newGrade)) {
                                $enrollmentsToNotify[] = $enrollment;
                            }
                        }
                    }
                }
            });

            // Enviar correos (Queue o directo)
            foreach ($enrollmentsToNotify as $enrollment) {
                if ($enrollment->student && $enrollment->student->email) {
                    try {
                        Mail::to($enrollment->student->email)->send(new GradePostedMail($enrollment));
                    } catch (\Exception $e) {
                        Log::error("Error mail notas: " . $e->getMessage());
                    }
                }
            }

            session()->flash('message', 'Calificaciones y estatus académicos actualizados correctamente.');
            
            $this->section->refresh();
            $this->enrollments = $this->section->enrollments->sortBy('student.fullName');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.teacher-portal.grades', [
            'title' => 'Calificaciones - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}