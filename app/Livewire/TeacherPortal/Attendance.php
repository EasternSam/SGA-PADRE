<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Attendance extends Component
{
    public Section $section;
    public $students = [];
    public $attendanceDate;
    public $attendanceData = []; 
    public $isLocked = false; 
    public $errorMessage = '';

    public function mount(Section $section)
    {
        $teacherId = Auth::id();
        
        // Autorizar: El usuario debe ser administrador o tener asignación en esta sección o ser su tutor
        $isAssigned = TeacherAssignment::where('section_id', $section->id)
            ->where('teacher_id', $teacherId)
            ->exists();
        
        if (Auth::user()->hasRole('Profesor') && !$isAssigned && $section->homeroom_teacher_id !== $teacherId) {
            abort(403, 'No tienes permiso para ver la asistencia de esta sección.');
        }

        $this->section = $section->load(['gradeLevel', 'academicYear']);
        $this->students = Student::where('section_id', $section->id)
            ->get()
            ->sortBy('fullName');

        $this->attendanceDate = now()->format('Y-m-d');
        
        $this->checkLockStatus();
        $this->loadAttendance();
    }

    private function checkLockStatus()
    {
        $this->isLocked = false;
        $this->errorMessage = '';
        
        $date = Carbon::parse($this->attendanceDate);
        
        // 1. No permitir fechas futuras
        if ($date->isFuture()) {
            $this->isLocked = true;
            $this->errorMessage = 'No puedes tomar asistencia en fechas futuras.';
            return;
        }

        // 2. Bloqueo de días pasados (Ej: 48 horas) - Salvo Admins o Registro
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('Registro')) {
            if ($date->diffInHours(now()) > 48) {
                // Verificamos si YA se tomó asistencia ese día. Si ya existe, se bloquea la edición.
                $exists = StudentAttendance::where('section_id', $this->section->id)
                    ->whereDate('date', $date)
                    ->exists();
                
                if ($exists) {
                    $this->isLocked = true;
                    $this->errorMessage = 'El periodo de edición de asistencia para esta fecha ha expirado (48 horas límite).';
                }
            }
        }
    }

    public function loadAttendance()
    {
        $date = Carbon::parse($this->attendanceDate);
        $this->attendanceData = []; 

        $attendances = StudentAttendance::where('section_id', $this->section->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        foreach ($this->students as $student) {
            $existingAttendance = $attendances->get($student->id);
            $this->attendanceData[$student->id] = $existingAttendance?->status ?? 'present';
        }
    }

    public function updatedAttendanceDate()
    {
        $this->checkLockStatus();
        $this->loadAttendance();
    }

    public function saveAttendance()
    {
        $this->checkLockStatus();
        if ($this->isLocked) {
             session()->flash('error', $this->errorMessage);
             return;
        }

        $date = Carbon::parse($this->attendanceDate);
        $activeYear = AcademicYear::where('status', 'active')->first() 
            ?? AcademicYear::orderByDesc('id')->first();

        foreach ($this->attendanceData as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'section_id' => $this->section->id,
                    'date' => $date,
                ],
                [
                    'academic_year_id' => $activeYear?->id,
                    'status' => $status,
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        session()->flash('message', 'Registro de asistencia guardado exitosamente para el ' . $date->format('d/m/Y'));
    }

    public function render(): View
    {
        return view('livewire.teacher-portal.attendance', [
            'title' => 'Asistencia - ' . $this->section->full_name
        ])->layout('layouts.dashboard');
    }
}