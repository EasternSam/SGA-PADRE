<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudentGrade;
use App\Models\StudentAttendance;
use App\Models\StudentPayment;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public ?Student $student = null;
    public $user;
    
    // Colecciones escolares
    public Collection $subjects;
    public Collection $grades;
    public Collection $pendingPayments;
    public Collection $paymentHistory;
    
    public ?Section $section = null;
    public array $attendanceStats = [
        'present' => 0,
        'absent' => 0,
        'late' => 0,
        'excused' => 0,
    ];
    public float $attendancePercentage = 100.0;
    public float $pendingBalance = 0.0;

    public function mount()
    {
        $this->user = Auth::user();
        $this->student = $this->user?->student;

        // Inicializar colecciones vacías
        $this->subjects = collect();
        $this->grades = collect();
        $this->pendingPayments = collect();
        $this->paymentHistory = collect();

        if ($this->student) {
            $this->section = $this->student->section;

            // 1. Asignaturas del alumno (a través de los SectionSubjects de su sección)
            if ($this->section) {
                $this->subjects = SectionSubject::where('section_id', $this->section->id)
                    ->with(['subject', 'teacher'])
                    ->get();
            }

            // 2. Calificaciones del alumno
            $this->grades = StudentGrade::where('student_id', $this->student->id)
                ->with(['sectionSubject.subject', 'evaluationPeriod'])
                ->get();

            // 3. Estadísticas de asistencia
            $attendances = StudentAttendance::where('student_id', $this->student->id)->get();
            $totalAttendance = $attendances->count();

            if ($totalAttendance > 0) {
                $this->attendanceStats = [
                    'present' => $attendances->where('status', 'present')->count(),
                    'absent' => $attendances->where('status', 'absent')->count(),
                    'late' => $attendances->where('status', 'late')->count(),
                    'excused' => $attendances->where('status', 'excused')->count(),
                ];
                
                $presentCount = $this->attendanceStats['present'] + $this->attendanceStats['late'] + $this->attendanceStats['excused'];
                $this->attendancePercentage = round(($presentCount / $totalAttendance) * 100, 1);
            }

            // 4. Pagos y Mensualidades
            $payments = StudentPayment::where('student_id', $this->student->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $this->pendingPayments = $payments->whereIn('status', ['pending', 'partial']);
            $this->paymentHistory = $payments->where('status', 'paid');
            
            // Calcular balance pendiente total
            $this->pendingBalance = (float) $this->pendingPayments->sum(fn($p) => $p->amount - $p->paid);
        }
    }

    public function render()
    {
        return view('livewire.student-portal.dashboard');
    }
}