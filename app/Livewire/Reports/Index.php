<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Models\CourseSchedule;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Index extends Component
{
    // Selección de Reporte
    public $reportType = 'attendance';

    // Filtros
    public $course_id = '';
    public $schedule_id = '';
    public $teacher_id = '';
    public $date_from;
    public $date_to;
    public $payment_status = 'all'; // all, pending, paid

    // Datos para selects
    public $courses = [];
    public $schedules = [];
    public $teachers = [];

    // Datos del Reporte Generado
    public $reportData = null;
    public $generatedReportType = null;

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
        
        $this->courses = Course::orderBy('name')->get();
        // Usando el role 'Profesor' según tus archivos
        $this->teachers = User::role('Profesor')->orderBy('name')->get();
    }

    public function updatedCourseId($value)
    {
        if ($value) {
            $this->schedules = CourseSchedule::where('course_id', $value)
                ->with('module') // Cargamos modulo si existe para mostrar nombre
                ->get();
        } else {
            $this->schedules = [];
        }
        $this->schedule_id = '';
    }

    public function generateReport()
    {
        $this->validateFilters();

        $this->generatedReportType = $this->reportType;
        $this->reportData = null;

        switch ($this->reportType) {
            case 'attendance':
                $this->generateAttendanceReport();
                break;
            case 'grades':
                $this->generateGradesReport();
                break;
            case 'payments':
                $this->generatePaymentsReport();
                break;
            case 'students':
                $this->generateStudentsReport();
                break;
            case 'calendar':
                $this->generateCalendarReport();
                break;
            case 'assignments':
                $this->generateAssignmentsReport();
                break;
        }
    }

    protected function validateFilters()
    {
        $rules = [
            'reportType' => 'required',
        ];

        if (in_array($this->reportType, ['attendance', 'grades', 'students'])) {
            $rules['course_id'] = 'required';
            $rules['schedule_id'] = 'required';
        }

        if (in_array($this->reportType, ['attendance', 'payments', 'calendar'])) {
            $rules['date_from'] = 'required|date';
            $rules['date_to'] = 'required|date|after_or_equal:date_from';
        }

        $this->validate($rules, [
            'course_id.required' => 'Debes seleccionar un curso.',
            'schedule_id.required' => 'Debes seleccionar una sección.',
            'date_from.required' => 'La fecha de inicio es requerida.',
        ]);
    }

    // 1. REPORTE DE ASISTENCIA
    public function generateAttendanceReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);

        $students = Student::whereHas('enrollments', function($q) {
            $q->where('course_schedule_id', $this->schedule_id);
        })->orderBy('last_name')->orderBy('first_name')->get();

        // Generar matriz de fechas
        $start = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);
        $period = CarbonPeriod::create($start, $end);
        
        $dates = [];
        $daysOfWeekMap = [
            'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 
            'Friday' => 5, 'Saturday' => 6, 'Sunday' => 0
        ];
        // En tu modelo, days_of_week es array. Puede ser nombres o números. Asumimos que viene de DB.
        // Si tu array guarda 'Lunes', etc, habría que mapear. Asumimos formato Carbon estándar o numérico.
        
        foreach ($period as $date) {
            // Lógica simple: incluimos todos los días del rango para visualización completa
            $dates[] = $date->format('Y-m-d');
        }

        $attendances = Attendance::where('course_schedule_id', $this->schedule_id)
            ->whereBetween('attendance_date', [$this->date_from, $this->date_to])
            ->get();

        $attendanceMatrix = [];
        foreach ($attendances as $record) {
            $attendanceMatrix[$record->enrollment->student_id ?? 0][$record->attendance_date->format('Y-m-d')] = $record->status;
        }

        $this->reportData = [
            'schedule' => $schedule,
            'students' => $students,
            'dates' => $dates,
            'matrix' => $attendanceMatrix,
            'start_date' => $start,
            'end_date' => $end,
        ];
    }

    // 2. REPORTE DE CALIFICACIONES
    public function generateGradesReport()
    {
        $schedule = CourseSchedule::with(['module.course', 'teacher'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name'); // Ordenar por apellido

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
        ];
    }

    // 3. REPORTE DE PAGOS
    public function generatePaymentsReport()
    {
        // Consulta base: Estudiantes y sus pagos
        $query = Payment::with(['student', 'paymentConcept', 'enrollment.courseSchedule.module.course']);

        if ($this->date_from && $this->date_to) {
            $query->whereBetween('created_at', [$this->date_from . ' 00:00:00', $this->date_to . ' 23:59:59']);
        }

        if ($this->teacher_id) {
            $query->whereHas('enrollment.courseSchedule', function($q) {
                $q->where('teacher_id', $this->teacher_id);
            });
        }

        if ($this->course_id) {
            $query->whereHas('enrollment.courseSchedule', function($q) {
                $q->where('course_id', $this->course_id);
            });
        }
        
        if ($this->payment_status !== 'all') {
            $query->where('status', $this->payment_status);
        }

        $payments = $query->latest()->get();

        // Para pagos PENDIENTES reales (deuda), la lógica es más compleja (buscar enrollments SIN pagos).
        // Por simplicidad en este reporte, listamos transacciones registradas.
        // Si necesitas "Deudas", habría que consultar Enrollments y restar pagos. 
        // Implementamos una versión mixta si selecciona 'pending'.
        
        $debts = [];
        if ($this->payment_status === 'pending') {
            // Buscar inscripciones activas que no tengan pago completo
            // Esto es un ejemplo simplificado
            $debts = Enrollment::with(['student', 'courseSchedule.module.course'])
                ->whereDoesntHave('payment', function($q) {
                    $q->where('status', 'paid');
                })
                ->where('status', 'Cursando') // Solo activos
                ->limit(50) // Limite por rendimiento
                ->get();
        }

        $this->reportData = [
            'payments' => $payments,
            'debts' => $debts,
            'filter_status' => $this->payment_status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }

    // 4. REPORTE DE ESTUDIANTES
    public function generateStudentsReport()
    {
        $schedule = CourseSchedule::with(['module.course'])->find($this->schedule_id);
        
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $this->schedule_id)
            ->get()
            ->sortBy('student.last_name');

        $this->reportData = [
            'schedule' => $schedule,
            'enrollments' => $enrollments,
        ];
    }

    // 5. REPORTE DE CALENDARIO
    public function generateCalendarReport()
    {
        // Buscar secciones activas en el rango
        $schedules = CourseSchedule::with(['module.course', 'teacher'])
            ->where(function($q) {
                $q->whereBetween('start_date', [$this->date_from, $this->date_to])
                  ->orWhereBetween('end_date', [$this->date_from, $this->date_to])
                  ->orWhere(function($sub) {
                      $sub->where('start_date', '<=', $this->date_from)
                          ->where('end_date', '>=', $this->date_to);
                  });
            })
            ->orderBy('start_date')
            ->get();

        $this->reportData = [
            'schedules' => $schedules,
            'start_date' => $this->date_from,
            'end_date' => $this->date_to,
        ];
    }

    // 6. REPORTE DE ASIGNACIÓN (Cursos y Profesores)
    public function generateAssignmentsReport()
    {
        $query = CourseSchedule::with(['module.course', 'teacher'])
            ->orderBy('teacher_id') // Agrupar por profesor visualmente
            ->orderBy('start_date');

        if ($this->teacher_id) {
            $query->where('teacher_id', $this->teacher_id);
        }
        
        if ($this->course_id) {
            $query->where('course_id', $this->course_id);
        }

        $assignments = $query->get();

        $this->reportData = [
            'assignments' => $assignments,
        ];
    }

    public function render()
    {
        return view('livewire.reports.index')
            ->layout('layouts.dashboard');
    }
}