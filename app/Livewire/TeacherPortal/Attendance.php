<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Attendance as AttendanceModel;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class Attendance extends Component
{
    public CourseSchedule $section;
    public $enrollments = [];
    public $attendanceDate;
    public $attendanceData = []; // ['enrollment_id' => 'status']

    public $isLocked = false; 

    /**
     * Carga la sección y los estudiantes.
     */
    public function mount(CourseSchedule $section)
    {
        // Cargar sección y estudiantes, PERO filtrando los que no han pagado
        $this->section = $section->load(['module.course', 'enrollments' => function($query) {
            // EXCLUIMOS estudiantes con estado 'Pendiente' (sin pagar)
            $query->whereNotIn('status', ['Pendiente', 'pendiente'])
                  ->with('student');
        }]);
        
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

        // Si se encontraron asistencias, bloquea la edición
        $this->isLocked = $attendances->isNotEmpty();

        foreach ($this->enrollments as $enrollment) {
            // 1. Obtenemos la asistencia existente. Puede ser 'null' si no hay registro para hoy.
            $existingAttendance = $attendances->get($enrollment->id);

            // 2. Asignamos estado. Si es null, por defecto 'Presente' (o vacío si prefieres que seleccionen)
            $this->attendanceData[$enrollment->id] = $existingAttendance?->status ?? 'Presente';
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
        if ($this->isLocked) {
             session()->flash('error', 'La asistencia para este día ya está guardada y bloqueada.');
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
        $this->loadAttendance();
        unset($this->completedDates);
    }

    /**
     * Obtiene la lista de fechas donde ya se pasó lista.
     */
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

    /**
     * Prepara y emite el evento para abrir el reporte en el modal.
     */
    public function generateReport()
    {
        // CORRECCIÓN IMPORTANTE:
        // Apuntar a la ruta 'reports.attendance.pdf' que definimos para el controlador PDF.
        // NO apuntar a 'reports.index', porque eso carga la página web de reportes.
        
        $url = route('reports.attendance.pdf', ['section' => $this->section->id]);
        
        // Dispara el evento para abrir el modal con la URL del PDF
        $this->dispatch('open-pdf-modal', url: $url);
    }

    public function render(): View
    {
        return view('livewire.teacher-portal.attendance', [
            'title' => 'Asistencia - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}