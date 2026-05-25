<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\SectionSubject;
use App\Models\StudentGrade;
use App\Models\EvaluationPeriod;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class Grades extends Component
{
    public SectionSubject $sectionSubject;
    public $students = [];
    public $periods = [];
    public $selectedPeriodId;
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

    public function mount(SectionSubject $sectionSubject): void
    {
        // 1. Autorización
        if (Auth::user()->hasRole('Profesor') && $sectionSubject->teacher_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        $this->sectionSubject = $sectionSubject->load(['section.gradeLevel', 'subject']);
        
        // 2. Obtener el año activo
        $activeYear = AcademicYear::where('status', 'active')->first() 
            ?? AcademicYear::orderByDesc('id')->first();

        // 3. Obtener los períodos evaluativos escolares
        if ($activeYear) {
            $this->periods = EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->orderBy('number')
                ->get();
        }

        // Seleccionar periodo activo por defecto, o el primero
        $activePeriod = collect($this->periods)->firstWhere('status', 'active') 
            ?? collect($this->periods)->first();
        
        $this->selectedPeriodId = $activePeriod?->id;

        // 4. Cargar estudiantes y calificaciones
        $this->loadStudentsAndGrades();
    }

    public function selectPeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
        $this->loadStudentsAndGrades();
    }

    public function loadStudentsAndGrades()
    {
        // Obtener estudiantes de la sección
        $this->students = Student::where('section_id', $this->sectionSubject->section_id)
            ->get()
            ->sortBy('fullName');

        $this->checkGradingAvailability();

        // Obtener calificaciones ya guardadas para este período
        if ($this->selectedPeriodId) {
            $existingGrades = StudentGrade::where('section_subject_id', $this->sectionSubject->id)
                ->where('evaluation_period_id', $this->selectedPeriodId)
                ->get()
                ->keyBy('student_id');

            $this->grades = [];
            foreach ($this->students as $student) {
                $this->grades[$student->id] = $existingGrades->get($student->id)?->score;
            }
        }
    }

    /**
     * Verifica si el periodo de digitación está cerrado o abierto.
     */
    private function checkGradingAvailability()
    {
        if (!$this->selectedPeriodId) {
            $this->isLocked = true;
            $this->lockReason = 'No hay un período evaluativo seleccionado.';
            return;
        }

        $period = EvaluationPeriod::find($this->selectedPeriodId);
        
        if ($period && $period->status === 'closed') {
            $this->isLocked = true;
            $this->lockReason = 'ESTADO CERRADO: El periodo de evaluación seleccionado (' . $period->name . ') ha sido cerrado oficialmente por la dirección del centro.';
            return;
        }

        // Admin o Registro siempre pueden editar
        if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Registro')) {
            $this->isLocked = false;
            $this->lockReason = '';
            return;
        }

        // Si el periodo no es activo o grading, está bloqueado para profesores
        if ($period && !in_array($period->status, ['active', 'grading'])) {
            $this->isLocked = true;
            $this->lockReason = 'PERIODO BLOQUEADO: El periodo seleccionado (' . $period->name . ') no está abierto para digitación en este momento.';
            return;
        }

        $this->isLocked = false;
        $this->lockReason = '';
    }

    public function saveGrades(): void
    {
        $this->checkGradingAvailability();
        if ($this->isLocked) {
            session()->flash('error', $this->lockReason);
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                foreach ($this->grades as $studentId => $score) {
                    $scoreVal = $score !== '' && $score !== null ? round((float)$score, 2) : null;
                    
                    StudentGrade::updateOrCreate([
                        'student_id' => $studentId,
                        'section_subject_id' => $this->sectionSubject->id,
                        'evaluation_period_id' => $this->selectedPeriodId,
                    ], [
                        'score' => $scoreVal,
                        'recorded_by' => auth()->id(),
                        'recorded_at' => now(),
                    ]);
                }
            });

            session()->flash('message', 'Calificaciones actualizadas correctamente en la malla oficial.');
            $this->loadStudentsAndGrades();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render(): View
    {
        $periodName = '';
        if ($this->selectedPeriodId) {
            $p = collect($this->periods)->firstWhere('id', $this->selectedPeriodId);
            $periodName = $p ? ' - ' . $p->name : '';
        }

        return view('livewire.teacher-portal.grades', [
            'title' => 'Calificaciones - ' . ($this->sectionSubject->subject->name ?? '') . $periodName
        ]);
    }
}