<?php

namespace App\Livewire\TeacherPortal; // <-- Namespace correcto

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Attendance as AttendanceModel; // <-- Alias para el Modelo
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class Attendance extends Component // <-- Clase correcta (Component)
{
    public CourseSchedule $section;
    public $enrollments = [];
    public $attendanceDate;
    public $attendanceData = []; // ['enrollment_id' => 'status']

    /**
     * Carga la sección y los estudiantes.
     */
    public function mount(CourseSchedule $section)
    {
        $this->section = $section->load('module.course', 'enrollments.student');
        $this->enrollments = $this->section->enrollments;
        $this->attendanceDate = now()->format('Y-m-d');
        
        $this->loadAttendance();
    }

    /**
     * Carga la asistencia guardada para la fecha seleccionada.
     */
    public function loadAttendance()
    {
        $date = Carbon::parse($this->attendanceDate);
        $this->attendanceData = []; // Reset

        // Usamos el alias
        $attendances = AttendanceModel::where('course_schedule_id', $this->section->id)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('enrollment_id');

        foreach ($this->enrollments as $enrollment) {
            $this->attendanceData[$enrollment->id] = $attendances->get($enrollment->id)->status ?? 'Presente';
        }
    }

    /**
     * Detecta cuando cambia la fecha y recarga la asistencia.
     */
    public function updatedAttendanceDate()
    {
        $this->loadAttendance();
    }

    /**
     * Guarda la asistencia del día.
     */
    public function saveAttendance()
    {
        $date = Carbon::parse($this->attendanceDate);

        foreach ($this->attendanceData as $enrollmentId => $status) {
            
            // Usamos el alias
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
    }

    public function render(): View
    {
        // CORRECCIÓN: Apuntar a 'livewire' (l minúscula) de nuevo
        return view('livewire.teacher-portal.attendance', [
            'title' => 'Asistencia - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}