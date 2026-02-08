<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Attendance as AttendanceModel;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class Attendance extends Component
{
    public CourseSchedule $section;
    public $enrollments = [];
    public $attendanceDate;
    public $attendanceData = []; 

    public $isLocked = false; 
    public $errorMessage = '';

    public function mount(CourseSchedule $section)
    {
        $this->section = $section->load(['module.course', 'enrollments' => function($query) {
            $query->whereNotIn('status', ['Pendiente', 'pendiente'])
                  ->with('student');
        }]);
        
        $this->enrollments = $this->section->enrollments;
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

        // 2. Bloqueo de días pasados (Ej: 48 horas) - Salvo Admins
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('Registro')) {
            if ($date->diffInHours(now()) > 48) {
                // Verificamos si YA se tomó asistencia ese día. Si ya existe, se bloquea la edición.
                // Si NO existe, se permite (caso de profesor que olvidó marcarla ayer).
                $exists = AttendanceModel::where('course_schedule_id', $this->section->id)
                    ->whereDate('attendance_date', $date)
                    ->exists();
                
                if ($exists) {
                    $this->isLocked = true;
                    $this->errorMessage = 'El periodo de edición para esta fecha ha expirado.';
                }
            }
        }
    }

    public function loadAttendance()
    {
        $date = Carbon::parse($this->attendanceDate);
        $this->attendanceData = []; 

        $attendances = AttendanceModel::where('course_schedule_id', $this->section->id)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('enrollment_id');

        foreach ($this->enrollments as $enrollment) {
            $existingAttendance = $attendances->get($enrollment->id);
            $this->attendanceData[$enrollment->id] = $existingAttendance?->status ?? 'Presente';
        }
    }

    public function updatedAttendanceDate()
    {
        $this->checkLockStatus();
        $this->loadAttendance();
    }

    public function saveAttendance()
    {
        // Re-validar al guardar por seguridad
        $this->checkLockStatus();
        if ($this->isLocked) {
             session()->flash('error', $this->errorMessage);
             return;
        }

        $date = Carbon::parse($this->attendanceDate);

        foreach ($this->attendanceData as $enrollmentId => $status) {
            AttendanceModel::updateOrCreate(
                [
                    'enrollment_id' => $enrollmentId,
                    'course_schedule_id' => $this->section->id,
                    'attendance_date' => $date,
                ],
                [
                    'status' => $status
                ]
            );
        }

        session()->flash('message', 'Asistencia guardada para el ' . $date->format('d/m/Y'));
        unset($this->completedDates);
    }

    #[Computed(persist: true)] 
    public function completedDates()
    {
        return AttendanceModel::where('course_schedule_id', $this->section->id)
            ->select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->get()
            ->pluck('attendance_date'); 
    }

    public function generateReport()
    {
        $url = route('reports.attendance.pdf', ['section' => $this->section->id]);
        $this->dispatch('open-pdf-modal', url: $url);
    }

    public function render(): View
    {
        return view('livewire.teacher-portal.attendance', [
            'title' => 'Asistencia - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}