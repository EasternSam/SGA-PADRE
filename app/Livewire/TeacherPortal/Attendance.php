<?php

namespace App\Livewire\TeacherPortal; // <-- Namespace correcto

use Livewire\Component;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Attendance as AttendanceModel; // <-- Alias para el Modelo
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Livewire\Attributes\Computed; // <-- ¡AÑADIDO! Para la lista de fechas

class Attendance extends Component // <-- Clase correcta (Component)
{
    public CourseSchedule $section;
    public $enrollments = [];
    public $attendanceDate;
    public $attendanceData = []; // ['enrollment_id' => 'status']

    public $isLocked = false; // <-- ¡AÑADIDO! Para bloquear la edición

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

        // --- ¡LÓGICA AÑADIDA! ---
        // Si se encontraron asistencias, bloquea la edición
        $this->isLocked = $attendances->isNotEmpty();
        // --- FIN LÓGICA AÑADIDA ---

        foreach ($this->enrollments as $enrollment) {
            // --- ¡¡¡ESTA ES LA CORRECCIÓN!!! ---
            // 1. Obtenemos la asistencia existente. Puede ser 'null' si no hay registro para hoy.
            $existingAttendance = $attendances->get($enrollment->id);

            // 2. Usamos el 'optional chaining operator' (?->) para leer 'status' de forma segura.
            //    Si $existingAttendance es null, $existingAttendance?->status devolverá null.
            // 3. Usamos el 'null coalescing operator' (??) para asignar 'Presente' si el resultado es null.
            //    Esto soluciona el error 'Attempt to read property "status" on null'
            
            // Esta única línea reemplaza y corrige la lógica 'if/else' anterior (líneas 55-60)
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
        // Si por alguna razón trata de guardar en un día bloqueado, lo evitamos.
        if ($this->isLocked) {
             session()->flash('error', 'La asistencia para este día ya está guardada y bloqueada.');
             return;
        }

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
        
        // --- ¡AÑADIDO! ---
        // Recargamos la asistencia, lo que ahora también la bloqueará
        $this->loadAttendance();
        
        // Limpiamos la caché de la lista de fechas completadas
        unset($this->completedDates);
    }

    /**
     * ¡NUEVA PROPIEDAD COMPUTADA!
     * Obtiene la lista de fechas donde ya se pasó lista.
     */
    #[Computed(persist: true)] // Persiste para no consultar la DB en cada render
    public function completedDates()
    {
        return AttendanceModel::where('course_schedule_id', $this->section->id)
            ->select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->get()
            ->pluck('attendance_date'); // Solo queremos las fechas
    }

    // --- ¡¡¡NUEVO MÉTODO AÑADIDO!!! ---
    /**
     * Prepara y emite el evento para abrir el reporte en el modal.
     */
    public function generateReport()
    {
        // Obtiene la URL de la nueva ruta del reporte
        $url = route('reports.attendance-report', $this->section);
        
        // Dispara el evento que el modal de Alpine.js está escuchando
        $this->dispatch('open-pdf-modal', url: $url);
    }
    // --- FIN DEL NUEVO MÉTODO ---


    public function render(): View
    {
        // CORRECCIÓN: Apuntar a 'livewire' (l minúscula) de nuevo
        return view('livewire.teacher-portal.attendance', [
            'title' => 'Asistencia - ' . $this->section->module->name
        ])->layout('layouts.dashboard');
    }
}